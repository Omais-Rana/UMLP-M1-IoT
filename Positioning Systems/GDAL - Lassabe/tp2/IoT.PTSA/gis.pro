QT       += core gui

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

CONFIG += c++17

SOURCES += \
    main.cpp \
    mainwindow.cpp \
    mapdisplay.cpp

HEADERS += \
    mainwindow.h \
    mapdisplay.h

# GDAL Configuration for Debian
INCLUDEPATH += /usr/include/gdal
LIBS += -lgdal

# Default rules for deployment 
qnx: target.path = /tmp/$${TARGET}/bin
else: unix:!android: target.path = /opt/$${TARGET}/bin
!isEmpty(target.path): INSTALLS += target
