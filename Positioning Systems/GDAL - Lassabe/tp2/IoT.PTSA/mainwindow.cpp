#include "mainwindow.h"
#include "mapdisplay.h"

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent), _display{new MapDisplay{this}} {
    setCentralWidget(_display);
    setWindowTitle("My GIS Lab 2");
    resize(1000, 750);
}

MainWindow::~MainWindow() {}
