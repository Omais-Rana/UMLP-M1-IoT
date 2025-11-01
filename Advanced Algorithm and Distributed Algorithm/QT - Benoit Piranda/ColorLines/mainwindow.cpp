#include "mainwindow.h"
#include "./ui_mainwindow.h"
#include <QPainter>

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
    , ui(new Ui::MainWindow)
{
    ui->setupUi(this);

    // Initialize Line 1 (all black)
    for (int i = 0; i < nbColors; ++i) {
        line1.append(Qt::black);
    }

    // Initialize Line 2 (yellow, red, rest black)
    line2.append(Qt::yellow);
    line2.append(Qt::red);
    for (int i = 2; i < nbColors; ++i) {
        line2.append(Qt::black);
    }

    // Initialize Line 3 (yellow, blue, red, rest black)
    line3.append(Qt::yellow);
    line3.insert(1, Qt::blue);
    line3.insert(2, Qt::red);
    while (line3.size() < nbColors) {
        line3.append(Qt::black);
    }
}

MainWindow::~MainWindow()
{
    delete ui;
}

void MainWindow::paintEvent(QPaintEvent *event)
{
    Q_UNUSED(event);
    QPainter painter(this);

    int blockSize = 40;   // size of each square
    int margin = 20;
    int topOffset = 50;

    // Draw Line 1
    for (int i = 0; i < line1.size(); ++i) {
        painter.fillRect(margin + i*blockSize, topOffset, blockSize, blockSize, line1[i]);
        painter.drawRect(margin + i*blockSize, topOffset, blockSize, blockSize);
    }

    // Draw Line 2
    for (int i = 0; i < line2.size(); ++i) {
        painter.fillRect(margin + i*blockSize, topOffset + 60, blockSize, blockSize, line2[i]);
        painter.drawRect(margin + i*blockSize, topOffset + 60, blockSize, blockSize);
    }

    // Draw Line 3
    for (int i = 0; i < line3.size(); ++i) {
        painter.fillRect(margin + i*blockSize, topOffset + 120, blockSize, blockSize, line3[i]);
        painter.drawRect(margin + i*blockSize, topOffset + 120, blockSize, blockSize);
    }
}
