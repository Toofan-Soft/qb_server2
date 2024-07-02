import json
import sys

from generator import generate, Combination, Choice


def combine(json_data):
    choices = []
    for item in json_data:
        choices.append(Choice.from_json(item))
    return generate(choices)


def uncombine(value):
    combination = Combination.uncombine(value)
    return [choice.to_dict() for choice in combination.choices]
    

def main():
    if len(sys.argv) != 3:
        print("Usage: python example.py <method_name> <json_data>")
        sys.exit(1)

    method_name = sys.argv[1]
    json_data = sys.argv[2]
    data = json.loads(json_data)

    if method_name == 'combine':
        result = combine(data)
    elif method_name == 'uncombine':
        result = uncombine(data)
    else:
        print(f"Unknown method: {method_name}")
        sys.exit(1)

    # Output result as JSON
    print(json.dumps(result))

if __name__ == "__main__":
    main()

# json_data = [
#     {"id": 1, ""isCorrect: True},
#     {"id": 2, "isCorrect": True},
#     {"id": 3, "isCorrect": True},
#     {"id": 4, "isCorrect": False},
#     {"id": 5, "isCorrect": False},
#     {"id": 6, "isCorrect": False},
#     {"id": 7, "isCorrect": False},
#     {"id": 8, "isCorrect": False}
# ]

# combinations = combine(json_data)
# print(combinations)


# print(uncombine('1,2,3,∞•'))
