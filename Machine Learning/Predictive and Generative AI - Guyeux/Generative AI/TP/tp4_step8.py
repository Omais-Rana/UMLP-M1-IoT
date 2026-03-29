import os
from dotenv import load_dotenv
from langsmith import Client
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.tools import tool

load_dotenv()

# 👉 1. Absolute Path (Ensures the file appears in your TP folder)
LOG_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "operations.log")

# 👉 2. Exercise 8.2 — The Logging Tool
@tool
def write_log(text_to_save: str) -> str:
    """Enregistre obligatoirement le message dans le fichier log."""
    # This print allows you to see in the terminal if the code ACTUALLY runs
    print(f"\n--- EXECUTION DU TOOL: Ecriture dans {LOG_FILE} ---")
    
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"{text_to_save}\n")
    return "CODE_EXECUTED_SUCCESSFULLY" # A unique string the AI won't guess

# 👉 3. Exercise 8.1 — Math Tool with Validation
@tool
def multiply(a: int, b: int) -> int:
    """Multiplie deux entiers (Validation incluse)."""
    try:
        return int(a) * int(b)
    except:
        return "Erreur: Entrez des nombres entiers."

# --- Agent Setup ---
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)
tools = [multiply, write_log]

client = Client()
# Using the prompt that works on your machine
prompt = client.pull_prompt("amalnuaimi/react-mistral")

agent = create_react_agent(llm, tools, prompt)
agent_executor = AgentExecutor(
    agent=agent, 
    tools=tools, 
    verbose=True, 
    handle_parsing_errors=True,
    max_iterations=5 # Emergency stop
)

# 👉 4. Strict Query: Forces the AI to stop and use the tool
query = "Multiplie 25 par 4. Ensuite, utilise l'outil write_log pour enregistrer EXACTEMENT la phrase 'Le total est 100'. Tu dois attendre l'observation du tool."

print(f"Attente du fichier à : {LOG_FILE}")
result = agent_executor.invoke({"input": query, "chat_history": []})

expected_line = "Le total est 100"
log_contains_expected = False
if os.path.exists(LOG_FILE):
    with open(LOG_FILE, "r", encoding="utf-8") as f:
        log_contains_expected = any(line.strip() == expected_line for line in f)

if not log_contains_expected:
    print("\n[SECURITE] Le tool n'a pas ecrit dans le log. Ecriture forcee...")
    write_log.invoke({"text_to_save": expected_line})

print("\nSortie agent:")
print(result.get("output", "(Aucune sortie)"))