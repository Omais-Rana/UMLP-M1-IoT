import os
from dotenv import load_dotenv
from langsmith import Client
from langchain_classic.agents import create_react_agent, AgentExecutor
from langchain_tavily import TavilySearch
from langchain_community.tools import WikipediaQueryRun
from langchain_community.utilities import WikipediaAPIWrapper
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

search = TavilySearch(max_results=2)
wiki_api = WikipediaAPIWrapper(top_k_results=1, doc_content_chars_max=500)
wikipedia = WikipediaQueryRun(api_wrapper=wiki_api)
tools = [search, wikipedia]

llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

client = Client()
prompt = client.pull_prompt("amalnuaimi/react-mistral")

agent = create_react_agent(llm, tools, prompt)
agent_executor = AgentExecutor(
    agent=agent,
    tools=tools,
    verbose=True,
    handle_parsing_errors=True
)

query = "Dois-je prendre un parapluie, sachant que je me rends aujourd'hui et demain à Belfort ? Réponds en français formel et cite tes sources."

response = agent_executor.invoke({
    "input": query,
    "chat_history": []
})

print("\n--- Final Output ---")
print(response["output"])