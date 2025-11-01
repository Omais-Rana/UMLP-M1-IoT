#ifndef BST_H
#define BST_H
#include <QString>
#include <word.h>
class BST {
public:
    BST();
    ~BST();
    /**
     * @brief search if word src string in the tree.
     * @param src: word searched.
     * @return true if found.
     */
    bool search(const QString &src,int &n);
    BST* insert(const QString &src);
    BST *findParent(const QString &src);
    int depth(const QString &word, int level = 1);
    QString getWord() const { return (nodeWord==nullptr?"NULL":nodeWord->get());}
    int height(BST* node);
    int balanceFactor();
    BST* rotateLeft();
    BST* rotateRight();
    void printInOrder();
private:
    BST(const QString &word, BST* p, BST* l, BST* r);
    BST *left,*right,*parent;
    Word *nodeWord;
    BST* insertRec(BST* node, const QString &src, BST* parent);
};

#endif // BST_H
