#include "canvas.h"
#include <QPainter>
#include <QDebug>
#include <QMouseEvent>
#include <QDebug>
#include <limits>

Canvas::Canvas(QWidget *parent) : QWidget(parent) {
    setMouseTracking(true);
    drawTriangleBorders=false;
    Polygon *S1 = new Polygon();
    S1->addVertex(120,40);
    S1->addVertex(400,160);
    S1->addVertex(320,400);
    S1->addVertex(40,80);
    polygons.push_back(S1);
    bool s1_convex = S1->isConvex(); // Store result
    S1->setColor(s1_convex?Qt::green:Qt::yellow);
    qDebug() << "S1 is convex:" << s1_convex; // Show convexity
    S1->triangulate();
    qDebug() << "S1.area = " << S1->area() << " u²";

    Polygon *S2 = new Polygon();
    S2->addVertex(620,40);
    S2->addVertex(820,60);
    S2->addVertex(900,160);
    S2->addVertex(820,400);
    polygons.push_back(S2);
    bool s2_convex = S2->isConvex(); // Store result
    S2->setColor(s2_convex?Qt::green:Qt::yellow);
    qDebug() << "S2 is convex:" << s2_convex; // Show convexity
    S2->triangulate();
    qDebug() << "S2.area = " << S2->area() << " u²";

    Polygon *S3 = new Polygon();
    S3->addVertex(500,500);
    S3->addVertex(800,600);
    S3->addVertex(900,900);
    S3->addVertex(400,900);
    S3->addVertex(680,800);
    S3->addVertex(640,650);
    S3->addVertex(240,680);
    polygons.push_back(S3);
    bool s3_convex = S3->isConvex(); // Store result
    S3->setColor(s3_convex?Qt::green:Qt::yellow);
    qDebug() << "S3 is convex:" << s3_convex; // Show convexity
    S3->triangulate();
    qDebug() << "S3.area = " << S3->area() << " u²";
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
    // search max x and y of the polygons
    auto globalBB=QPair<Vector2D,Vector2D>(Vector2D(width(),height()),Vector2D(0,0));
    for (auto &poly:polygons) {
        auto bb = poly->getBoundingBox();
        if (globalBB.first.x>bb.first.x) globalBB.first.x=bb.first.x;
        if (globalBB.first.y>bb.first.y) globalBB.first.y=bb.first.y;
        if (globalBB.second.x<bb.second.x) globalBB.second.x=bb.second.x;
        if (globalBB.second.y<bb.second.y) globalBB.second.y=bb.second.y;
    }
    float maxx = globalBB.second.x-fmin(globalBB.first.x,0);
    float maxy = globalBB.second.y-fmin(globalBB.first.y,0);
    float maxs = maxx>maxy?maxx:maxy;
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

void Canvas::paintEvent(QPaintEvent *) {
    QPainter painter(this);
    QBrush whiteBrush(Qt::SolidPattern);
    whiteBrush.setColor(Qt::black);
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
    painter.restore ();

    // drawing of the polygons
    for (auto &poly: polygons) {
        poly->draw(painter,drawTriangleBorders);
    }

    if (drawDistance) {
        painter.setPen(QPen(Qt::blue,5));
        painter.drawLine(distanceVector.first,distanceVector.second);
    }
    painter.restore();

    // draw points in a standard referential
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
    // convert event->pos() to canvas coordinates
    float mouseX=float(e->pos().x()-origin.x())/scale;
    float mouseY=-float(e->pos().y()-height()+origin.y())/scale;
    Vector2D pt(mouseX,mouseY); // Mouse point

    // check if point is in the polygon
    for (auto &poly:polygons) {
        poly->changeColor(pt);
    }

    if (drawDistance) {
        float min_dist_sq = std::numeric_limits<float>::max();
        Vector2D closest_point_on_any_edge(pt.x, pt.y);

        for (auto &poly : polygons) {
            int n = poly->nbVertices();
            for (int i = 0; i < n; i++) {
                // Get the two endpoints of the edge
                Vector2D A = (*poly)[i];
                Vector2D B = (*poly)[i+1];
                Vector2D AB = B - A; // Vector for the edge
                Vector2D AP = pt - A; // Vector from A to the mouse point

                // Project AP onto AB to find the closest point on the *line*
                float ab_len_sq = AB.x * AB.x + AB.y * AB.y;
                float ap_dot_ab = AP.x * AB.x + AP.y * AB.y;

                // t is the projection ratio.
                // t=0 means closest point is A. t=1 means closest point is B.
                float t = ap_dot_ab / ab_len_sq;

                Vector2D closest_point_on_segment;
                if (t < 0.0f) {
                    // Closest point is A
                    closest_point_on_segment = A;
                } else if (t > 1.0f) {
                    // Closest point is B
                    closest_point_on_segment = B;
                } else {
                    // Closest point is between A and B
                    closest_point_on_segment = A + AB * t;
                }

                // Calculate squared distance from pt to the closest point on the segment
                Vector2D dist_vec = pt - closest_point_on_segment;
                float dist_sq = dist_vec.x * dist_vec.x + dist_vec.y * dist_vec.y;

                // Update if this is the new minimum distance
                if (dist_sq < min_dist_sq) {
                    min_dist_sq = dist_sq;
                    closest_point_on_any_edge = closest_point_on_segment;
                }
            }
        }

        // Store the line for paintEvent to draw
        distanceVector.first = QPointF(pt.x, pt.y);
        distanceVector.second = QPointF(closest_point_on_any_edge.x, closest_point_on_any_edge.y);

        // Update status bar with the distance (in u, not pixels)
        float min_dist = sqrt(min_dist_sq);

        emit updateSB(QString("Distance = ") + QString::number(min_dist,'f',1) + " u");

    } else {
        emit updateSB(QString("Mouse position= (") + QString::number(mouseX,'f',1) + "," + QString::number(mouseY,'f',1) + ")");
    }
    update();
}
