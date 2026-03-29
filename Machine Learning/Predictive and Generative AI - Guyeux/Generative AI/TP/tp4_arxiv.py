import os
import time
import arxiv
from dotenv import load_dotenv
from langsmith import Client
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_core.tools import Tool
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.prompts import PromptTemplate

load_dotenv()

llm = ChatMistralAI(model="mistral-large-latest", temperature=0)


def safe_arxiv_search(query: str) -> str:
	normalized_query = query.strip()
	arxiv_id = None
	if normalized_query.lower().startswith("id:"):
		arxiv_id = normalized_query.split(":", 1)[1].strip()
	elif normalized_query.replace(".", "").replace("v", "").isdigit():
		arxiv_id = normalized_query

	retries = 4
	base_wait_seconds = 2
	for attempt in range(1, retries + 1):
		try:
			client = arxiv.Client(num_retries=0, page_size=3, delay_seconds=0)
			if arxiv_id:
				search = arxiv.Search(id_list=[arxiv_id], max_results=1)
			else:
				search = arxiv.Search(
					query=normalized_query,
					max_results=3,
					sort_by=arxiv.SortCriterion.Relevance,
				)

			papers = list(client.results(search))
			if not papers:
				return f"No arXiv results found for query: {normalized_query}"

			chunks = []
			for idx, paper in enumerate(papers, start=1):
				authors = ", ".join(author.name for author in paper.authors[:5])
				chunks.append(
					f"Result {idx}:\n"
					f"Title: {paper.title}\n"
					f"ID: {paper.get_short_id()}\n"
					f"Published: {paper.published.date()}\n"
					f"Authors: {authors}\n"
					f"Summary: {paper.summary}"
				)
			return "\n\n".join(chunks)
		except Exception as exc:
			message = str(exc)
			is_transient = "HTTP 429" in message or "HTTP 503" in message
			if is_transient and attempt < retries:
				wait_seconds = base_wait_seconds ** attempt
				print(
					f"[arxiv] transient error ({message}). "
					f"Retry {attempt}/{retries - 1} in {wait_seconds}s..."
				)
				time.sleep(wait_seconds)
				continue
			return (
				"arXiv tool temporarily unavailable after retries. "
				f"Last error: {message}. "
				"If needed, answer with a limitation notice instead of failing."
			)


tools = [
	Tool(
		name="arxiv",
		description=(
			"A wrapper around Arxiv.org Useful for when you need to answer questions "
			"about Physics, Mathematics, Computer Science, Quantitative Biology, "
			"Quantitative Finance, Statistics, Electrical Engineering, and Economics "
			"from scientific articles on arxiv.org. Input should be a search query."
		),
		func=safe_arxiv_search,
	)
]

# Exercise 5.1 — Document the tool
print("=== Exercise 5.1 — Tool Documentation ===")
print(f"Tool name:        {tools[0].name}")
print(f"Tool description: {tools[0].description}")
print()

# Exercise 5.2 — Custom structured prompt
custom_template = """You are a scientific literature assistant. Answer in English.
When summarizing a paper, you MUST structure your response exactly as follows:

**Summary** : A concise summary of the paper.
**Key points** : The key contributions or findings as a bullet list.
**Potential applications** : Concrete real-world applications of this research.

You have access to the following tools:
{tools}

Use this format strictly:
Question: the input question you must answer
Thought: your reasoning
Action: the action to take, must be one of [{tool_names}]
Action Input: the input to the action
Observation: the result of the action
... (repeat Thought/Action/Action Input/Observation as needed)
Thought: I now know the final answer
Final Answer: [your answer structured with Summary, Key points, Potential applications]

Begin!

Question: {input}
Thought:{agent_scratchpad}"""

prompt = PromptTemplate.from_template(custom_template)

# --- Exercise 5.1: Run with default prompt ---
print("=== Exercise 5.1 — Default prompt run ===")
client = Client()
default_prompt = client.pull_prompt("hwchase17/react")
agent = create_react_agent(llm, tools, default_prompt)
agent_executor = AgentExecutor(agent=agent, tools=tools, verbose=True, handle_parsing_errors=True)
default_response = agent_executor.invoke({"input": "Summarize the article 1605.08386 in English"})
print("\n--- Default Prompt Output ---")
print(default_response["output"])

# --- Exercise 5.2: Run with custom structured prompt ---
print("\n=== Exercise 5.2 — Structured custom prompt run ===")
agent2 = create_react_agent(llm, tools, prompt)
agent_executor2 = AgentExecutor(agent=agent2, tools=tools, verbose=True, handle_parsing_errors=True)
response = agent_executor2.invoke({"input": "Summarize the article 1605.08386 in English"})

print("\n--- Final Structured Output ---")
print(response["output"])