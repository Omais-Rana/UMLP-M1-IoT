#include "canvas.h"
#include <QMouseEvent>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>
#include <QFile>

Canvas::Canvas(QWidget *parent) : QWidget(parent) {
    setMouseTracking(true);

}

Canvas::~Canvas() {
    clear();
}

void Canvas::clear() {
    triangles.clear();
    vertices.clear();
}

void Canvas::addPoints(const QVector<Vector2D> &tab) {
    for (auto &pt:tab) {
        // duplicate the point to get a local permanent version
        vertices.push_back(Vector2D(pt));
    }
    reScale();
    update();
}

void Canvas::addTriangle(int id0, int id1, int id2) {
    triangles.push_back(Triangle(&vertices[id0],&vertices[id1],&vertices[id2]));
}

void Canvas::addTriangle(int id0, int id1, int id2,const QColor &color) {
    triangles.push_back(Triangle(&vertices[id0],&vertices[id1],&vertices[id2],color));
}

void Canvas::paintEvent(QPaintEvent *) {
    QPainter painter(this);
    QBrush whiteBrush(Qt::SolidPattern);
    whiteBrush.setColor(Qt::white);
    painter.fillRect(0,0,width(),height(),whiteBrush);

    // draw axes
    QPointF points[7]={{0,-2},{80,-2},{80,-10},{100,0},{80,10},{80,2},{0,2}};
    painter.save();
    painter.translate(20,height()-20);
    painter.setPen(Qt::NoPen);
    painter.setBrush(Qt::red);
    painter.drawPolygon(points,7);
    painter.save();
    painter.setBrush(Qt::green);
    painter.rotate(-90);
    painter.drawPolygon(points,7);
    painter.restore();
    painter.restore();

    painter.save();
    painter.translate(10,height()-10);
    painter.scale(scale,-scale);
    painter.translate(-origin.x(),-origin.y());

    // draw triangles
    if (showTriangles) {
        for (auto &tri:triangles) {
            tri.draw(painter);
        }
    }
    // draw circle
    if (showCircles) {
        for (auto &tri:triangles) {
            if (tri.isHighlighted()) tri.drawCircle(painter);
        }
    }
    // draw points
    if (showCenters) {
        painter.setPen(QPen(Qt::black,3));
        for (auto &tri:triangles) {
            auto pt = tri.getCircleCenter();
            painter.drawLine(pt.x-15,pt.y-15,pt.x+15,pt.y+15);
            painter.drawLine(pt.x-15,pt.y+15,pt.x+15,pt.y-15);
        }
    }
    painter.restore();
    // draw the text in basic coordinate system
    int s=width()/100;
    QFont font("Times",s,QFont::Normal);
    painter.setFont(font);
    painter.setPen(QPen(Qt::black));
    const QRect rect(-3*s,-2.5*s,3*s,2.5*s);
    int i=0;
    for (auto &v:vertices) {
        painter.save();
        float x = (v.x-origin.x())*scale+10+1.5*s;
        float y = -(v.y-origin.y())*scale+height()-10+1.25*s;
        painter.translate(x,y);
        painter.fillRect(rect,QBrush(QColor(255,255,255,192)));
        painter.drawText(rect,Qt::AlignCenter|Qt::AlignVCenter,QString::number(i++));
        painter.restore();
    }
}

QPair<Vector2D,Vector2D> Canvas::getBox() {
    if (vertices.empty()) {Vector2D infLeft,supRight;
        return QPair<Vector2D,Vector2D>(Vector2D(0,0),Vector2D(200,200));
    }
    auto pts=vertices.begin();
    Vector2D infLeft(pts->x,pts->y),supRight(pts->x,pts->y);
    while (pts!=vertices.end()) {
        if (pts->x<infLeft.x) infLeft.x=pts->x;
        if (pts->y<infLeft.y) infLeft.y=pts->y;
        if (pts->x>supRight.x) supRight.x=pts->x;
        if (pts->y>supRight.y) supRight.y=pts->y;
        pts++;
    }
    return QPair<Vector2D,Vector2D>(infLeft,supRight);
}

void Canvas::resizeEvent(QResizeEvent *event) {
    reScale();
    update();
}

void Canvas::reScale() {
    int newWidth = width()-20;
    int newHeight = height()-20;
    auto box=getBox();
    float dataWidth=box.second.x-box.first.x;
    float dataHeight=box.second.y-box.first.y;
    scale=qMin(float(newWidth)/float(dataWidth),float(newHeight)/float(dataHeight)  );
    origin.setX(box.first.x);
    origin.setY(box.first.y);
}

void Canvas::mouseMoveEvent(QMouseEvent *event) {
    float mouseX=float(event->pos().x()-10)/scale+origin.x();
    float mouseY=-float(event->pos().y()-height()+10)/scale+origin.y();

    for (auto &tri:triangles) {
        tri.setHighlighted(tri.isInside(mouseX,mouseY));
    }
    update();
}

void Canvas::loadMesh(const QString &title) {
    QFile file(title);
    if (file.open(QIODevice::ReadOnly|QIODevice::Text)) {
        clear();
        QString JSON=file.readAll();
        file.close();

        QJsonDocument doc = QJsonDocument::fromJson(JSON.toUtf8());
        QJsonArray JSONvertices = doc["vertices"].toArray();
        vertices.resize(JSONvertices.size());
        qDebug() << "Vertices:" << JSONvertices.size();
        for (auto &&v:JSONvertices) {
            QJsonObject vector=v.toObject();
            qDebug() << vector["position"].toString() << "," << vector["id"].toInt();
            auto strPosition = vector["position"].toString().split(',');
            Vector2D pt(strPosition[0].toFloat(),strPosition[1].toFloat());
            auto intId = vector["id"].toInt();
            vertices[intId]=pt;
        }

        QJsonArray JSONtriangles = doc["triangles"].toArray();
        qDebug() << "Triangles:" << JSONtriangles.size();
        for (auto &&v:JSONtriangles) {
            QJsonObject vector=v.toObject();
            qDebug() << vector["tri"].toString() << "," << vector["color"];
            auto tri = vector["tri"].toString().split(',');
            auto color = vector["color"].toString();
            if (tri.size()==3) {
                addTriangle(tri[0].toInt(),tri[1].toInt(),tri[2].toInt(),QColor(color));
            }
        }
    }
    reScale();
    update();
}
