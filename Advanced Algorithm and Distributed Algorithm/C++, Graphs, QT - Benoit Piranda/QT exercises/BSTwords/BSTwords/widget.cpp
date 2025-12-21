#include "widget.h"
#include "build/Desktop_Qt_6_9_2_MinGW_64_bit-Debug/ui_widget.h"
#include "ui_widget.h"
#include <QFile>
#include <QMessageBox>

Widget::Widget(QWidget *parent)
    : QWidget(parent)
    , ui(new Ui::Widget) {
    ui->setupUi(this);

    root = new BST();
    loadFile("../../words.txt");
}

Widget::~Widget() {
    delete ui;
    delete root;
}

bool Widget::loadFile(const QString &fileName) {
    QFile file(fileName);
    int i=0;
    if (file.open(QIODevice::ReadOnly)) {
        QTextStream in(&file);
        while (!in.atEnd()) {
            QString word=in.readLine().toUpper();
            if (word!="") {
                root= root->insert(word.toUpper());
                i++;
            }
        }
    } else {
        QMessageBox messageBox;
        messageBox.critical(0,"Error","File "+fileName+" not reachable!");
        return false;
    }
    qDebug() << "Inserted words:" << i;
    if (root) {
        root->printInOrder();
    } else {
        qDebug() << "Tree is empty!";
    }
    file.close();
    return true;
}

void Widget::on_pb_search_clicked() {
    QString word = ui->entry->text().toUpper();
    int n=0;
    if (root->search(ui->entry->text().toUpper(),n)) {
        int depth = root->depth(word);
        ui->txt_answer->setText("YES (Depth = " + QString::number(depth) + ")");
    } else {
        ui->txt_answer->setText("NO");
    }
    ui->txt_steps->setText(QString::number(n));
}

void Widget::on_pb_parent_clicked() {
    QString word = ui->entry->text().toUpper();
    if (word.isEmpty()) return;

    BST *parentNode = root->findParent(word);

    if (parentNode != nullptr) {
        ui->txt_answer->setText(parentNode->getWord()); //Gives name of the closet parent node after which the word should be inserted
    } else {
        ui->txt_answer->setText("Tree is empty");
    }
}

void Widget::on_pb_insert_clicked() {
    QString word = ui->entry->text().toUpper();
    if (!word.isEmpty()) {
        root = root->insert(word.toUpper());           // root may change after rotations
        ui->txt_answer->setText("Inserted");
    }

    if (root->insert(word)) {
        ui->txt_answer->setText("Inserted");
    } else {
        ui->txt_answer->setText("Duplicate");
    }
}


void Widget::on_entry_returnPressed() {
    on_pb_search_clicked();
}

