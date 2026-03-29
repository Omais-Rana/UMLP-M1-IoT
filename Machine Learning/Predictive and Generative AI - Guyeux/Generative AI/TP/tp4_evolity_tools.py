import os
from dotenv import load_dotenv
from langsmith import Client
from langchain_core.tools import tool
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

# Exercise 8.2 — Helper for logging
def log_to_file(operation: str, result: str):
    """Enregistre chaque opération dans un fichier operations.log."""
    with open("operations.log", "a", encoding="utf-8") as f:
        f.write(f"Operation: {operation} | Result: {result}\n")

# Exercise 8.1 — Tools with Type Validation
@tool
def multiply(first_int: int, second_int: int) -> int:
    """Multiplie deux entiers."""
    try:
        # Strict validation
        val1, val2 = int(first_int), int(second_int)
        res = val1 * val2
        log_to_file(f"Multiply({val1}, {val2})", str(res))
        return res
    except (ValueError, TypeError):
        return "Error: Both inputs must be valid integers."

@tool
def add(first_int: int, second_int: int) -> int:
    """Ajoute deux entiers."""
    try:
        val1, val2 = int(first_int), int(second_int)
        res = val1 + val2
        log_to_file(f"Add({val1}, {val2})", str(res))
        return res
    except (ValueError, TypeError):
        return "Error: Both inputs must be valid integers."

@tool
def exponentiate(base: int, exponent: int) -> int:
    """Calcule la puissance d'un entier donné."""
    try:
        b, e = int(base), int(exponent)
        res = b ** e
        log_to_file(f"Exponentiate({b}, {e})", str(res))
        return res
    except (ValueError, TypeError):
        return "Error: Both inputs must be valid integers."

# --- Setup Agent ---
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)
tools = [multiply, add, exponentiate]

# Using the LangSmith Client method that worked for you
client = Client()
prompt = client.pull_prompt("amalnuaimi/react-mistral")

agent = create_react_agent(llm, tools, prompt)
agent_executor = AgentExecutor(
    agent=agent,
    tools=tools,
    verbose=True,
    handle_parsing_errors=True
)

# Test query involving the math logic: 3^5 * (12 + 3)^2
query = "Porter 3 à la puissance 5 et multiplier le résultat par la somme de douze et de trois, puis élever le tout au carré."

response = agent_executor.invoke({
    "input": query,
    "chat_history": []
})

print("\n--- Result ---")
print(response["output"])