/**
 * @author Benoît Piranda
 * @date 7/11/2024
 * @todo Make more tests of the triangulation algorithm with complex shapes.
 **/

#ifndef POLYGON_H
#define POLYGON_H

#include "vector2d.h"
#include <QPainter>
#include <QDebug>
#include <cmath> // Required for std::abs

/**
 * @brief The Triangle class store 3 pointers to existing Vector2D vertices.
 * It is used by the MyPolygon class to create a set of internal triangles.
 */
class Triangle {
    Vector2D *tabPts[3]; ///< array of 3 pointers to point
public:
    /**
     * @brief Constuctor of triangle with pointers to vertices.
     * @warning The order of vertices must be CCW.
     * @param p_p0 Pointer to the first vertex (P0).
     * @param p_p1 Pointer to the second vertex (P1).
     * @param p_p2 Pointer to the third vertex (P2).
     */
    Triangle(Vector2D *p_p0,Vector2D *p_p1,Vector2D *p_p2) {
        tabPts[0]=p_p0;
        tabPts[1]=p_p1;
        tabPts[2]=p_p2;
    }

    /**
     * @brief Gets a read-only pointer to a vertex by its index.
     * @param i The index of the vertex (0, 1, or 2).
     * @return A const pointer to the Vector2D vertex.
     */
    const Vector2D* operator[](int i) const { return tabPts[i]; }

    /**
     * @brief Calculates the area of the triangle.
     * @return The area in square pixels, using the cross-product formula.
     */
    float area() const {
        const Vector2D &p0 = *tabPts[0];
        const Vector2D &p1 = *tabPts[1];
        const Vector2D &p2 = *tabPts[2];
        return 0.5f * std::abs(p0.x * (p1.y - p2.y) + p1.x * (p2.y - p0.y) + p2.x * (p0.y - p1.y));
    }
};

/**
 * @brief MyPolygon class allow to create, draw and manipulate
 * polygons, especially check if a point is inside and compute
 * the surface of the polygon.
*/
class Polygon {
private:
    ///< @warning Store N+1 vertices in tabPts array, first is duplicated in last.
    QVector<Vector2D> tabPts;
    QVector<Triangle> triangles;
    QColor currentColor; /// current drawing color of the polygon
    QColor originalColor; /// original color of the polygon

    /**
     * @brief Helper for triangulation: checks if point C is to the left of the directed edge A->B.
     * @param A The starting point of the edge.
     * @param B The ending point of the edge.
     * @param C The point to test.
     * @return True if C is to the left or on the edge A->B, false otherwise.
     */
    bool isLeftOf(const Vector2D &A, const Vector2D &B, const Vector2D &C) const {
        Vector2D AB = B - A;
        Vector2D AC = C - A;
        // Z-component of 2D cross product (AB x AC)
        return (AB.x * AC.y - AB.y * AC.x) >= 0;
    }

public:
    /**
     * @brief Default constructor for a Polygon.
     */
    Polygon():currentColor(Qt::green) {}

    /**
     * @brief Gets the number of unique vertices in the polygon.
     * @return The total count of vertices.
     */
    int nbVertices() const { return tabPts.size()==0?0:tabPts.size()-1; }

    /**
     * @brief Adds a new vertex to the end of the polygon's list.
     * @param x The x-coordinate of the new vertex.
     * @param y The y-coordinate of the new vertex.
     */
    void addVertex(float x,float y);

    /**
     * @brief Calculates the axis-aligned bounding box (AABB) of the polygon.
     * @return A QPair containing the (min, max) coordinates (bottom-left, top-right).
     */
    QPair<Vector2D,Vector2D> getBoundingBox() const;

    /**
     * @brief Sets the polygon's display color.
     * @param c The new QColor to use.
     * @note This sets both the `currentColor` and `originalColor`.
     */
    void setColor(const QColor c) {
        currentColor=c;
        originalColor = c;
    }

    /**
     * @brief Updates the polygon's color based on mouse position (picking).
     * @param pt The mouse pointer's coordinates to test.
     * @note Sets color to red if inside, or reverts to `originalColor` if outside.
     * @warning This method relies on a correct triangulation (e.g., from Ear Clipping).
     */
    void changeColor(const Vector2D &pt) {
        bool isInside = false;
        // Iterate through all triangles of the polygon
        for (const auto& tri : triangles) {
            // --- Point-in-triangle logic ---
            const Vector2D &v0 = *tri[0];
            const Vector2D &v1 = *tri[1];
            const Vector2D &v2 = *tri[2];

            // Check if pt is to the left of all three CCW edges
            bool leftOf_v0v1 = ((v1.x - v0.x) * (pt.y - v0.y) - (v1.y - v0.y) * (pt.x - v0.x)) >= 0;
            bool leftOf_v1v2 = ((v2.x - v1.x) * (pt.y - v1.y) - (v2.y - v1.y) * (pt.x - v1.x)) >= 0;
            bool leftOf_v2v0 = ((v0.x - v2.x) * (pt.y - v2.y) - (v0.y - v2.y) * (pt.x - v2.x)) >= 0;

            if (leftOf_v0v1 && leftOf_v1v2 && leftOf_v2v0) {
                isInside = true;
                break; // Found it's inside, no need to check other triangles
            }
        }

        // Set the color based on the result
        if (isInside) {
            currentColor = Qt::red; // Highlight color
        } else {
            currentColor = originalColor; // Revert to the original color
        }
    }

    /**
     * @brief Gets a vertex by its index.
     * @param i The index of the vertex (0 to N).
     * @return A copy of the Vector2D vertex.
     * @note Index N will return the same vertex as index 0.
     */
    Vector2D operator[](int i) const { return tabPts[i]; }

    /**
     * @brief Draws the polygon using a QPainter.
     * @param painter The QPainter context to draw on.
     * @param showTriangles If true, draws the internal triangulation lines.
     */
    void draw(QPainter &painter,bool showTriangles) const;

    /**
     * @brief Triangulates the polygon using the Ear Clipping algorithm.
     * @note This works for both convex and concave simple polygons.
     * @warning Will fail on complex self-intersecting polygons.
     */
    void triangulate() {
        triangles.clear(); // Clear any previous triangles
        int n_total = nbVertices();
        if (n_total < 3) {
            return;
        }

        // Create a temporary list of pointers to the original vertices.
        QVector<Vector2D*> vertexPtrs;
        for (int i = 0; i < n_total; ++i) {
            vertexPtrs.push_back(&tabPts[i]);
        }

        int n = vertexPtrs.size();
        int safeguard = n * 2; // Safety counter

        // Loop until only 3 vertices remain.
        while (n > 3 && safeguard > 0) {
            safeguard--;
            bool earFound = false;

            // Iterate through vertices to find an ear
            for (int i = 0; i < n; ++i) {
                int idx0 = i;
                int idx1 = (i + 1) % n;
                int idx2 = (i + 2) % n;

                Vector2D *p0 = vertexPtrs[idx0];
                Vector2D *p1 = vertexPtrs[idx1];
                Vector2D *p2 = vertexPtrs[idx2];

                // Check if the turn is convex
                if (isLeftOf(*p0, *p1, *p2)) {
                    // Check if any other vertex is inside this triangle
                    bool pointInside = false;
                    for (int j = 0; j < n; ++j) {
                        if (j == idx0 || j == idx1 || j == idx2) continue; // Skip self

                        Vector2D *pt = vertexPtrs[j];
                        if (isLeftOf(*p0, *p1, *pt) &&
                            isLeftOf(*p1, *p2, *pt) &&
                            isLeftOf(*p2, *p0, *pt)) {
                            pointInside = true;
                            break;
                        }
                    }

                    // Found an Ear
                    if (!pointInside) {
                        triangles.push_back(Triangle(p0, p1, p2)); // Store triangle
                        vertexPtrs.remove(idx1); // Remove ear tip
                        n = vertexPtrs.size();
                        earFound = true;
                        break; // Restart loop
                    }
                } // end if(isLeftOf)
                } // end for(i < n)

            if (!earFound && safeguard <= 0) {
                qDebug() << "Ear clipping failed. Polygon may be complex.";
                return;
            }
        } // end while(n > 3)

        // Add the last remaining triangle
        if (vertexPtrs.size() == 3) {
            triangles.push_back(Triangle(vertexPtrs[0], vertexPtrs[1], vertexPtrs[2]));
        }
    };

    /**
     * @brief Checks if point p is to the left of the directed edge from vertex i to i+1.
     * @param p The point to check.
     * @param i The starting index of the edge in the polygon's vertex list.
     * @return True if the point is to the left or on the edge, false otherwise.
     */
    bool isOnTheLeft(const Vector2D &p, int i) const {
        // Edge vector from vertex i to i+1
        Vector2D AB = tabPts[i+1] - tabPts[i];
        // Vector from vertex i to the point p
        Vector2D AP = p - tabPts[i];

        // Calculate the Z component of the 2D cross product (AB x AP)
        return (AB.x * AP.y - AB.y * AP.x) >= 0;
    }

    /**
     * @brief Checks if the polygon is convex.
     * @return True if the polygon is convex, false otherwise.
     * @note Assumes vertices are in CCW order.
     */
    bool isConvex() const {
        int N = nbVertices();
        if (N < 3) return false;
        int i = 0;
        // Loop while all turns are to the left (for CCW polygons)
        while (i < N && isOnTheLeft(tabPts[(i + 2) % N], i)) {
            i++;
        }
        // If the loop completed, i will be equal to N.
        return i >= N;
    }

    /**
     * @brief Checks if a point is inside the polygon using the convex algorithm.
     * @param p The point to check.
     * @return True if the point is inside, false otherwise.
     * @warning This method is only accurate for convex polygons.
     */
    bool isInside(const Vector2D &p) const {
        int N = nbVertices();
        if (N < 3) return false;

        // Loop through all edges of the polygon
        for (int i = 0; i < N; ++i) {
            if (!isOnTheLeft(p, i)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @brief Calculates the total area of the polygon.
     * @return The surface area in u², where 1u² = 100 pixels².
     * @note Works by summing the areas of the internal triangles.
     * @warning Relies on a correct triangulation.
     */
    float area() const {
        float totalAreaInPixels = 0.0f;

        // Sum the area of each triangle (in square pixels)
        for (const auto& tri : triangles) {
            totalAreaInPixels += tri.area();
        }

        // Convert from square pixels to u² (since 1 u² = 100 pixels²)
        const float pixelsPerUnitSquared = 100.0f;
        return totalAreaInPixels / pixelsPerUnitSquared;
    }
};

#endif // POLYGON_H
