#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <canvas.h>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>
#include <QFile>
#include <QFileDialog>
#include <QMessageBox>
#include <trianglemesh.h>

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
    , ui(new Ui::MainWindow)
{
    ui->setupUi(this);
    // load initial simple case
    loadJson("../../json/simple.json");
    //loadJson("../../json/arcane.json");
}

MainWindow::~MainWindow()
{
    delete ui;
}

bool MainWindow::loadJson(const QString& title) {
    QFile file(title);
    if (!file.open(QIODevice::ReadOnly)) {
        qWarning() << "Impossible d'ouvrir le fichier:" << title;
        return false;
    }

    QByteArray data = file.readAll();
    file.close();

    QJsonParseError error;
    QJsonDocument doc = QJsonDocument::fromJson(data, &error);
    if (error.error != QJsonParseError::NoError) {
        qWarning() << "Erreur JSON:" << error.errorString();
        return false;
    }
    if (!doc.isObject()) {
        qWarning() << "Le document JSON n'est pas un objet.";
        return false;
    }

    QJsonObject root = doc.object();

    // --- Window ---
    if (root.contains("window") && root["window"].isObject()) {
        QJsonObject win = root["window"].toObject();

        auto origin = win.value("origine").toString().split(",");
        auto size   = win.value("size").toString().split(",");
        QPoint wOrigin={origin[0].toInt(),origin[1].toInt()};
        QSize wSize={size[0].toInt(),size[1].toInt()};
        qDebug() << "Window.origine =" << wOrigin;
        qDebug() << "Window.size    =" << wSize;
        ui->canvas->setWindow(wOrigin,wSize);
    }

    // --- Servers ---
    if (root.contains("servers") && root["servers"].isArray()) {
        int num=0;
        QJsonArray arr = root["servers"].toArray();
        for (const QJsonValue &v : arr) {
            if (!v.isObject()) continue;
            QJsonObject obj = v.toObject();
            Server s;
            s.name = obj.value("name").toString();
            QString pos = obj.value("position").toString();
            auto parts = pos.split(',');
            if (parts.size() == 2)
                s.position = QPoint(parts[0].toInt(), parts[1].toInt());
            s.color = QColor(obj.value("color").toString());
            s.id=num++;
            ui->canvas->servers.append(s);
            qDebug() << "Server:" << s.id << "," << s.name << s.position << s.color;
        }
    }

    // --- Drones ---
    if (root.contains("drones") && root["drones"].isArray()) {
        QJsonArray arr = root["drones"].toArray();

        for (const QJsonValue &v : arr) {
            if (!v.isObject()) continue;
            QJsonObject obj = v.toObject();
           Drone d;
            d.name = obj.value("name").toString();
            QString pos = obj.value("position").toString();
            auto parts = pos.split(',');
            if (parts.size() == 2)
                d.position = Vector2D(parts[0].toInt(), parts[1].toInt());
            QString name = obj.value("target").toString();
            // search name in server list
            d.target=nullptr;
            auto it=ui->canvas->servers.begin();
            while (it!=ui->canvas->servers.end() && it->name!=name) it++;
            if (it!=ui->canvas->servers.end()) {
                d.target=&(*it);
                qDebug() << "Drone:" << d.name << "(" << d.position.x << "," << d.position.y << ") →" << d.target->name;
            } else {
                qDebug() << "error in JsonFile: bad destination name: " << name;
            }
            ui->canvas->drones.append(d);
        }
    }

    createVoronoiMap();
    createServersLinks();
    fillDistanceArray();
    return true;
}

void MainWindow::createVoronoiMap() {
    TriangleMesh mesh(ui->canvas->servers);
    mesh.setBox(ui->canvas->getOrigin(),ui->canvas->getSize());

    auto triangles = mesh.getTriangles();
    auto m_servor = ui->canvas->servers.begin();
    QVector<const Triangle*> tabTri;
    while (m_servor!=ui->canvas->servers.end()) {
        // for all vertices of the mesh
        const Vector2D vert((*m_servor).position.x(),(*m_servor).position.y());
        auto mt_it = triangles->begin();
        tabTri.clear(); // tabTri: list of triangles containing m_vert
        while (mt_it!=triangles->end()) {
            if ((*mt_it).hasVertex(vert)) {
                tabTri.push_back(&(*mt_it));
            }
            mt_it++;
        }
        // find left border
        auto first = tabTri.begin();
        auto tt_it = tabTri.begin();
        bool found=false;
        while (tt_it!=tabTri.end() && !found) {
            auto comp_it = tabTri.begin();
            while (comp_it!=tabTri.end() && (*tt_it)->getNextVertex(vert)!=(*comp_it)->getPrevVertex(vert)) {
                comp_it++;
            }
            if (comp_it==tabTri.end()) {
                first=tt_it;
                found=true;
            }
            tt_it++;
        }
        // create polygon

        //poly->setColor((*m_servor)->color);
        tt_it=first;
        if (found && mesh.isInWindow((*tt_it)->getCenter().x,(*tt_it)->getCenter().y)) { // add a point for the left border
            Vector2D V = (*first)->nextEdgeNormal(vert);
            float k;
            if (V.x > 0) { // (circumCenter+k V).x=width
                k = (mesh.getWindowXmax() - (*first)->getCenter().x) / float(V.x);
            } else {
                k = (mesh.getWindowXmin()-(*first)->getCenter().x) / float(V.x);
            }
            if (V.y > 0) { // (circumCenter+k V).y=height
                k = fmin(k, (mesh.getWindowYmax() - (*first)->getCenter().y) / float(V.y));
            } else {
                k = fmin(k, (mesh.getWindowYmin()-(*first)->getCenter().y) / float(V.y));
            }
            m_servor->area.addVertex(Vector2D((*first)->getCenter() + k * V));
            Vector2D pt = (*first)->getCenter() + k * V;
        }
        auto comp_it = first;
        do {
            m_servor->area.addVertex((*tt_it)->getCenter());
            // search triangle on right of tt_it
            comp_it = tabTri.begin();
            while (comp_it!=tabTri.end() && (*tt_it)->getPrevVertex(vert)!=(*comp_it)->getNextVertex(vert)) {
                comp_it++;
            }
            if (comp_it!=tabTri.end()) tt_it = comp_it;
        } while (tt_it!=first && comp_it!=tabTri.end());
        if (found && mesh.isInWindow((*tt_it)->getCenter())) { // add a point for the right border
            Vector2D V = (*tt_it)->previousEdgeNormal(vert);
            float k;
            if (V.x > 0) { // (circumCenter+k V).x=width
                k = (mesh.getWindowXmax() - (*tt_it)->getCenter().x) / float(V.x);
            } else {
                k = (mesh.getWindowXmin()-(*tt_it)->getCenter().x) / float(V.x);
            }
            if (V.y > 0) { // (circumCenter+k V).y=height
                k = fmin(k, (mesh.getWindowYmax() - (*tt_it)->getCenter().y) / float(V.y));
            } else {
                k = fmin(k, (mesh.getWindowYmin()-(*tt_it)->getCenter().y) / float(V.y));
            }
            m_servor->area.addVertex(Vector2D((*tt_it)->getCenter() + k * V));
            Vector2D pt = (*tt_it)->getCenter() + k * V;
        }
        qDebug() << m_servor->name;
        m_servor->area.clip(mesh.getWindowXmin(),mesh.getWindowYmin(),mesh.getWindowXmax(),mesh.getWindowYmax());
        m_servor->area.triangulate();

        m_servor++;
    }
}

void MainWindow::createServersLinks() {
    auto &servers = ui->canvas->servers;
    auto &links = ui->canvas->links;

    for (int i = 0; i < servers.size(); ++i) {
        for (int j = i + 1; j < servers.size(); ++j) {
            Polygon &poly1 = servers[i].area;
            Polygon &poly2 = servers[j].area;

            bool connected = false;
            // Loop over edges of server i
            for (int k = 0; k < poly1.nbVertices(); ++k) {
                Vector2D p1 = poly1[k];
                Vector2D p2 = poly1[(k + 1) % poly1.nbVertices()];

                // Loop over edges of server j
                for (int m = 0; m < poly2.nbVertices(); ++m) {
                    Vector2D q1 = poly2[m];
                    Vector2D q2 = poly2[(m + 1) % poly2.nbVertices()];

                    // Check if edges are identical
                    if ((p1 == q1 && p2 == q2) || (p1 == q2 && p2 == q1)) {
                        // We found a neighbor! Create the link.
                        Link* newLink = new Link(&servers[i], &servers[j], {p1, p2});

                        links.append(newLink);
                        servers[i].links.append(newLink);
                        servers[j].links.append(newLink);

                        connected = true;
                        break;
                    }
                }
                if (connected) break;
            }
        }
    }
}

void MainWindow::fillDistanceArray() {
    // define a nServers x nServers array
    int nServers = ui->canvas->servers.size();
    auto &servers = ui->canvas->servers;

    distanceArray.resize(nServers);
    for (int i=0; i<nServers; i++) {
        distanceArray[i].resize(nServers);
    }

    // init Servers distanceArray
    for (auto &s : servers) {
        s.bestDistance.resize(nServers);
        for (int i=0; i<nServers; i++) {
            s.bestDistance[i] = {nullptr, 0};
        }
    }

    /* Write here the code to compute the distance array for all servers */
    // Dijkstra's algorithm for each server to find paths to all others
    for (int src = 0; src < nServers; ++src) {
        // Initialize temporary Dijkstra structures
        QVector<double> dist(nServers, 1e9); // Infinite distance
        QVector<int> parent(nServers, -1);   // To reconstruct the path
        QVector<bool> visited(nServers, false);

        dist[src] = 0.0;

        // Main Dijkstra Loop
        for (int i = 0; i < nServers; ++i) {
            // Find the unvisited node with the smallest distance
            int u = -1;
            double minDist = 1e9;
            for (int j = 0; j < nServers; ++j) {
                if (!visited[j] && dist[j] < minDist) {
                    minDist = dist[j];
                    u = j;
                }
            }

            if (u == -1 || minDist == 1e9) break; // Remaining nodes are unreachable
            visited[u] = true;

            // Relax neighbors of u
            for (Link* l : servers[u].links) {
                // Determine the neighbor ID
                Server* neighborNode = (l->getNode1()->id == servers[u].id) ? l->getNode2() : l->getNode1();
                int v = neighborNode->id;

                double weight = l->getDistance();
                if (dist[u] + weight < dist[v]) {
                    dist[v] = dist[u] + weight;
                    parent[v] = u;
                }
            }
        }

        // Fill the results for this source server
        for (int dest = 0; dest < nServers; ++dest) {
            // Fill the debug/display array
            distanceArray[src][dest] = dist[dest];

            // Fill the Server's bestDistance structure
            if (dest == src) {
                servers[src].bestDistance[dest] = {nullptr, 0.0};
            } else if (dist[dest] >= 1e9) {
                servers[src].bestDistance[dest] = {nullptr, -1.0}; // Unreachable
            } else {
                // Backtrack to find the first link to take from 'src'
                int curr = dest;
                while (parent[curr] != src && parent[curr] != -1) {
                    curr = parent[curr];
                }

                // 'curr' is now the immediate neighbor of 'src'. Find the connecting link.
                Link* bestLink = nullptr;
                for (Link* l : servers[src].links) {
                    Server* neighbor = (l->getNode1()->id == servers[src].id) ? l->getNode2() : l->getNode1();
                    if (neighbor->id == curr) {
                        bestLink = l;
                        break;
                    }
                }
                servers[src].bestDistance[dest] = {bestLink, dist[dest]};
            }
        }
    }

    qDebug() << "\n [Shortest Path Distances] ";

    // Create the Header Row
    QString header = QString("%1").arg("S\\D", -10);

    for(auto &s : servers) {
        // Truncate server name to 9 chars
        header += QString("%1").arg(s.name.left(9), -10);
    }
    qDebug().noquote() << header; // .noquote() prevents quotes around the string

    // Create the Data Rows
    for (int i = 0; i < nServers; ++i) {
        // Start row with the Source Server Name
        QString row = QString("%1").arg(servers[i].name.left(9), -10);

        for (int j = 0; j < nServers; ++j) {
            double d = distanceArray[i][j];

            if (d >= 1e9) {
                // Unreachable
                row += QString("%1").arg("inf", -10);
            } else {
                // Print number with 0 decimals, padded to 10 chars
                QString numStr = QString::number(d, 'f', 0);
                row += QString("%1").arg(numStr, -10);
            }
        }
        qDebug().noquote() << row;
    }
}

void MainWindow::update() {
    static int last=elapsedTimer.elapsed();
    int current=elapsedTimer.elapsed();
    int dt=current-last;
    // update positions of drones
    for (auto &drone:ui->canvas->drones) {
        drone.move(dt/1000.0);
    }
    ui->canvas->repaint();
}

void MainWindow::on_actionShow_graph_triggered(bool checked) {
    ui->canvas->showGraph=checked;
    ui->canvas->repaint();
}


void MainWindow::on_actionMove_drones_triggered() {
    //Associate each drone with the server of the area it is currently in
    for (auto &drone : ui->canvas->drones) {
        Server* startServer = drone.overflownArea(ui->canvas->servers);

        if (startServer) {
            //Set the first destination to that server's position
            drone.destination = Vector2D(startServer->position.x(), startServer->position.y());
        }
    }

    timer = new QTimer(this);
    timer->setInterval(100);
    connect(timer,SIGNAL(timeout()),this,SLOT(update()));
    timer->start();

    elapsedTimer.start();
}


void MainWindow::on_actionQuit_triggered() {
    QApplication::quit();
}


void MainWindow::on_actionCredits_triggered() {
    QMessageBox::information(this,"About DroneAndRooms program",
                             "My tiny project.\nCredit Benoît Piranda");
}


void MainWindow::on_actionLoad_triggered() {
    auto fileName = QFileDialog::getOpenFileName(this,tr("Open json description file"), "../../data", tr("JSON Files (*.json)"));
    if (!fileName.isEmpty()) {
        ui->canvas->clear();
        loadJson(fileName);
        ui->canvas->update();
    }
}

