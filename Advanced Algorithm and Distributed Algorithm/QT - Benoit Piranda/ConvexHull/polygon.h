/**
 * @author Benoît Piranda
 * @date 7/11/2024
 **/

#ifndef POLYGON_H
#define POLYGON_H

#include "vector2d.h"
#include "pointset.h"
#include <QPainter>
#include <QDebug>
#include <cmath>
#include <algorithm>

class Triangle {
    Vector2D *tabPts[3];
public:
    Triangle(Vector2D *p_p0,Vector2D *p_p1,Vector2D *p_p2) {
        tabPts[0]=p_p0;
        tabPts[1]=p_p1;
        tabPts[2]=p_p2;
    }

    const Vector2D* operator[](int i) const { return tabPts[i]; }

    float area() const {
        const Vector2D &p0 = *tabPts[0];
        const Vector2D &p1 = *tabPts[1];
        const Vector2D &p2 = *tabPts[2];
        return 0.5f * std::abs(p0.x * (p1.y - p2.y) + p1.x * (p2.y - p0.y) + p2.x * (p0.y - p1.y));
    }
};

class Polygon {
private:
    QVector<Vector2D> tabPts;
    QVector<Triangle> triangles;
    QColor currentColor;
    QColor originalColor;

    static Vector2D anchor; // For Graham Scan sorting

    // Helper: Returns true if C is left of or on edge AB
    bool isLeftOf(const Vector2D &A, const Vector2D &B, const Vector2D &C) const {
        Vector2D AB = B - A;
        Vector2D AC = C - A;
        return (AB.x * AC.y - AB.y * AC.x) >= 0;
    }

public:
    Polygon(): currentColor(Qt::green), originalColor(Qt::green) {}

    // Constructor for Graham Scan
    Polygon(QVector<Vector2D> &points);

    static bool polarComparison(const Vector2D& a, const Vector2D& b);

    int nbVertices() const { return tabPts.size()==0?0:tabPts.size()-1; }
    void addVertex(float x,float y);
    QPair<Vector2D,Vector2D> getBoundingBox() const;

    void setColor(const QColor c) {
        currentColor = c;
        originalColor = c;
    }

    void resetColor() {
        currentColor = originalColor;
    }

    // Picking Logic
    void changeColor(const Vector2D &pt) {
        bool isInside = false;
        // We check against the triangles (Ear Clipping result)
        for (const auto& tri : triangles) {
            const Vector2D &v0 = *tri[0];
            const Vector2D &v1 = *tri[1];
            const Vector2D &v2 = *tri[2];

            bool leftOf_v0v1 = ((v1.x - v0.x) * (pt.y - v0.y) - (v1.y - v0.y) * (pt.x - v0.x)) >= 0;
            bool leftOf_v1v2 = ((v2.x - v1.x) * (pt.y - v1.y) - (v2.y - v1.y) * (pt.x - v1.x)) >= 0;
            bool leftOf_v2v0 = ((v0.x - v2.x) * (pt.y - v2.y) - (v0.y - v2.y) * (pt.x - v2.x)) >= 0;

            if (leftOf_v0v1 && leftOf_v1v2 && leftOf_v2v0) {
                isInside = true;
                break;
            }
        }

        if (isInside) {
            currentColor = Qt::magenta;
        } else {
            currentColor = originalColor;
        }
    }

    Vector2D operator[](int i) const { return tabPts[i]; }
    void draw(QPainter &painter,bool showTriangles) const;

    // Ear Clipping Triangulation
    void triangulate() {
        triangles.clear();
        int n_total = nbVertices();
        if (n_total < 3) return;

        QVector<Vector2D*> vertexPtrs;
        for (int i = 0; i < n_total; ++i) vertexPtrs.push_back(&tabPts[i]);

        int n = vertexPtrs.size();
        int safeguard = n * 2;

        while (n > 3 && safeguard > 0) {
            safeguard--;
            bool earFound = false;

            for (int i = 0; i < n; ++i) {
                int idx0 = i;
                int idx1 = (i + 1) % n;
                int idx2 = (i + 2) % n;

                Vector2D *p0 = vertexPtrs[idx0];
                Vector2D *p1 = vertexPtrs[idx1];
                Vector2D *p2 = vertexPtrs[idx2];

                if (isLeftOf(*p0, *p1, *p2)) {
                    bool pointInside = false;
                    for (int j = 0; j < n; ++j) {
                        if (j == idx0 || j == idx1 || j == idx2) continue;
                        Vector2D *pt = vertexPtrs[j];
                        if (isLeftOf(*p0, *p1, *pt) && isLeftOf(*p1, *p2, *pt) && isLeftOf(*p2, *p0, *pt)) {
                            pointInside = true;
                            break;
                        }
                    }

                    if (!pointInside) {
                        triangles.push_back(Triangle(p0, p1, p2));
                        vertexPtrs.remove(idx1);
                        n = vertexPtrs.size();
                        earFound = true;
                        break;
                    }
                }
            }
            if (!earFound && safeguard <= 0) return;
        }

        if (vertexPtrs.size() == 3) {
            triangles.push_back(Triangle(vertexPtrs[0], vertexPtrs[1], vertexPtrs[2]));
        }
    };

    bool isOnTheLeft(const Vector2D &p, int i) const {
        Vector2D AB = tabPts[i+1] - tabPts[i];
        Vector2D AP = p - tabPts[i];
        return (AB.x * AP.y - AB.y * AP.x) >= 0;
    }

    bool isConvex() const {
        int N = nbVertices();
        if (N < 3) return false;
        int i = 0;
        while (i < N && isOnTheLeft(tabPts[(i + 2) % N], i)) {
            i++;
        }
        return i >= N;
    }

    bool isInside(const Vector2D &p) const {
        int N = nbVertices();
        if (N < 3) return false;
        for (int i = 0; i < N; ++i) {
            if (!isOnTheLeft(p, i)) return false;
        }
        return true;
    }

    float area() const {
        float totalAreaInPixels = 0.0f;
        for (const auto& tri : triangles) {
            totalAreaInPixels += tri.area();
        }
        return totalAreaInPixels / 100.0f;
    }
};

#endif // POLYGON_H
