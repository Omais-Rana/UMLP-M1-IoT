import os
from dotenv import load_dotenv
from langchain_community.tools import WikipediaQueryRun
from langchain_community.utilities import WikipediaAPIWrapper

load_dotenv()

# Setup the Wikipedia API Wrapper
# We limit the results to 1 and the characters to 500 as requested
api_wrapper = WikipediaAPIWrapper(top_k_results=1, doc_content_chars_max=500)
wikipedia_tool = WikipediaQueryRun(api_wrapper=api_wrapper)

def resume_article(sujet: str) -> str:
    """
    Exercise 2.1: S'appuie sur l'outil Wikipedia et tronque la réponse 
    à 500 caractères.
    """
    try:
        # wikipedia_tool.run already limits characters based on our api_wrapper config
        result = wikipedia_tool.run(sujet)
        return result
    except Exception as e:
        return f"Erreur lors de la recherche : {e}"

# Test the function
subject = "Université de Marie et Louis Pasteur-Montbéliard"
print(f"--- Résumé pour : {subject} ---\n")
print(resume_article(subject))