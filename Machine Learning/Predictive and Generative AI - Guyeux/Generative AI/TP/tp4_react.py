import os
from dotenv import load_dotenv
from langchain import hub
from langchain.agents import create_react_agent, AgentExecutor
from langchain_community.tools.tavily_search import TavilySearchResults
from langchain_community.tools import WikipediaQueryRun
from langchain_community.utilities import WikipediaAPIWrapper
from langchain_mistralai.chat_models import ChatMistralAI

load_dotenv()

# 1. Setup Tools (Exercise 3.1: Combining Wikipedia and Tavily)
search = TavilySearchResults(max_results=2)
wiki_api = WikipediaAPIWrapper(top_k_results=1, doc_content_chars_max=500)
wikipedia = WikipediaQueryRun(api_wrapper=wiki_api)

tools = [search, wikipedia]

# 2. Setup LLM
llm = ChatMistralAI(model="mistral-large-latest", temperature=0)

# 3. Pull the Prompt Template (Exercise 3.2: Formatting instructions)
# We use a standard ReAct prompt from the LangChain Hub
prompt = hub.pull("hwchase17/react")

# 4. Create the Agent and Executor
agent = create_react_agent(llm, tools, prompt)

# handle_parsing_errors=True is vital for stability
agent_executor = AgentExecutor(
    agent=agent, 
    tools=tools, 
    verbose=True, 
    handle_parsing_errors=True
)

# 5. Execute with custom instructions (Exercise 3.2: Formal French & Sources)
query = "Dois-je prendre un parapluie, sachant que je me rends aujourd'hui et demain à Belfort ? Réponds en français formel et cite tes sources."

response = agent_executor.invoke({
    "input": query,
    "chat_history": []
})

print("\n--- Final Output ---")
print(response["output"])