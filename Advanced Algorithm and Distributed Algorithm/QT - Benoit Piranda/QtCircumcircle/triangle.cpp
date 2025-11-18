#include "triangle.h"

void Triangle::computeCircle() {
    circumCenter = (1.0/3.0)*(*ptr[0] + *ptr[1] + *ptr[2]);
    circumRadius = (circumCenter-ptr[0]).length();
}

void Triangle::draw(QPainter &painter) {
    QPen pen(Qt::black);
    pen.setWidth(3);
    painter.setPen(pen);
    painter.setBrush(isHighlited?(isDelaunay?Qt::green:Qt::red):Qt::yellow);
    QPointF points[3];
    for (int i=0; i<3; i++) {
        points[i].setX(ptr[i]->x);
        points[i].setY(ptr[i]->y);
    }
    painter.drawPolygon(points,3);
}

void Triangle::drawCircle(QPainter &painter) {
    painter.setPen(QPen(Qt::black,3,Qt::DashLine));
    painter.setBrush(Qt::NoBrush);
    painter.drawEllipse(circumCenter.x-circumRadius,circumCenter.y-circumRadius,2.0*circumRadius,2.0*circumRadius);
}
