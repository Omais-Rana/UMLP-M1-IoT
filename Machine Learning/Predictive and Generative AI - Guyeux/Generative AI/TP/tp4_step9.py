import os
import re
import time
from datetime import datetime
import arxiv
from dotenv import load_dotenv
from langsmith import Client
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.tools import tool

load_dotenv()
client = Client()
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)


def invoke_safely(executor: AgentExecutor, payload: dict, fallback_output: str) -> dict:
    try:
        return executor.invoke(payload)
    except Exception as exc:
        return {"output": f"{fallback_output} | Détail: {exc}"}


@tool
def safe_arxiv_latest_iot(query: str = "IoT") -> str:
    """Retourne le dernier article ArXiv lié à l'IoT avec son titre et sa date de publication."""
    retries = 4
    for attempt in range(1, retries + 1):
        try:
            client_arxiv = arxiv.Client(num_retries=0, page_size=1, delay_seconds=0)
            search = arxiv.Search(
                query='all:"Internet of Things" OR all:IoT',
                max_results=1,
                sort_by=arxiv.SortCriterion.SubmittedDate,
                sort_order=arxiv.SortOrder.Descending,
            )
            papers = list(client_arxiv.results(search))
            if not papers:
                return "Aucun article IoT trouvé sur ArXiv."

            paper = papers[0]
            return (
                f"Title: {paper.title}\n"
                f"Published: {paper.published.date()}\n"
                f"ArXiv ID: {paper.get_short_id()}"
            )
        except Exception as exc:
            msg = str(exc)
            transient = "HTTP 429" in msg or "HTTP 503" in msg
            if transient and attempt < retries:
                time.sleep(2**attempt)
                continue
            return f"Recherche ArXiv indisponible temporairement. Erreur: {msg}"

# --- AGENT 1: CHERCHEUR (Research) ---
search_tools = [safe_arxiv_latest_iot]
search_prompt = client.pull_prompt("hwchase17/react")
chercheur_agent = create_react_agent(llm, search_tools, search_prompt)
chercheur_executor = AgentExecutor(
    agent=chercheur_agent,
    tools=search_tools,
    verbose=True,
    handle_parsing_errors=True,
    max_iterations=4,
)

# --- AGENT 2: ANALYSTE (Calculation/Summary) ---
@tool
def days_since(publication_date: str) -> str:
    """Calcule le nombre de jours écoulés entre une date de publication et le 2026-03-29."""
    accepted_formats = ["%Y-%m-%d", "%Y/%m/%d", "%d/%m/%Y", "%d-%m-%Y"]
    for fmt in accepted_formats:
        try:
            published = datetime.strptime(publication_date.strip(), fmt).date()
            target = datetime.strptime("2026-03-29", "%Y-%m-%d").date()
            return str((target - published).days)
        except ValueError:
            continue
    return "Erreur: format de date non reconnu. Utilise YYYY-MM-DD."


def deterministic_fallback_answer(research_text: str) -> str:
    title_match = re.search(r"Title:\s*(.+)", research_text)
    date_match = re.search(r"\b\d{4}-\d{2}-\d{2}\b", research_text)

    if not date_match:
        return (
            "Impossible de calculer les jours écoulés: "
            "aucune date de publication YYYY-MM-DD trouvée dans la recherche."
        )

    publication_date = date_match.group(0)
    days = days_since.invoke({"publication_date": publication_date})
    title = title_match.group(1).strip() if title_match else "Titre non disponible"
    return (
        f"Titre: {title} | Date de publication: {publication_date} | "
        f"Jours écoulés au 2026-03-29: {days}"
    )


math_tools = [days_since]
math_prompt = client.pull_prompt("hwchase17/react")
analyste_agent = create_react_agent(llm, math_tools, math_prompt)
analyste_executor = AgentExecutor(
    agent=analyste_agent,
    tools=math_tools,
    verbose=True,
    handle_parsing_errors=True,
    max_iterations=4,
)

# --- ORCHESTRATION ---
query = "Trouve le titre du dernier article sur l'IoT sur ArXiv, puis calcule combien de jours se sont écoulés depuis sa publication jusqu'à aujourd'hui."

print("\n--- Démarrage de l'Orchestration ---")
start_time = time.time()

# Step 1: Research
print("\n[ÉTAPE 1] Le Chercheur récupère les données...")
research_result = invoke_safely(
    chercheur_executor,
    {"input": query},
    "Échec de la recherche",
)
data = research_result["output"]

# Step 2: Analysis
print("\n[ÉTAPE 2] L'Analyste traite les données...")
final_result = invoke_safely(
    analyste_executor,
    {
        "input": (
            f"Voici les informations trouvées : {data}. "
            "Extrais la date de publication puis appelle l'outil days_since "
            "avec le format YYYY-MM-DD."
        )
    },
    "Échec de l'analyse",
)

if "Agent stopped due to iteration limit or time limit." in final_result.get("output", ""):
    final_result["output"] = deterministic_fallback_answer(data)

end_time = time.time()

# --- COMPARISON & RESULTS ---
print("\n" + "="*30)
print(f"RÉSULTAT FINAL : {final_result['output']}")
print(f"TEMPS TOTAL : {end_time - start_time:.2f} secondes")
print("="*30)