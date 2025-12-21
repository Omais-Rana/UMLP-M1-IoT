#ifndef COLORS_H
#define COLORS_H

#include <QPair>
#include <QString>
#include <QColor>

const QPair<QString, QColor> colors[11] = {
    qMakePair(QString("red"), QColor(0xFF0000)),
    qMakePair(QString("orange"), QColor(0xFFA500)),
    qMakePair(QString("yellow"), QColor(0xFFFF00)),
    qMakePair(QString("green"), QColor(0x00FF00)),
    qMakePair(QString("blue"), QColor(0x0000FF)),
    qMakePair(QString("indigo"), QColor(0x4B0082)),
    qMakePair(QString("pink"), QColor(0xFFC0CB)),
    qMakePair(QString("brown"), QColor(0xA52A2A)),
    qMakePair(QString("white"), QColor(0xFFFFFF)),
    qMakePair(QString("magenta"), QColor(0xFF00FF)),
    qMakePair(QString("grey"), QColor(0x777777))
};

#endif // COLORS_H
