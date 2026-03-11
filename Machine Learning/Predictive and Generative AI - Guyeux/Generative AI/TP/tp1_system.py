from langchain_core.prompts import ChatPromptTemplate
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.output_parsers import StrOutputParser
from dotenv import load_dotenv

load_dotenv()

# Exercise 5.1: Define a system persona
prompt = ChatPromptTemplate.from_messages([
    ("system", "You are a witty and helpful transit assistant for évolitY in Montbéliard. "
               "Provide concise answers and always maintain a professional yet friendly tone."),
    ("user", "{input}")
])

llm = ChatMistralAI(model="mistral-large-latest")
chain = prompt | llm

# Exercise 5.2: Run a request and inspect metadata
result = chain.invoke({"input": "What is the best way to get to the Gare from Marie and Louis Pasteur University, Montbeliard?"})

print("--- Assistant Response ---")
print(result.content)

print("\n--- Token Usage Metadata ---")
print(result.usage_metadata)

final_chain = prompt | llm | StrOutputParser()

result_text = final_chain.invoke({"input": "Explain the évolitY network in 3 sentences."})

# Save to a Markdown file
with open("evolitY_guide.md", "w", encoding="utf-8") as f:
    f.write(f"# évolitY Assistant Response\n\n{result_text}")

print("Response saved to evolitY_guide.md")