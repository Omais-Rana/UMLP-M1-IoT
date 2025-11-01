#ifndef MYDATA_H
#define MYDATA_H

#include <QString>
#include <QColor>

class MyData {
    int number;
    QString name;
    QColor color;
public:
    MyData(int n, QString nm, QColor c) : number(n), name(nm), color(c) {}

    QString print() const {
        return QString("Number: %1, Color: %2").arg(number).arg(name);
    }

    QColor getColor() const { return color; }
};

#endif
