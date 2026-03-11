from pydantic import BaseModel, Field
from langchain_core.prompts import ChatPromptTemplate
from langchain_mistralai.chat_models import ChatMistralAI
from dotenv import load_dotenv

load_dotenv()

# Define the model here so it's available for your chain
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

class TonMessage(BaseModel):
    """Evaluation of the message tone."""
    note_ton: int = Field(..., ge=1, le=5, description="Score from 1 (neutral) to 5 (very kind)")
    commentary: str = Field(..., description="Explanation for the assigned score") # Exercise 4.1

prompt_msgs = [
    ("system", "Evaluate the tone of the message from 1 to 5."),
    ("human", "{text}")
]

chain = ChatPromptTemplate.from_messages(prompt_msgs) | llm.with_structured_output(schema=TonMessage)

# Exercise 4.2: Calculating an average
msgs = ["Could you please help?", "Gimme the bus time!"]
scores = [chain.invoke({"text": m}).note_ton for m in msgs]
print(f"Average Tone Score: {sum(scores)/len(scores)}")