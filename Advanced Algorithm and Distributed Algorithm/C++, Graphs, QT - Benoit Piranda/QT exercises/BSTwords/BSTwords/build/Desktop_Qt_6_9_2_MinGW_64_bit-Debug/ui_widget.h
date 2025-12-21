/********************************************************************************
** Form generated from reading UI file 'widget.ui'
**
** Created by: Qt User Interface Compiler version 6.9.2
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_WIDGET_H
#define UI_WIDGET_H

#include <QtCore/QVariant>
#include <QtWidgets/QApplication>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QLabel>
#include <QtWidgets/QLineEdit>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QSpacerItem>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QWidget>

QT_BEGIN_NAMESPACE

class Ui_Widget
{
public:
    QVBoxLayout *verticalLayout;
    QHBoxLayout *horizontalLayout;
    QLineEdit *entry;
    QSpacerItem *horizontalSpacer;
    QPushButton *pb_insert;
    QPushButton *pb_parent;
    QPushButton *pb_search;
    QHBoxLayout *horizontalLayout_2;
    QLabel *label;
    QLineEdit *txt_steps;
    QSpacerItem *horizontalSpacer_2;
    QLabel *label_2;
    QLineEdit *txt_answer;

    void setupUi(QWidget *Widget)
    {
        if (Widget->objectName().isEmpty())
            Widget->setObjectName("Widget");
        Widget->resize(800, 600);
        verticalLayout = new QVBoxLayout(Widget);
        verticalLayout->setObjectName("verticalLayout");
        horizontalLayout = new QHBoxLayout();
        horizontalLayout->setObjectName("horizontalLayout");
        entry = new QLineEdit(Widget);
        entry->setObjectName("entry");
        QFont font;
        font.setFamilies({QString::fromUtf8("Courier New")});
        font.setPointSize(20);
        font.setBold(true);
        entry->setFont(font);

        horizontalLayout->addWidget(entry);

        horizontalSpacer = new QSpacerItem(40, 20, QSizePolicy::Policy::Expanding, QSizePolicy::Policy::Minimum);

        horizontalLayout->addItem(horizontalSpacer);

        pb_insert = new QPushButton(Widget);
        pb_insert->setObjectName("pb_insert");
        QFont font1;
        font1.setPointSize(14);
        pb_insert->setFont(font1);

        horizontalLayout->addWidget(pb_insert);

        pb_parent = new QPushButton(Widget);
        pb_parent->setObjectName("pb_parent");
        pb_parent->setFont(font1);

        horizontalLayout->addWidget(pb_parent);

        pb_search = new QPushButton(Widget);
        pb_search->setObjectName("pb_search");
        pb_search->setFont(font1);

        horizontalLayout->addWidget(pb_search);


        verticalLayout->addLayout(horizontalLayout);

        horizontalLayout_2 = new QHBoxLayout();
        horizontalLayout_2->setObjectName("horizontalLayout_2");
        label = new QLabel(Widget);
        label->setObjectName("label");
        label->setFont(font1);

        horizontalLayout_2->addWidget(label);

        txt_steps = new QLineEdit(Widget);
        txt_steps->setObjectName("txt_steps");
        txt_steps->setMaximumSize(QSize(80, 16777215));
        txt_steps->setSizeIncrement(QSize(50, 0));
        QFont font2;
        font2.setPointSize(14);
        font2.setBold(true);
        txt_steps->setFont(font2);
        txt_steps->setReadOnly(true);

        horizontalLayout_2->addWidget(txt_steps);

        horizontalSpacer_2 = new QSpacerItem(40, 20, QSizePolicy::Policy::Expanding, QSizePolicy::Policy::Minimum);

        horizontalLayout_2->addItem(horizontalSpacer_2);

        label_2 = new QLabel(Widget);
        label_2->setObjectName("label_2");
        label_2->setFont(font1);

        horizontalLayout_2->addWidget(label_2);

        txt_answer = new QLineEdit(Widget);
        txt_answer->setObjectName("txt_answer");
        txt_answer->setSizeIncrement(QSize(90, 0));
        txt_answer->setFont(font2);
        txt_answer->setReadOnly(true);

        horizontalLayout_2->addWidget(txt_answer);


        verticalLayout->addLayout(horizontalLayout_2);


        retranslateUi(Widget);

        QMetaObject::connectSlotsByName(Widget);
    } // setupUi

    void retranslateUi(QWidget *Widget)
    {
        Widget->setWindowTitle(QCoreApplication::translate("Widget", "Widget", nullptr));
        entry->setText(QCoreApplication::translate("Widget", "APPLE", nullptr));
        pb_insert->setText(QCoreApplication::translate("Widget", "Insert Word", nullptr));
        pb_parent->setText(QCoreApplication::translate("Widget", "Search Parent", nullptr));
        pb_search->setText(QCoreApplication::translate("Widget", "Search Word", nullptr));
        label->setText(QCoreApplication::translate("Widget", "Search steps: ", nullptr));
        label_2->setText(QCoreApplication::translate("Widget", "Found:", nullptr));
        txt_answer->setText(QCoreApplication::translate("Widget", "???", nullptr));
    } // retranslateUi

};

namespace Ui {
    class Widget: public Ui_Widget {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_WIDGET_H
