#include "mapdisplay.h"
#include <QPainter>
#include <QMouseEvent>
#include <QWheelEvent>
#include <iostream>
#include <gdal_priv.h>
#include <ogr_spatialref.h>

MapDisplay::MapDisplay(QWidget *parent)
    : QWidget{parent}, geo_center(QPointF{0.0f, 0.0f}) {
    
    setlocale(LC_NUMERIC, "C");
    GDALAllRegister();

    GDALDataset *poDS = (GDALDataset*) GDALOpenEx("map.osm", GDAL_OF_VECTOR, NULL, NULL, NULL);
    if (!poDS) return;

    OGRSpatialReference oSourceSRS, oTargetSRS;
    oSourceSRS.importFromEPSG(4326); 
    oTargetSRS.importFromEPSG(2154); 
    oSourceSRS.SetAxisMappingStrategy(OAMS_TRADITIONAL_GIS_ORDER);
    OGRCoordinateTransformation *poCT = OGRCreateCoordinateTransformation(&oSourceSRS, &oTargetSRS);

    // Load data from all layers
    double gMinX = 1e18, gMinY = 1e18, gMaxX = -1e18, gMaxY = -1e18;
    for (int i = 0; i < poDS->GetLayerCount(); i++) {
        OGRLayer *layer = poDS->GetLayer(i);
        OGREnvelope env;
        if (layer->GetExtent(&env) == OGRERR_NONE) {
            double lx = env.MinX, ly = env.MinY, hx = env.MaxX, hy = env.MaxY;
            poCT->Transform(1, &lx, &ly);
            poCT->Transform(1, &hx, &hy);
            gMinX = std::min(gMinX, lx); gMinY = std::min(gMinY, ly);
            gMaxX = std::max(gMaxX, hx); gMaxY = std::max(gMaxY, hy);
        }

        layer->ResetReading();
        OGRFeature *poFeature;
        while ((poFeature = layer->GetNextFeature()) != NULL) {
            OGRGeometry *poGeom = poFeature->GetGeometryRef();
            if (poGeom) {
                OGRGeometry *poCopy = poGeom->clone();
                if (poCopy->transform(poCT) == OGRERR_NONE) _geometries.push_back(poCopy);
                else delete poCopy;
            }
            OGRFeature::DestroyFeature(poFeature);
        }
    }

    _footprint = QRectF(gMinX, gMinY, gMaxX - gMinX, gMaxY - gMinY);
    OGRCoordinateTransformation::DestroyCT(poCT);
    GDALClose(poDS);
}

MapDisplay::~MapDisplay() {
    for (auto g : _geometries) OGRGeometryFactory::destroyGeometry(g);
}

QPointF MapDisplay::pixelToMode(const QPoint &pos) {
    double gx = _footprint.left() + (pos.x() * _footprint.width() / width());
    double gy = _footprint.top() + ((height() - pos.y()) * _footprint.height() / height());
    return QPointF(gx, gy);
}

void MapDisplay::mousePressEvent(QMouseEvent *event) {
    if (event->button() == Qt::LeftButton) {
        _is_panning = true;
        _last_mouse_pos = event->pos();
    }
}

void MapDisplay::mouseMoveEvent(QMouseEvent *event) {
    if (_is_panning) {
        QPoint delta = event->pos() - _last_mouse_pos;
        
        double moveX = delta.x() * _footprint.width() / width();
        double moveY = -delta.y() * _footprint.height() / height();
        
        _footprint.translate(-moveX, -moveY);
        
        _last_mouse_pos = event->pos();
        update(); 
    }
}

void MapDisplay::mouseReleaseEvent(QMouseEvent *event) {
    if (event->button() == Qt::LeftButton) _is_panning = false;
}

void MapDisplay::wheelEvent(QWheelEvent *event) {
    // Determine if we zoom in (factor < 1) or out (factor > 1)
    double zoomFactor = (event->angleDelta().y() > 0) ? 0.8 : 1.2;
    
    // Get the geographic point under the mouse so we zoom "into" the cursor
    QPointF mouseGeoBefore = pixelToMode(event->pos());
    
    // Scale the footprint dimensions
    double newWidth = _footprint.width() * zoomFactor;
    double newHeight = _footprint.height() * zoomFactor;
    
    // Adjust the top-left corner so the point under the mouse stays the same
    double newLeft = mouseGeoBefore.x() - (event->pos().x() * newWidth / width());
    double newTop = mouseGeoBefore.y() - ((height() - event->pos().y()) * newHeight / height());
    
    _footprint = QRectF(newLeft, newTop, newWidth, newHeight);
    update();
}

void MapDisplay::paintEvent(QPaintEvent *event) {
    Q_UNUSED(event);
    setlocale(LC_NUMERIC, "C");
    QPainter painter{this};
    painter.setRenderHint(QPainter::Antialiasing);
    painter.fillRect(rect(), QColor(30, 30, 30));

    if (_footprint.isNull() || _geometries.empty()) return;

    for (OGRGeometry* geom : _geometries) drawOGRGeometry(geom, painter);
}

void MapDisplay::drawOGRGeometry(OGRGeometry *geom, QPainter &painter) {
    auto mapToScreen = [&](double x, double y) {
        double sx = (x - _footprint.left()) * width() / _footprint.width();
        double sy = height() - ((y - _footprint.top()) * height() / height());
        return QPointF(sx, sy);
    };

    OGRwkbGeometryType type = wkbFlatten(geom->getGeometryType());
    if (type == wkbLineString) {
        painter.setPen(QPen(Qt::white, 1));
        OGRLineString *ls = (OGRLineString*)geom;
        for (int i = 0; i < ls->getNumPoints() - 1; i++) {
            painter.drawLine(mapToScreen(ls->getX(i), ls->getY(i)),
                             mapToScreen(ls->getX(i+1), ls->getY(i+1)));
        }
    } else if (type == wkbPoint) {
        painter.setPen(QPen(Qt::yellow, 2));
        OGRPoint *p = (OGRPoint*)geom;
        painter.drawEllipse(mapToScreen(p->getX(), p->getY()), 2, 2);
    } else if (type == wkbPolygon) {
        painter.setPen(QPen(Qt::gray, 1));
        OGRPolygon *poly = (OGRPolygon*)geom;
        drawOGRGeometry(poly->getExteriorRing(), painter);
    } else if (type == wkbMultiLineString || type == wkbMultiPolygon) {
        OGRGeometryCollection *col = (OGRGeometryCollection*)geom;
        for (int i = 0; i < col->getNumGeometries(); i++) drawOGRGeometry(col->getGeometryRef(i), painter);
    }
}

void MapDisplay::set_center(const QPointF &c) { geo_center = c; update(); }
void MapDisplay::resizeEvent(QResizeEvent *event) { Q_UNUSED(event); }
