from sentence_transformers import SentenceTransformer
import numpy as np

# We use a small, fast model for your laptop
model = SentenceTransformer("sentence-transformers/all-MiniLM-L6-v2", device="cpu")

sentences = [
    "How do I get to the Montbéliard train station?",
    "What is the route to the Gare of Montbéliard?"
]

embeddings = model.encode(sentences)

for sentence, embedding in zip(sentences, embeddings):
    # Printing just the first 3 numbers of the vector (out of 384!)
    print(f"Text: {sentence}")
    print(f"Vector (start): {embedding[:3]}...\n")

print(f"Total Vector Size: {len(embeddings[0])}")

def cosine_similarity(A, B):
    return np.dot(A, B) / (np.linalg.norm(A) * np.linalg.norm(B))

score = cosine_similarity(embeddings[0], embeddings[1])
print(f"Similarity Score: {score:.4f}") 
# A score near 1.0 means they are almost identical in meaning!