import os
from dotenv import load_dotenv
from pydantic import BaseModel, Field
from langchain_core.prompts import ChatPromptTemplate
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

# Define the schema
class Translation(BaseModel):
    original_text: str = Field(..., description="The original text before translation")
    original_language: str = Field(..., description="The original language")
    translated_text: str = Field(..., description="The text after translation")
    translated_language: str = Field(..., description="The target language")

llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

# Build the chain
prompt = ChatPromptTemplate.from_template(
    "Translate the following into {lang}: {text}"
)

# Use .with_structured_output to force the model to follow our Translation class
chain = prompt | llm.with_structured_output(Translation)

# Execute
result = chain.invoke({"lang": "English", "text": "Quelle est la capitale de l'Albanie ?"})
print(f"Original ({result.original_language}): {result.original_text}")
print(f"Translated ({result.translated_language}): {result.translated_text}")