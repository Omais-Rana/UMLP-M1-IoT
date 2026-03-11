import os
from dotenv import load_dotenv
from langchain_mistralai.chat_models import ChatMistralAI
from langchain_core.messages import HumanMessage

# 1. Securely load the API key from the .env file
load_dotenv()

# 2. Initialize the Mistral model
# Exercise 3.2: You can change temperature to 0.7 here to make responses more creative!
llm = ChatMistralAI(
    model="mistral-large-latest",
    temperature=0, 
)

# 3. Create the prompt and invoke the model
question = "What is the capital of France ?"
message = HumanMessage(content=question)

print(f"Asking Mistral: '{question}'...\n")
response = llm.invoke([message])

# 4. Print the response content
print("--- Response ---")
print(response.content)

# ==========================================
# Exercise 3.1: Usage Metadata & Cost Estimation
# ==========================================
print("\n--- Usage Metadata ---")
metadata = response.usage_metadata
print(metadata)

# Note for Cost Calculation:
# As of current pricing, Mistral Large costs roughly:
# Input: $2.00 per 1M tokens | Output: $6.00 per 1M tokens
input_tokens = metadata.get('input_tokens', 0)
output_tokens = metadata.get('output_tokens', 0)

estimated_cost = (input_tokens / 1_000_000 * 2.00) + (output_tokens / 1_000_000 * 6.00)
print(f"\nEstimated cost for this single request: ${estimated_cost:.6f}")