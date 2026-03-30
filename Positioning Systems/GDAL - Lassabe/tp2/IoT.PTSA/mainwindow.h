#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

class MapDisplay;

class MainWindow : public QMainWindow {
    Q_OBJECT

    MapDisplay *_display;

public:
    MainWindow(QWidget *parent = nullptr);
    ~MainWindow();
};
#endif // MAINWINDOW_H
