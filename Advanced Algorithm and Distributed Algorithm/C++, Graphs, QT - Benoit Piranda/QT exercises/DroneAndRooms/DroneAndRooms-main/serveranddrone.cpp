#include "serveranddrone.h"
#include <QDebug>

Link::Link(Server *n1,Server *n2,const QPair<Vector2D,Vector2D> &edge):
    node1(n1),node2(n2) {
    // computation of the length of the link
    Vector2D center=0.5*(edge.first+edge.second);
    distance = (center-Vector2D(n1->position.x(),n1->position.y())).length();
    distance += (center-Vector2D(n2->position.x(),n2->position.y())).length();
    edgeCenter=QPointF(center.x,center.y);
}

void Link::draw(QPainter &painter) {
    painter.drawLine(node1->position,edgeCenter);
    painter.drawLine(node2->position,edgeCenter);
}

/* Motions of the drone to reach the "destination" position*/
void Drone::move(qreal dt) {
    Vector2D dir=destination-position;
    double d=dir.length();

    if (d<slowDownDistance) {
        speed=(d*speedLocal/slowDownDistance)*dir;
    } else {
        speed+=(accelation*dt/d)*dir;
        if (speed.length()>speedMax) {
            speed.normalize();
            speed*=speedMax;
        }
    }
    // new position and orientation of the drone
    position+=(dt*speed);
    Vector2D Vn = (1.0/speed.length())*speed;
    if (Vn.y==0) {
        if (Vn.x>0) {
            azimut = -90;
        } else {
            azimut = 90.0;
        }
    } else if (Vn.y>0) {
        azimut = 180.0-180.0*atan(Vn.x/Vn.y)/M_PI;
    } else {
        azimut = -180.0*atan(Vn.x/Vn.y)/M_PI;
    }

    /* Write here your code that manages drone trajectories */
    if (d < slowDownDistance) {
        speed = (d * speedLocal / slowDownDistance) * dir;
    } else {
        speed += (accelation * dt / d) * dir;
        if (speed.length() > speedMax) {
            speed.normalize();
            speed *= speedMax;
        }
    }
    // Update position and azimut
    position += (dt * speed);
    if (Vn.y == 0) {
        if (Vn.x > 0) azimut = -90; else azimut = 90.0;
    } else if (Vn.y > 0) {
        azimut = 180.0 - 180.0 * atan(Vn.x / Vn.y) / M_PI;
    } else {
        azimut = -180.0 * atan(Vn.x / Vn.y) / M_PI;
    }

    // --- EXERCISE 3: NAVIGATION LOGIC ---

    // Check if we reached the current destination (within minDistance)
    if (d < minDistance) {
        if (!connectedTo) return; // Safety check

        // Convert QPointF to Vector2D for comparison
        Vector2D serverPos(connectedTo->position.x(), connectedTo->position.y());

        // CASE 1: We are at a SERVER
        // (We check if our current destination matches the server's position)
        if ((destination - serverPos).length() < 1.0) {

            // Check if we reached the FINAL target
            if (target && connectedTo->id == target->id) {
                speed = Vector2D(0,0); // Stop moving
                return;
            }

            // If not final target, find path to the target
            if (target && target->id < connectedTo->bestDistance.size()) {
                // Rule 3: Get the link that leads to the target
                Link* nextLink = connectedTo->bestDistance[target->id].first;

                if (nextLink) {
                    // Set destination to the "door" (middle of the edge)
                    destination = nextLink->getEdgeCenter();
                }
            }
        }
        // CASE 2: We are at a DOOR (Edge Center)
        else {
            // Rule 4: Switch to the server in the opposite area
            // We iterate links to find which one we are currently crossing
            for (Link* link : connectedTo->links) {
                Vector2D doorPos = link->getEdgeCenter();

                // Check if we are near this door
                if ((position - doorPos).length() < minDistance + 2.0) {
                    // Find the "other" server attached to this link
                    Server* neighbor = (link->getNode1()->id == connectedTo->id) ? link->getNode2() : link->getNode1();

                    // Update the drone's current logical location
                    connectedTo = neighbor;

                    // Set destination to the center of the new server
                    destination = Vector2D(neighbor->position.x(), neighbor->position.y());
                    break;
                }
            }
        }
    }
}

Server* Drone::overflownArea(QList<Server>& list) {
    auto it=list.begin();
    while (it!=list.end() && !it->area.contains(position)) {
        it++;
    }
    connectedTo= it!=list.end()?&(*it):nullptr;
    return connectedTo;
}
