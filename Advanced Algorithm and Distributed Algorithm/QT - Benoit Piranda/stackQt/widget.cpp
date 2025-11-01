#include "widget.h"
#include <QPainter>
#include <QMouseEvent>

Widget::Widget(QWidget *parent)
    : QWidget(parent)
{
    // Pre-fill stack with 5 elements
    for (int i = 0; i < 5; i++) {
        myStack.push(new MyData(i, colors[i].first, colors[i].second));
    }
    setMinimumSize(600, 400);
}

Widget::~Widget()
{
    // Free remaining stack elements
    while (!myStack.isEmpty()) {
        delete myStack.pop();
    }
    // Free popped elements
    qDeleteAll(popped);
}

void Widget::mousePressEvent(QMouseEvent *event)
{
    Q_UNUSED(event);

    // Pop topmost element and push to right side
    MyData* d = myStack.pop();
    if (d) {
        popped.append(d);
    }
    update(); // redraw
}

void Widget::paintEvent(QPaintEvent *event)
{
    Q_UNUSED(event);

    QPainter painter(this);

    int xStack = 50;
    int yStack = 50; // start from top
    int w = 120;
    int h = 30;
    int spacing = 10;

    // Draw stack (left side)
    QVector<MyData*> drawStack;
    Stack<MyData> temp;

    // Pop all elements temporarily and append (preserve top-to-bottom order)
    while (!myStack.isEmpty()) {
        MyData* d = myStack.pop();
        drawStack.append(d); // append keeps top at index 0
        temp.push(d);        // for restoring stack
    }

    // Draw elements from top to bottom
    for (MyData* d : drawStack) {
        painter.setBrush(d->getColor());
        painter.drawRect(xStack, yStack, w, h);
        painter.setPen(Qt::black);
        painter.drawText(xStack + 5, yStack + 20, d->print());
        yStack += h + spacing;
    }

    // Restore stack
    while (!temp.isEmpty()) {
        myStack.push(temp.pop());
    }

    // Draw popped elements (right side)
    int xPop = 300;
    int yPop = 50;
    for (MyData* d : std::as_const(popped)) {
        painter.setBrush(d->getColor());
        painter.drawRect(xPop, yPop, w, h);
        painter.setPen(Qt::black);
        painter.drawText(xPop + 5, yPop + 20, d->print());
        yPop += h + spacing;
    }
}

