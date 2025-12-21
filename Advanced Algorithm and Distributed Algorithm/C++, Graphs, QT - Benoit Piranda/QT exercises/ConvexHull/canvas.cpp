#include "canvas.h"
#include <QPainter>
#include <QDebug>
#include <QMouseEvent>
#include <limits> // Required for std::numeric_limits

Canvas::Canvas(QWidget *parent) : QWidget(parent) {
    setMouseTracking(true);
    drawTriangleBorders=false;

    psSquare.initSquare();
    psDisc.initDisc();

    // Convex Hull for Square
    Polygon *polySquare = new Polygon(psSquare.getPoints());
    polygons.push_back(polySquare);
    polySquare->triangulate();

    // Convex Hull for Disc
    Polygon *polyDisc = new Polygon(psDisc.getPoints());
    polygons.push_back(polyDisc);
    polyDisc->triangulate();
}

Canvas::~Canvas() {
    for (auto &poly : polygons) {
        delete poly;
    }
    polygons.clear();
}

void Canvas::resizeEvent(QResizeEvent *event) {
    int newWidth = event->size().width();
    int newHeight = event->size().height();
    auto globalBB=QPair<Vector2D,Vector2D>(Vector2D(width(),height()),Vector2D(0,0));

    // If there are no polygons, set a default bounding box to avoid crashes
    if (polygons.empty()) {
        globalBB = QPair<Vector2D,Vector2D>(Vector2D(0,0), Vector2D(1000, 1000));
    } else {
        for (auto &poly:polygons) {
            auto bb = poly->getBoundingBox();
            if (globalBB.first.x>bb.first.x) globalBB.first.x=bb.first.x;
            if (globalBB.first.y>bb.first.y) globalBB.first.y=bb.first.y;
            if (globalBB.second.x<bb.second.x) globalBB.second.x=bb.second.x;
            if (globalBB.second.y<bb.second.y) globalBB.second.y=bb.second.y;
        }
    }

    float maxx = globalBB.second.x-fmin(globalBB.first.x,0);
    float maxy = globalBB.second.y-fmin(globalBB.first.y,0);
    float maxs = maxx>maxy?maxx:maxy;

    // Avoid division by zero if maxs is 0
    if (maxs == 0) maxs = 1;

    if (newWidth>newHeight) {
        scale=newHeight/(maxs+40);
        origin.setY(10);
        origin.setX((newWidth-newHeight)/2);
    } else {
        scale=newWidth/(maxs+40);
        origin.setX(10);
        origin.setY((newHeight-newWidth)/2);
    }
}

// Resets colors when mouse leaves the widget
void Canvas::leaveEvent(QEvent *event) {
    for (auto &poly : polygons) {
        poly->resetColor();
    }
    update();
    QWidget::leaveEvent(event);
}

void Canvas::paintEvent(QPaintEvent *) {
    QPainter painter(this);
    QBrush whiteBrush(Qt::SolidPattern);
    whiteBrush.setColor(Qt::white);
    painter.fillRect(0,0,width(),height(),whiteBrush);

    QPointF points[]={{0,-2},{80,-2},{80,-10},{100,0},{80,10},{80,2},{0,2}};

    painter.save();
    painter.translate(origin.x(),height()-origin.y());
    painter.scale(scale,-scale);

    painter.setPen(Qt::NoPen);
    painter.setBrush(Qt::red);
    painter.drawPolygon(points,7);
    painter.save();
    painter.setBrush(Qt::green);
    painter.rotate(90);
    painter.drawPolygon(points ,7);
    painter.restore();

    // Draw Polygons
    for (auto &poly: polygons) {
        poly->draw(painter,drawTriangleBorders);
    }

    // Draw Distance Line
    if (drawDistance) {
        painter.setPen(QPen(Qt::blue,5));
        painter.drawLine(distanceVector.first,distanceVector.second);
    }

    // Draw Point Sets
    psSquare.draw(painter);
    psDisc.draw(painter);

    painter.restore();
    painter.setPen(Qt::black);

    // Draw Labels (Outside transform)
    QFont font("Arial") ;
    font.setPointSize(18);
    painter.setBrush(Qt::black);
    painter.setFont(font);
    Vector2D v;
    for (auto &poly:polygons) {
        int n=poly->nbVertices();
        for (int i=0; i<n; i++) {
            v = ((*poly)[i+1]-(*poly)[i]).normed().ortho();
            painter.drawText(QPointF(origin.x()+(*poly)[i].x*scale-20*v.x,height()-origin.y()-(*poly)[i].y*scale+20*v.y),QString::number(i));
        }
    }
}

void Canvas::mouseMoveEvent(QMouseEvent*e) {
    float mouseX=float(e->pos().x()-origin.x())/scale;
    float mouseY=-float(e->pos().y()-height()+origin.y())/scale;
    Vector2D pt(mouseX,mouseY);

    // Picking check
    for (auto &poly:polygons) {
        poly->changeColor(pt);
    }

    if (drawDistance) {
        float min_dist_sq = std::numeric_limits<float>::max();
        Vector2D closest_point_on_any_edge(pt.x, pt.y);

        // Only run logic if polygons exist
        bool found = false;

        for (auto &poly : polygons) {
            int n = poly->nbVertices();
            for (int i = 0; i < n; i++) {
                Vector2D A = (*poly)[i];
                Vector2D B = (*poly)[i+1];
                Vector2D AB = B - A;
                Vector2D AP = pt - A;

                float ab_len_sq = AB.x * AB.x + AB.y * AB.y;
                float ap_dot_ab = AP.x * AB.x + AP.y * AB.y;
                float t = 0;
                if(ab_len_sq != 0) t = ap_dot_ab / ab_len_sq;

                Vector2D closest_point_on_segment;
                if (t < 0.0f) closest_point_on_segment = A;
                else if (t > 1.0f) closest_point_on_segment = B;
                else closest_point_on_segment = A + AB * t;

                Vector2D dist_vec = pt - closest_point_on_segment;
                float dist_sq = dist_vec.x * dist_vec.x + dist_vec.y * dist_vec.y;

                if (dist_sq < min_dist_sq) {
                    min_dist_sq = dist_sq;
                    closest_point_on_any_edge = closest_point_on_segment;
                    found = true;
                }
            }
        }

        if(found) {
            distanceVector.first = QPointF(pt.x, pt.y);
            distanceVector.second = QPointF(closest_point_on_any_edge.x, closest_point_on_any_edge.y);
            float min_dist = sqrt(min_dist_sq);
            emit updateSB(QString("Distance = ") + QString::number(min_dist,'f',1) + " u");
        }

    } else {
        emit updateSB(QString("Mouse position= (") + QString::number(mouseX,'f',1) + "," + QString::number(mouseY,'f',1) + ")");
    }
    update();
}
