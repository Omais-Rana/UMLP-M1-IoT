from dotenv import load_dotenv
from pydantic import BaseModel, Field
from langchain_core.prompts import ChatPromptTemplate
from langchain_mistralai.chat_models import ChatMistralAI
from typing import Literal

load_dotenv()
# Define the list of actions allowed
tasks = ["Find Route", "Check Price", "Bus Schedule", "Request Clarification"]

class NextTask(BaseModel):
    """Structures the next action to take for the chatbot."""
    # This replaces 'str' and 'enum=tasks' with a strict set of options
    action: Literal["Find Route", "Check Price", "Bus Schedule", "Request Clarification"] = Field(
        ...,
        description="The next logical action to perform"
    )

prompt_msgs = [
    ("system", "You are a transit classification assistant. Determine the next action."),
    ("human", "{text}")
]

prompt = ChatPromptTemplate.from_messages(prompt_msgs)
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)
chain = prompt | llm.with_structured_output(schema=NextTask)

# Test cases
for query in ["How much for a ticket?", "What time is the next bus?"]:
    decision = chain.invoke({"text": query})
    print(f"Query: {query} -> Action: {decision.action}")