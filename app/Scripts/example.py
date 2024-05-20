import sys
import json

print("Hello, World from Python!")

data = json.loads(sys.argv[2])

# Modify the data as needed
data['age'] += 1  # Example modification

# Print the updated data as JSON
print(json.dumps(data))


def process_data():
    # Your logic to process data
    print(f"Processing data using process_data method: ")
