#ifndef WORD_H
#define WORD_H
#include <QString>

class Word {
public:
    Word(const QString &src):str(src) {};

    bool operator<(const QString &cmp);
    bool operator==(const QString &cmp);
    QString get() const;
private :
    QString str;
};

#endif // WORD_H
