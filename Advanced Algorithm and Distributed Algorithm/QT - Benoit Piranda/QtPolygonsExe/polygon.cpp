#include "polygon.h"
#include <QDebug>

void Polygon::addVertex(float x, float y) {
    if (tabPts.empty()) {
        tabPts.push_back(Vector2D(x,y));
        tabPts.push_back(Vector2D(x,y));
    } else {
        auto N=tabPts.size()-1;
        tabPts[N].x=x;
        tabPts[N].y=y;
///< the last vertex duplicates the first one
        tabPts.push_back(tabPts[0]);
    }
}

void Polygon::draw(QPainter &painter,bool showTriangles) const {
    QPen pen(Qt::black);
    pen.setWidth(3);

    ///< use the drawPolygon method of QPainter
    auto N=tabPts.size();
    QPoint *points=new QPoint[N];
    for (int i=0; i<N; i++) {
        points[i].setX(tabPts[i].x);
        points[i].setY(tabPts[i].y);
    }
    painter.setBrush(currentColor);
    painter.setPen(pen);
    painter.drawPolygon(points,N,Qt::OddEvenFill);
    delete [] points;

    if (showTriangles) { // draw triangles
        painter.setPen(Qt::blue);
        for (auto &t:triangles) {
            painter.drawLine(t[0]->x,t[0]->y,t[1]->x,t[1]->y);
            painter.drawLine(t[1]->x,t[1]->y,t[2]->x,t[2]->y);
            painter.drawLine(t[2]->x,t[2]->y,t[0]->x,t[0]->y);
        }
    }
}

QPair<Vector2D,Vector2D> Polygon::getBoundingBox() const {
    Vector2D min=tabPts[0],max=tabPts[0];
    for (auto &pt:tabPts) {
        if (pt.x<min.x) min.x = pt.x;
        if (pt.y<min.y) min.y = pt.y;
        if (pt.x>max.x) max.x = pt.x;
        if (pt.y>max.y) max.y = pt.y;
    }
    return QPair<Vector2D,Vector2D>(min,max);
}
