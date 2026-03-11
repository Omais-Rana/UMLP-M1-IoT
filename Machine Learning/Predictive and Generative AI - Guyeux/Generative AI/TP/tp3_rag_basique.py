import os
from dotenv import load_dotenv
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

llm = ChatMistralAI(model="mistral-large-latest")

# This mimics the "Retrieval" part of RAG
query = "Where can I find the bus line 1 schedule?"
context = (
    "The schedules for line 1 and line 2 are available at the Acropole agency. "
    "Line 3 schedules are only available online at evolity.fr."
)

# Exercise 6.1 & 6.2: Add instructions for language and 'I don't know' logic
prompt = f"""You are an évolitY transit expert. 
Answer in French using ONLY the context provided below. 
If the answer is not in the context, say 'I do not know'.

Question: {query}
Context: {context}"""

response = llm.invoke(prompt)
print(f"Assistant: {response.content}")