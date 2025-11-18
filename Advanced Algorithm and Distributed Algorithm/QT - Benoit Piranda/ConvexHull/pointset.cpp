#ifndef POINTSET_H
#define POINTSET_H

#include <QVector>
#include <QPainter>
#include "vector2d.h"

class PointSet {
private:
    QVector<Vector2D> points;

public:
    PointSet() {}

    /**
     * @brief Adds a point to the set.
     */
    void addPoint(const Vector2D& p) {
        points.push_back(p);
    }

    /**
     * @brief Generates a random float between xmin and xmax (Q2).
     */
    static float myRandom(float xmin, float xmax) {
        float random = static_cast<float>(rand()) / static_cast<float>(RAND_MAX);
        return xmin + random * (xmax - xmin);
    }

    /**
     * @brief initSquare Generates 50 points in a square (Q3.1).
     */
    void initSquare() {
        points.clear();
        for (int i = 0; i < 50; ++i) {
            float x = myRandom(50, 450);
            float y = myRandom(50, 450);
            points.push_back(Vector2D(x, y));
        }
    }

    /**
     * @brief initDisc Generates 200 points in a disc (Q3.2).
     */
    void initDisc() {
        points.clear();
        Vector2D center(750, 250);
        float radius = 200.0f;

        for (int i = 0; i < 200; ++i) {
            // Random angle
            float theta = myRandom(0, 2 * M_PI);
            // Random radius (sqrt used for uniform distribution area-wise)
            float r = radius * sqrt(myRandom(0, 1));

            float x = center.x + r * cos(theta);
            float y = center.y + r * sin(theta);
            points.push_back(Vector2D(x, y));
        }
    }

    /**
     * @brief Accessor for the points (Q6 - needed for Polygon constructor).
     * Pass by reference to avoid duplication.
     */
    QVector<Vector2D>& getPoints() {
        return points;
    }

    /**
     * @brief Draws the points as small crosses.
     */
    void draw(QPainter &painter) {
        painter.setPen(QPen(Qt::black, 2));
        for (const auto& p : points) {
            painter.drawLine(p.x - 2, p.y - 2, p.x + 2, p.y + 2);
            painter.drawLine(p.x - 2, p.y + 2, p.x + 2, p.y - 2);
        }
    }
};

#endif // POINTSET_H
