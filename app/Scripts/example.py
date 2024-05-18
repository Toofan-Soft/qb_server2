import sys
import json

print("Hello, World from Python!")

data = json.loads(sys.argv[1])

# Modify the data as needed
data['age'] += 1  # Example modification

# Print the updated data as JSON
print(json.dumps(data))
