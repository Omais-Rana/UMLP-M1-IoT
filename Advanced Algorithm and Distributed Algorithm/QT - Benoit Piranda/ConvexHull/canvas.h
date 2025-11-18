#ifndef CANVAS_H
#define CANVAS_H

#include <QWidget>
#include <QVector>
#include <QResizeEvent>
#include "polygon.h"
#include "pointset.h"

class Canvas : public QWidget {
    Q_OBJECT
public:
    explicit Canvas(QWidget *parent = nullptr);
    ~Canvas();
    void paintEvent(QPaintEvent*) override;
    void resizeEvent(QResizeEvent*) override;
    void mouseMoveEvent(QMouseEvent*) override;

    // Added: Handle case when mouse leaves window to reset colors
    void leaveEvent(QEvent *event) override;

    void showTriangles(bool state) { drawTriangleBorders=state; repaint(); }
    void showDistance(bool state) { drawDistance=state; repaint(); }
signals:
    void updateSB(QString s);
private:
    QVector<Polygon*> polygons;
    PointSet psSquare;
    PointSet psDisc;
    float scale;
    QPoint origin;
    bool drawTriangleBorders=false;
    bool drawDistance=false;
    QPair<QPointF,QPointF> distanceVector;
};

#endif // CANVAS_H
