#ifndef CANVAS_H
#define CANVAS_H

#include <QWidget>
#include <QVector>
#include <QResizeEvent>
#include "polygon.h"

class Canvas : public QWidget {
    Q_OBJECT
 public:
    explicit Canvas(QWidget *parent = nullptr);
    ~Canvas();
    void paintEvent(QPaintEvent*) override;
    void resizeEvent(QResizeEvent*) override;
    void mouseMoveEvent(QMouseEvent*) override;
    /*
     * show/hide the drawing of the triangles
     */
    void showTriangles(bool state) { drawTriangleBorders=state; repaint(); }
    void showDistance(bool state) { drawDistance=state; repaint(); }
signals:
    /*
     * Update the status bar string with the parameter
     */
    void updateSB(QString s);
private:
    QVector<Polygon*> polygons; // the array of polygons
    float scale; // scale of the drawing to fill the window
    QPoint origin; // origin of the drawing area
    bool drawTriangleBorders=false; // show/hide the drawing of the triangles
    bool drawDistance=false; // show/hide the drawing of the distance
    QPair<QPointF,QPointF> distanceVector;
};

#endif // CANVAS_H
