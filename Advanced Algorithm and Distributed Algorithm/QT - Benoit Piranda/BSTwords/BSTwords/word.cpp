#include "word.h"
#include <QDebug>

bool Word::operator<(const QString &cmp) {
    return (str<cmp);
}

bool Word::operator==(const QString &cmp) {
    return (str==cmp);
}

QString Word::get() const {
    return str;
}
