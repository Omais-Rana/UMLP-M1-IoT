#ifndef VECTOR2D_H
#define VECTOR2D_H

#include <QtMath>
class Vector2D {
public:
    float x,y;
    Vector2D(float p_x,float p_y):x(p_x),y(p_y) {};
    Vector2D():x(0),y(0) {};
    Vector2D operator+(const Vector2D &op) const {
        return Vector2D(x+op.x,y+op.y);
    }
    Vector2D operator-(const Vector2D &op) const {
        return Vector2D(x-op.x,y-op.y);
    }
    Vector2D operator*(double op) const {
        return Vector2D(op*x,op*y);
    }
    /*
     * return a normed vector
     */
    Vector2D normed() const {
        float d = sqrt(x*x+y*y);
        return Vector2D(x/d,y/d);
    }
    /*
     * return an orthogonal vector
     */
    Vector2D ortho() const {
        return Vector2D(-y,x);
    }

    double length() const {
        return sqrt(x*x+y*y);
    }
};

#endif // VECTOR2D_H
