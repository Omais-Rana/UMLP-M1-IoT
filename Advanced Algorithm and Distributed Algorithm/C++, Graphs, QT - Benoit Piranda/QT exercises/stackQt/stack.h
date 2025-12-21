#ifndef STACK_H
#define STACK_H

template <class T>
class Element {
public:
    T* data;
    Element<T>* next;

    Element(T* d, Element<T>* n) : data(d), next(n) {}
};

template <class T>
class Stack {
    Element<T>* top;
public:
    Stack() : top(nullptr) {}

    void push(T* data) {
        top = new Element<T>(data, top);
    }

    T* pop() {
        if (!top) return nullptr;
        Element<T>* temp = top;
        T* result = temp->data;
        top = top->next;
        delete temp;
        return result;
    }

    bool isEmpty() const {
        return top == nullptr;
    }
};

#endif // STACK_H
