import os
from dotenv import load_dotenv
from pydantic import BaseModel
from langchain_core.prompts import ChatPromptTemplate
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

# Define the expected schema
class Answer(BaseModel):
    answer: bool

# Prepare the prompt
prompt_msgs = [
    ("system", "You are an assistant who must answer with a boolean (True or False) to user questions."),
    ("human", "{question}")
]

prompt = ChatPromptTemplate.from_messages(prompt_msgs)
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

# Bind the structured output to the model
chain = prompt | llm.with_structured_output(schema=Answer)

# Exercise 2.1 & 2.2: Test and verify types
questions = ["Is Montbéliard in France?", "Does a bus fly?"]

for q in questions:
    result = chain.invoke({"question": q})
    print(f"Question: {q} | Answer: {result.answer} | Type: {type(result.answer)}")