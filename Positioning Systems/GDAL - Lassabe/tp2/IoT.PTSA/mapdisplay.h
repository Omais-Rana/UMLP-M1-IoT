#ifndef MAPDISPLAY_H
#define MAPDISPLAY_H

#include <QWidget>
#include <QRectF>
#include <QPointF>
#include <vector>
#include <ogrsf_frmts.h>

class MapDisplay : public QWidget {
    Q_OBJECT

    QPointF geo_center;
    QRectF _footprint; 
    std::vector<OGRGeometry*> _geometries;
    
    QPoint _last_mouse_pos;
    bool _is_panning = false;

public:
    explicit MapDisplay(QWidget *parent = nullptr);
    ~MapDisplay();

    void set_center(const QPointF &c);
    const QPointF &get_geo_center() const { return geo_center; }

protected:
    void resizeEvent(QResizeEvent *event) override;
    void paintEvent(QPaintEvent *event) override;
    
    void mousePressEvent(QMouseEvent *event) override;
    void mouseMoveEvent(QMouseEvent *event) override;
    void mouseReleaseEvent(QMouseEvent *event) override;
    void wheelEvent(QWheelEvent *event) override;

private:
    void drawOGRGeometry(OGRGeometry *geom, QPainter &painter);
    QPointF pixelToMode(const QPoint &pos);
};

#endif // MAPDISPLAY_H
