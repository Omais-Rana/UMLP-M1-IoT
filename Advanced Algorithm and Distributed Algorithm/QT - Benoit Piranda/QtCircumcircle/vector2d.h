#ifndef VECTOR2D_H
#define VECTOR2D_H

#include <cmath>

class Vector2D {
public:
    float x,y;
    Vector2D(float p_x,float p_y):x(p_x),y(p_y) {};
    Vector2D() { x=y=0.0; }
    Vector2D(Vector2D *p):x(p->x),y(p->y) {};

    void set(double p_x,double p_y) { x=p_x; y=p_y; }
    double length() const {
        return sqrt(x*x+y*y);
    }
    Vector2D orthoNormed() const {
        float l=length();
        return Vector2D(y/l,-x/l);
    }

    float operator[](const int i) const {
        return (i==0)?x:y;
    };

    friend double operator *(const Vector2D&,const Vector2D&);
    friend const Vector2D operator *(double,const Vector2D&);
    friend const Vector2D operator +(const Vector2D&,const Vector2D&);
    friend const Vector2D operator -(const Vector2D&,const Vector2D&);
    friend const Vector2D operator -(const Vector2D&);
    friend double operator ^(const Vector2D&,const Vector2D&);
    friend bool operator ==(const Vector2D&,const Vector2D&);
    friend bool operator !=(const Vector2D&,const Vector2D&);
};

const Vector2D operator *(double,const Vector2D&);
double operator *(const Vector2D&,const Vector2D&);
const Vector2D operator +(const Vector2D&,const Vector2D&);
const Vector2D operator -(const Vector2D&,const Vector2D&);
const Vector2D operator -(const Vector2D&);
double operator ^(const Vector2D&,const Vector2D&);
bool operator==(const Vector2D&,const Vector2D&);
bool operator !=(const Vector2D&,const Vector2D&);


#endif // VECTOR2D_H
