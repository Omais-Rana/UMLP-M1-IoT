import os
from dotenv import load_dotenv
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import JsonOutputParser
from langchain_core.runnables import ConfigurableField

# Load environment variables
load_dotenv()

# Exercise 4.3: Define a model with a configurable temperature field
model = ChatMistralAI(model="mistral-large-latest", temperature=0).configurable_fields(
    temperature=ConfigurableField(
        id="llm_temperature",
        name="LLM Temperature",
        description="The creativity of the model (0.0 to 1.0)"
    )
)

# Exercise 4.2: Structured prompt for JSON output
# This guides the model to return a specific dictionary structure
prompt = ChatPromptTemplate.from_template(
    "Tell me a property of the following object: {object}. "
    "Respond only in JSON format with the keys 'setup' and 'punchline'."
)

# Composition of the chain using the pipe operator (|)
# Prompt -> Model -> JSON Parser
chain = prompt | model | JsonOutputParser()

# Exercise 4.1: Dynamic user input loop with error handling
while True:
    user_object = input("\nEnter an object to describe (or type 'exit' to quit): ").strip()
    
    if user_object.lower() == 'exit':
        break
    if not user_object:
        print("⚠️ Error: The object cannot be empty!")
        continue
    
    # Invoke the chain with a specific temperature (Exercise 4.3)
    try:
        response = chain.invoke(
            {"object": user_object}, 
            config={"configurable": {"llm_temperature": 0.8}} 
        )
        
        print("\n--- Structured Description (JSON) ---")
        print(f"Property: {response['setup']}")
        print(f"Description: {response['punchline']}")
        
    except Exception as e:
        print(f"An error occurred: {e}")