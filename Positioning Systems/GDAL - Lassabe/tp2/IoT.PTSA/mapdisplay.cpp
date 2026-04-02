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

    double gMinX = 1e18, gMinY = 1e18, gMaxX = -1e18, gMaxY = -1e18;
    for (int i = 0; i < poDS->GetLayerCount(); i++) {
        OGRLayer *poLayer = poDS->GetLayer(i);
        OGREnvelope envelope;
        if (poLayer->GetExtent(&envelope) == OGRERR_NONE) {
            double lx = envelope.MinX, ly = envelope.MinY, hx = envelope.MaxX, hy = envelope.MaxY;
            poCT->Transform(1, &lx, &ly);
            poCT->Transform(1, &hx, &hy);
            gMinX = std::min(gMinX, lx); gMinY = std::min(gMinY, ly);
            gMaxX = std::max(gMaxX, hx); gMaxY = std::max(gMaxY, hy);
        }

        poLayer->ResetReading();
        OGRFeature *poFeature;
        while ((poFeature = poLayer->GetNextFeature()) != NULL) {
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

void MapDisplay::paintEvent(QPaintEvent *event) {
    Q_UNUSED(event);
    setlocale(LC_NUMERIC, "C");
    QPainter painter{this};
    painter.setRenderHint(QPainter::Antialiasing);

    // BACKGROUND: Deep Ruby Vignette
    QRadialGradient bg(rect().center(), width());
    bg.setColorAt(0, QColor("#2D0005"));
    bg.setColorAt(1, QColor("#0F0001"));
    painter.fillRect(rect(), bg);

    if (_footprint.isNull() || _geometries.empty()) return;

    // 1. Draw Technical Grid (Major visual change)
    drawBackgroundGrid(painter);

    // 2. Draw Geometries with Glow Effect
    for (OGRGeometry* geom : _geometries) drawOGRGeometry(geom, painter);
    
    // 3. Draw Legend
    drawRubyLegend(painter);
}

void MapDisplay::drawBackgroundGrid(QPainter &painter) {
    painter.setPen(QPen(QColor(255, 0, 0, 30), 1, Qt::DotLine));
    
    // Draw vertical/horizontal grid lines every 200 meters
    double step = 200.0; 
    for (double x = floor(_footprint.left()/step)*step; x < _footprint.right(); x += step) {
        double sx = (x - _footprint.left()) * width() / _footprint.width();
        painter.drawLine(sx, 0, sx, height());
    }
    for (double y = floor(_footprint.top()/step)*step; y < _footprint.bottom(); y += step) {
        double sy = height() - ((y - _footprint.top()) * height() / _footprint.height());
        painter.drawLine(0, sy, width(), sy);
    }
}

void MapDisplay::drawOGRGeometry(OGRGeometry *geom, QPainter &painter) {
    auto toPx = [&](double x, double y) {
        double sx = (x - _footprint.left()) * width() / _footprint.width();
        double sy = height() - ((y - _footprint.top()) * height() / _footprint.height());
        return QPointF(sx, sy);
    };

    OGRwkbGeometryType type = wkbFlatten(geom->getGeometryType());

    if (type == wkbLineString) {
        OGRLineString *ls = (OGRLineString*)geom;
        // THE "GLOW" TRICK: Draw twice
        // 1. Thick dark glow
        painter.setPen(QPen(QColor(255, 0, 0, 80), 3)); 
        for (int i = 0; i < ls->getNumPoints() - 1; i++) 
            painter.drawLine(toPx(ls->getX(i), ls->getY(i)), toPx(ls->getX(i+1), ls->getY(i+1)));
        
        // 2. Thin bright core
        painter.setPen(QPen(QColor("#FF7F7F"), 1)); 
        for (int i = 0; i < ls->getNumPoints() - 1; i++) 
            painter.drawLine(toPx(ls->getX(i), ls->getY(i)), toPx(ls->getX(i+1), ls->getY(i+1)));

    } else if (type == wkbPoint) {
        painter.setBrush(QColor("#FFFFFF"));
        OGRPoint *p = (OGRPoint*)geom;
        painter.drawEllipse(toPx(p->getX(), p->getY()), 2, 2);
    }
}

void MapDisplay::drawRubyLegend(QPainter &painter) {
    // Professional Bottom-Right Legend
    int w = 180, h = 80, m = 15;
    QRect r(width() - w - m, height() - h - m, w, h);

    painter.setOpacity(0.7);
    painter.fillRect(r, Qt::black);
    painter.setOpacity(1.0);
    painter.setPen(QColor("#FF7F7F"));
    painter.drawText(r.adjusted(10,15,0,0), "BELFORT TACTICAL");
    
    painter.setPen(Qt::red);
    painter.drawText(r.adjusted(10,40,0,0), ">> INFRASTRUCTURE");
    painter.setPen(Qt::white);
    painter.drawText(r.adjusted(10,60,0,0), ">> NODE POINTS");
}

// Keep your original math logic
QPointF MapDisplay::pixelToMode(const QPoint &pos) {
    double x = _footprint.left() + (pos.x() * _footprint.width() / width());
    double y = _footprint.top() + ((height() - pos.y()) * _footprint.height() / height());
    return QPointF(x, y);
}

void MapDisplay::wheelEvent(QWheelEvent *event) {
    double ratio = (event->angleDelta().y() > 0) ? 0.8 : 1.2; 
    QPointF target = pixelToMode(event->pos());
    double nw = _footprint.width() * ratio, nh = _footprint.height() * ratio;
    _footprint = QRectF(target.x() - (event->pos().x() * nw / width()), 
                        target.y() - ((height() - event->pos().y()) * nh / height()), nw, nh);
    update();
}

void MapDisplay::mousePressEvent(QMouseEvent *event) {
    if (event->button() == Qt::LeftButton) { _is_panning = true; _last_mouse_pos = event->pos(); }
}

void MapDisplay::mouseMoveEvent(QMouseEvent *event) {
    if (_is_panning) {
        QPoint delta = event->pos() - _last_mouse_pos;
        _footprint.translate(-(delta.x() * _footprint.width() / width()), (delta.y() * _footprint.height() / height()));
        _last_mouse_pos = event->pos();
        update();
    }
}

void MapDisplay::mouseReleaseEvent(QMouseEvent *event) { if (event->button() == Qt::LeftButton) _is_panning = false; }
void MapDisplay::set_center(const QPointF &c) { geo_center = c; update(); }
void MapDisplay::resizeEvent(QResizeEvent *event) { Q_UNUSED(event); }
