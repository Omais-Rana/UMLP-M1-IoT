#include "mainwindow.h"
#include "mapdisplay.h"

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent), _display{new MapDisplay{this}} {
    setCentralWidget(_display);
    setWindowTitle("Belfort - Ruby Themed");
    resize(1000, 750);
}

MainWindow::~MainWindow() {}
