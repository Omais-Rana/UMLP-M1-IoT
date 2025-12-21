#include "polygon.h"
#include <QDebug>
#include <QtMath> // Required for std::atan2

// Initialize the static 'anchor' point.
// This point is used by the polarComparison function as the reference
Vector2D Polygon::anchor = Vector2D(0,0);

/**
 * @brief Adds a new vertex to the polygon's internal vertex list (tabPts).
 *
 * This method enforces a specific "closed loop" structure in the tabPts vector.
 * The first vertex is always duplicated at the very end of the vector.
 */
void Polygon::addVertex(float x, float y) {
    if (tabPts.empty()) {
        // If this is the first vertex, add it twice e.g tabPts = [P0, P0]
        tabPts.push_back(Vector2D(x,y));
        tabPts.push_back(Vector2D(x,y));
    } else {
        // If not empty, overwrite the last point with the new vertex's coordinates.
        auto N = tabPts.size()-1;
        tabPts[N].x = x;
        tabPts[N].y = y;
        // Then, add the first vertex again to re-close the loop e.g [P0, P1, P0] becomes [P0, P1, P2, P0]
        tabPts.push_back(tabPts[0]);
    }
}

/**
 * @brief Renders the polygon onto the QPainter context.
 * @param painter The QPainter from the Canvas's paintEvent.
 * @param showTriangles If true, draws the internal triangulation.
 */
void Polygon::draw(QPainter &painter, bool showTriangles) const {
    QPen pen(Qt::black);
    pen.setWidth(3);

    // Convert our Vector2D list to a QPoint array, as QPainter::drawPolygon expects QPoint.
    auto N = tabPts.size();
    QPoint *points = new QPoint[N];
    for (int i=0; i<N; i++) {
        points[i].setX(tabPts[i].x);
        points[i].setY(tabPts[i].y);
    }

    // Draw the main polygon shape
    painter.setBrush(currentColor);
    painter.setPen(pen);
    painter.drawPolygon(points, N, Qt::OddEvenFill);
    delete [] points;

    // Draw the internal triangles (from Ear Clipping)
    if (showTriangles) {
        painter.setPen(Qt::blue);
        for (auto &t : triangles) {
            painter.drawLine(t[0]->x, t[0]->y, t[1]->x, t[1]->y);
            painter.drawLine(t[1]->x, t[1]->y, t[2]->x, t[2]->y);
            painter.drawLine(t[2]->x, t[2]->y, t[0]->x, t[0]->y);
        }
    }
}

/**
 * @brief Calculates the axis-aligned bounding box of the polygon.
 * @return A QPair holding the (min_corner, max_corner) vectors.
 *
 * This is used by the Canvas to calculate the global scale and origin,
 * ensuring all drawn objects fit within the viewport.
 */
QPair<Vector2D,Vector2D> Polygon::getBoundingBox() const {
    // We can safely assume tabPts[0] exists because the constructor adds points.
    Vector2D min=tabPts[0], max=tabPts[0];
    // Iterate through all points to find the min/max X and Y values.
    for (auto &pt : tabPts) {
        if (pt.x < min.x) min.x = pt.x;
        if (pt.y < min.y) min.y = pt.y;
        if (pt.x > max.x) max.x = pt.x;
        if (pt.y > max.y) max.y = pt.y;
    }
    return QPair<Vector2D,Vector2D>(min, max);
}

// Graham Scan Algorithm Implementation

/**
 * @brief A static comparator function for std::sort (Exercise 3 Q5).
 * @return True if point 'a' comes before point 'b' in a counter-clockwise
 * polar angle sort around the static 'Polygon::anchor' point.
 */
bool Polygon::polarComparison(const Vector2D& a, const Vector2D& b) {
    // Calculate vectors from the anchor to points a and b
    float dx_a = a.x - Polygon::anchor.x;
    float dy_a = a.y - Polygon::anchor.y;
    float dx_b = b.x - Polygon::anchor.x;
    float dy_b = b.y - Polygon::anchor.y;

    // Use atan2 to get the angle.
    float angle_a = std::atan2(dy_a, dx_a);
    float angle_b = std::atan2(dy_b, dx_b);

    // Tie-breaker: If two points have the same angle the one closer to the anchor comes first.
    if (std::abs(angle_a - angle_b) < 0.0001f) {
        return (dx_a*dx_a + dy_a*dy_a) < (dx_b*dx_b + dy_b*dy_b);
    }

    return angle_a < angle_b;
}

/**
 * @brief Polygon Constructor for Graham Scan (Exercise 3 Q6).
 * Creates a new Polygon by computing the Convex Hull of a given point cloud.
 * @param points A QVector of points (passed by reference to avoid copying).
 */
Polygon::Polygon(QVector<Vector2D> &points) : currentColor(Qt::yellow), originalColor(Qt::yellow) {
    int N = points.size();
    if (N < 3) return; // A polygon needs at least 3 points.

    // Find the Anchor Point
    // Find the point with the lowest Y-coordinate.
    // If tied, use the leftmost X-coordinate.
    int minIndex = 0;
    for (int i = 1; i < N; ++i) {
        if (points[i].y < points[minIndex].y ||
            (points[i].y == points[minIndex].y && points[i].x < points[minIndex].x)) {
            minIndex = i;
        }
    }

    // Sort the Points
    // Swap the anchor point to the front of the list
    std::swap(points[0], points[minIndex]);
    // Set the static 'anchor' for the comparator function
    Polygon::anchor = points[0];
    // Sort all points except the anchor by polar angle
    std::sort(points.begin() + 1, points.end(), Polygon::polarComparison);

    // Build the Hull
    // The hull (our stack) is built using the 'tabPts' vector.
    // Initialize the hull with the first three sorted points.
    this->addVertex(points[0].x, points[0].y); // Anchor
    this->addVertex(points[1].x, points[1].y);
    this->addVertex(points[2].x, points[2].y);

    // Iterate through the rest of the sorted points
    for (int i = 3; i < N; ++i) {
        // This 'while' loop is the "backtracking" part of the stack.
        // It runs as long as the last turn was NOT a "left turn".
        while (tabPts.size() >= 3) {
            // Get the last two points added to the hull
            int top = nbVertices() - 1;
            int next_to_top = top - 1;
            Vector2D p_top = tabPts[top];
            Vector2D p_prev = tabPts[next_to_top];

            // Check the turn from (A -> B) to (B -> New Point C)
            Vector2D AB = p_top - p_prev;
            Vector2D AC = points[i] - p_prev; // Note: Uses p_prev as origin

            // Calculate Z-component of the 2D cross product (AB x AC)
            float cross_product = AB.x * AC.y - AB.y * AC.x;

            if (cross_product > 0) {
                // cross_product > 0 means a LEFT turn (convex).
                // Stop popping and proceed to add the new point.
                break;
            } else {
                // cross_product <= 0 means a RIGHT turn or Collinear (concave "dent").
                // The last point (p_top) is inside the new hull.
                // "Pop" p_top from our stack (tabPts).
                tabPts.pop_back(); // Removes the duplicate [P0]
                tabPts.pop_back(); // Removes the actual vertex [P_top]
                tabPts.push_back(tabPts[0]); // Re-adds the duplicate [P0]
            }
        }
        // Add the new point (points[i]) to the hull
        this->addVertex(points[i].x, points[i].y);
    }

    this->setColor(Qt::yellow);
}
