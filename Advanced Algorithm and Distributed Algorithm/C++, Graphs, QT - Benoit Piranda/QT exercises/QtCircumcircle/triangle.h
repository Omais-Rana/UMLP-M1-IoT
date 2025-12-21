#ifndef TRIANGLE_H
#define TRIANGLE_H

#include <QPainter>
#include <vector2d.h>

class Triangle {
    Vector2D *ptr[3]; ///< array of 3 pointers on the vertices
    Vector2D circumCenter; ///< the center of the triangle (by computeCenter)
    float circumRadius; ///< the radius of the circumCircle (by computeCenter)
    QBrush brush; ///< current brush to draw the triangle
    bool isHighlited=false; ///< is drawn highlighted
    bool isDelaunay=false; ///< is drawn as Delaunay triangle
    /**
     * @brief Compute the circum circle from the vertices
     */
    void computeCircle();
public:
    Triangle(Vector2D *ptr1,Vector2D *ptr2,Vector2D *ptr3) {
        ptr[0]=ptr1;
        ptr[1]=ptr2;
        ptr[2]=ptr3;
        setColor(Qt::yellow);
        computeCircle();
    }

    Triangle(Vector2D *ptr1,Vector2D *ptr2,Vector2D *ptr3,const QColor &p_color) {
        ptr[0]=ptr1;
        ptr[1]=ptr2;
        ptr[2]=ptr3;
        setColor(p_color);
        computeCircle();
    }

    bool isOnTheLeft(const Vector2D *P,const Vector2D *P1,const Vector2D *P2) {
        Vector2D AB = *P2-*P1,
                AP = *P-*P1;

        return (AB.x*AP.y - AB.y*AP.x)>=0;
    }

    inline bool isInside(float x,float y) {
        return isInside(Vector2D(x,y));
    }

    bool isInside(const Vector2D &P) {
        return isOnTheLeft(&P,ptr[0],ptr[1]) &&
               isOnTheLeft(&P,ptr[1],ptr[2]) &&
               isOnTheLeft(&P,ptr[2],ptr[0]);
    }

    void draw(QPainter &painter);
    void drawCircle(QPainter &painter);

    void setColor(const QColor &p_color) {
        brush.setStyle(Qt::BrushStyle::SolidPattern);
        brush.setColor(p_color);
    }

    inline void setHighlighted(bool v) {
        isHighlited=v;
    }
    inline bool isHighlighted() {
        return isHighlited;
    }
    inline Vector2D getCircleCenter() {
        return circumCenter;
    }
    inline void setDelaunay(bool v) {
        isDelaunay=v;
    }
};

#endif // TRIANGLE_H
