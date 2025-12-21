#ifndef POINTSET_H
#define POINTSET_H

#include <QVector>
#include <QPainter>
#include <cstdlib> // For rand()
#include "vector2d.h"

class PointSet {
private:
    QVector<Vector2D> points;

public:
    PointSet() {}

    void addPoint(const Vector2D& p) {
        points.push_back(p);
    }

    // Random float generator
    static float myRandom(float xmin, float xmax) {
        float random = static_cast<float>(std::rand()) / static_cast<float>(RAND_MAX);
        return xmin + random * (xmax - xmin);
    }

    //  Square initialization
    void initSquare() {
        points.clear();
        for (int i = 0; i < 50; ++i) {
            float x = myRandom(50, 450);
            float y = myRandom(50, 450);
            points.push_back(Vector2D(x, y));
        }
    }

    // Disc initialization
    void initDisc() {
        points.clear();
        Vector2D center(750, 250);
        float radius = 200.0f;

        for (int i = 0; i < 200; ++i) {
            float theta = myRandom(0, 2 * M_PI);
            // sqrt(random) ensures uniform distribution over area
            float r = radius * std::sqrt(myRandom(0, 1));

            float x = center.x + r * cos(theta);
            float y = center.y + r * sin(theta);
            points.push_back(Vector2D(x, y));
        }
    }

    // Accessor to avoid duplication
    QVector<Vector2D>& getPoints() {
        return points;
    }

    void draw(QPainter &painter) {
        painter.setPen(QPen(Qt::black, 2));
        for (const auto& p : points) {
            painter.drawLine(p.x - 2, p.y - 2, p.x + 2, p.y + 2);
            painter.drawLine(p.x - 2, p.y + 2, p.x + 2, p.y - 2);
        }
    }
};

#endif // POINTSET_H
