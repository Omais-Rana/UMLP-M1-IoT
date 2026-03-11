import os
from dotenv import load_dotenv
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_community.document_loaders import PyPDFLoader
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import FAISS
from langchain_huggingface import HuggingFaceEmbeddings

load_dotenv()

# 1. Load the PDF
loader = PyPDFLoader("evolity.pdf")
raw_pages = loader.load()

# 2. STEP 9: The Splitter 
# We use a balanced chunk size to keep bus stop names with their times
text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000, 
    chunk_overlap=100,
    separators=["\n\n", "\n", " "]
)

# Crucial step: Index the CHUNKS, not the raw_pages
split_docs = text_splitter.split_documents(raw_pages)

# 3. STEP 7: Indexing with FAISS
embeddings = HuggingFaceEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")
vector_db = FAISS.from_documents(split_docs, embeddings) # Using split_docs here!

# 4. Search & Full RAG
query = "What is the frequency of the bus on Saturdays?"
# We fetch k=3 chunks to ensure we get enough context from the table
search_results = vector_db.similarity_search(query, k=3)

# Combine chunks into one context
context = "\n---\n".join([doc.page_content for doc in search_results])

llm = ChatMistralAI(model="mistral-large-latest")
prompt = f"""You are an évolitY expert. Use the following schedule data to answer.
If the information is not present, say you don't know.

CONTEXT:
{context}

QUESTION: {query}"""

response = llm.invoke(prompt)

print(f"\n--- AI Answer (Saturday Frequency) ---")
print(response.content) # Should mention 'Every 30 minutes'