import os
from dotenv import load_dotenv
from langchain_mistralai.chat_models import ChatMistralAI
from pydantic import BaseModel, Field
from langchain_core.prompts import ChatPromptTemplate

# 1. Load the environment FIRST
load_dotenv()

# 2. Now define the model (it will now see the MISTRAL_API_KEY)
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

class Etape(BaseModel):
    explication: str
    sortie: str

class MathReponse(BaseModel):
    etapes: list[Etape]
    reponse_finale: str
    # Exercise 6.1: Adding a field for verifications
    verifications: list[str] = Field(..., description="List of checks performed to ensure the result is correct")

prompt = ChatPromptTemplate.from_messages([
    ("system", "You are a pedagogical math teacher. Break down the problem and provide verifications."),
    ("human", "{exercice}")
])

chain = prompt | llm.with_structured_output(schema=MathReponse)

# Execute the chain
explications = chain.invoke({"exercice": "Solve 8x + 31 = 2"})

for i, etape in enumerate(explications.etapes):
    print(f"Step {i+1}: {etape.explication}")
    print(f"Intermediate Result: {etape.sortie}\n")

print(f"Final Answer: {explications.reponse_finale}")
# Printing the new verification field
print(f"Verifications: {explications.verifications}")