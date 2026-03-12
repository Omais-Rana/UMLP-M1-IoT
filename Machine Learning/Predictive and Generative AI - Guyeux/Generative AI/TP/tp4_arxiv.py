import os
from dotenv import load_dotenv
from langsmith import Client
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_community.agent_toolkits.load_tools import load_tools
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.prompts import PromptTemplate

load_dotenv()

llm = ChatMistralAI(model="mistral-large-latest", temperature=0)
tools = load_tools(["arxiv"])

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
agent_executor.invoke({"input": "Summarize the article 1605.08386 in English"})

# --- Exercise 5.2: Run with custom structured prompt ---
print("\n=== Exercise 5.2 — Structured custom prompt run ===")
agent2 = create_react_agent(llm, tools, prompt)
agent_executor2 = AgentExecutor(agent=agent2, tools=tools, verbose=True, handle_parsing_errors=True)
response = agent_executor2.invoke({"input": "Summarize the article 1605.08386 in English"})

print("\n--- Final Structured Output ---")
print(response["output"])