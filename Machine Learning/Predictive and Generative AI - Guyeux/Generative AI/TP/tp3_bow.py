from sklearn.feature_extraction.text import CountVectorizer

corpus = [
    "The bus stops at Acropole.",
    "A bus is at the station.",
    "Where is the Acropole station?"
]

vectorizer = CountVectorizer()
X = vectorizer.fit_transform(corpus)

print("Vocabulary:", vectorizer.get_feature_names_out())
print("BoW Matrix:\n", X.toarray())