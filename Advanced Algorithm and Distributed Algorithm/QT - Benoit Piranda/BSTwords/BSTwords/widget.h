#ifndef WIDGET_H
#define WIDGET_H

#include <QWidget>
#include <bst.h>

QT_BEGIN_NAMESPACE
namespace Ui {
class Widget;
}
QT_END_NAMESPACE

class Widget : public QWidget {
    Q_OBJECT

public:
    Widget(QWidget *parent = nullptr);
    ~Widget();
    bool loadFile(const QString &fileName);
private slots:
    void on_pb_search_clicked();
    void on_pb_parent_clicked();
    void on_pb_insert_clicked();
    void on_entry_returnPressed();

private:
    Ui::Widget *ui;
    BST *root;
};
#endif // WIDGET_H
