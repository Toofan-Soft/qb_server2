import sys
import json

# print("Hello, World from Python!")

# data = json.loads(sys.argv[2])

# # Modify the data as needed
# data['age'] += 1  # Example modification


def method_one(data):
    # Process data for method one
    return {
        'status': 'success',
        'message': f"Processed data in method_one for {data['name']}"
    }
def method_two(data):
    # Process data for method two
    return {
        'status': 'success',
        'message': f"Processed data in method_two for {data['name']}"
    }

def main():
    if len(sys.argv) != 3:
        print("Usage: python example.py <method_name> <json_data>")
        sys.exit(1)

    method_name = sys.argv[1]
    json_data = sys.argv[2]
    data = json.loads(json_data)

    if method_name == 'method_one':
        result = method_one(data)
    elif method_name == 'method_two':
        result = method_two(data)
    else:
        print(f"Unknown method: {method_name}")
        sys.exit(1)

    # Output result as JSON
    print(json.dumps(result))

if __name__ == "__main__":
    main()
