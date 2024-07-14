import json
import sys

from generator import generate_exam


def generate(json_data):
    """
    Generate an exam based on the given JSON data.
    Parameters:
        json_data (dict): The input data containing the following keys:
            - "questions" (list): A list of questions where each question is a dictionary where each dictionary has:
                - "id" (int): The unique identifier of the question.
                - "difficulty_level" (float): The difficulty level of the question.
                - "selection_times" (int): The number of times the question has been selected.
                - "last_selection" (int): The last selection time of the question.
                - "answer_time" (int): The time taken to answer the question.
                - "topic_id" (int): The ID of the topic this question belongs to.
                - "type_id" (int): The type ID of the question.
            - "forms_count" (int): The number of forms to generate.
            - "question_types_and_questions_count" (list): A list of dictionaries where each dictionary has:
                - "id" (int): The type ID of the questions.
                - "count" (int): The number of questions of this type.
            - "difficulty_level" (float): The difficulty level of the exam.
            - "estimated_time" (int): The estimated time for the exam in minutes.

       Returns:
       str: A JSON string representing the generated exam.
            [[first form questions ids...], [second form questions ids...], ...]
       """
    questions = json_data["questions"]
    forms_count = json_data["forms_count"]
    question_types_and_questions_count = json_data["question_types_and_questions_count"]
    difficulty_level = json_data["difficulty_level"]
    estimated_time = json_data["estimated_time"]

    return generate_exam(questions, forms_count, question_types_and_questions_count, difficulty_level, estimated_time)


def main():
    if len(sys.argv) != 2:
        print("Usage: python example.py <method_name>")
        sys.exit(1)

    method_name = sys.argv[1]

    try:
        # Read JSON data from stdin
        json_data = sys.stdin.read()
        data = json.loads(json_data)
    except Exception as e:
        print(f"Error reading JSON data: {e}")
        sys.exit(1)
    

    if method_name == 'generate':
        result = generate(data)
    else:
        print(f"Unknown method: {method_name}")
        sys.exit(1)

    # Output result as JSON
    print(json.dumps(result))


if __name__ == "__main__":
    main()
