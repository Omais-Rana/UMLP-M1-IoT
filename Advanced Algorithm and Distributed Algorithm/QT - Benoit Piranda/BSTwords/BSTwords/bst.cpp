#include "bst.h"
#include <QDebug>
BST::BST():left(nullptr),right(nullptr),parent(nullptr),nodeWord(nullptr) {
}

BST::BST(const QString &word, BST* p, BST*l, BST*r):
    left(l),right(r),parent(p) {

    nodeWord = new Word(word);
};

BST::~BST() {
    delete nodeWord;
    delete left;
    delete right;
}

// height of subtree
int BST::height(BST* node) {
    if (node == nullptr) return 0;
    int lh = height(node->left);
    int rh = height(node->right);
    return 1 + std::max(lh, rh);
}

//balance factor (right - left)
int BST::balanceFactor() {
    int lh = height(left);
    int rh = height(right);
    return rh - lh;
}

// rotations
BST* BST::rotateLeft() {
    BST* newRoot = right;
    right = newRoot->left;
    if (newRoot->left) newRoot->left->parent = this;
    newRoot->left = this;

    newRoot->parent = parent;
    parent = newRoot;
    return newRoot;
}

BST* BST::rotateRight() {
    BST* newRoot = left;
    left = newRoot->right;
    if (newRoot->right) newRoot->right->parent = this;
    newRoot->right = this;

    newRoot->parent = parent;
    parent = newRoot;
    return newRoot;
}

BST* BST::insert(const QString &src) {
    if (nodeWord == nullptr) {
        nodeWord = new Word(src);
        return this;
    }

    return insertRec(this, src, nullptr);  // returns possibly new root after rotations
}

// recursive insert with balancing
BST* BST::insertRec(BST* node, const QString &src, BST* p) {
    if (node == nullptr) {
        return new BST(src, p, nullptr, nullptr);
    }

    if (src == node->getWord()) {
        qDebug() << "Duplicate skipped:" << src;
        return node; // no duplicates
    }

    if (src < node->getWord()) {
        node->left = insertRec(node->left, src, node);
    } else {
        node->right = insertRec(node->right, src, node);
    }

    // balance this node
    int balance = node->balanceFactor();

    // Right heavy
    if (balance > 1) {
        if (src < node->right->getWord()) {
            node->right = node->right->rotateRight(); // RL case
        }
        return node->rotateLeft();
    }

    // Left heavy
    if (balance < -1) {
        if (src > node->left->getWord()) {
            node->left = node->left->rotateLeft(); // LR case
        }
        return node->rotateRight();
    }

    return node; // no change
}

bool BST::search(const QString &src, int &n) {
    n++;
    if (!nodeWord) return false;

    if (*(nodeWord) == src) return true;   // found

    if (src < nodeWord->get()) {
        return left ? left->search(src, n) : false;
    } else {
        return right ? right->search(src, n) : false;
    }
}


BST* BST::findParent(const QString &src) {
    BST *current = this; //pointer that moves down the tree.
    BST *parentNode = nullptr; //keeps track of the last node visited

    while (current != nullptr) {
        parentNode = current; //stores the current node as parentNode
        if (src == current->nodeWord->get()) {
            return current; // exact match
        } else if (src < current->nodeWord->get()) {
            if (current->left == nullptr) return current;
            current = current->left;
        } else {
            if (current->right == nullptr) return current;
            current = current->right;
        }
    }
    return parentNode; //return the last node visited
}

int BST::depth(const QString &word, int level) {
    if (this->getWord() == word) {
        return level; // found at current depth
    } else if (word < this->getWord() && left != nullptr) {
        return left->depth(word, level + 1);
    } else if (word > this->getWord() && right != nullptr) {
        return right->depth(word, level + 1);
    }
    return 0;
}

void BST::printInOrder() {
    if (!this || !nodeWord) return;
    if (left) left->printInOrder();
    if (nodeWord) {
        qDebug() << nodeWord->get()
        << "(balance:" << balanceFactor() << ")";
    }
    if (right) right->printInOrder();
}

