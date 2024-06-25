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

    result = generate_exam(questions, forms_count, question_types_and_questions_count, difficulty_level, estimated_time)
    # return json.dumps(result, ensure_ascii=False, indent=2)
    return result


def main():
    if len(sys.argv) != 3:
        print("Usage: python example.py <method_name> <json_data>")
        sys.exit(1)

    method_name = sys.argv[1]
    json_data = sys.argv[2]
    data = json.loads(json_data)

    if method_name == 'generate':
        result = generate(data)
    else:
        print(f"Unknown method: {method_name}")
        sys.exit(1)

    # Output result as JSON
    print(json.dumps(result))


if __name__ == "__main__":
    main()


# json_data = {
#     "difficulty_level": 0.5,
#     "estimated_time": 200,
#     "forms_count": 2,
#     "question_types_and_questions_count": [
#         {
#             "id": 1,
#             "count": 3
#         },
#         {
#             "id": 2,
#             "count": 2
#         },
#     ],
#     "questions": [
#         {
#             "id": 1,
#             "difficulty_level": 0.4,
#             "selection_times": 10,
#             "last_selection": 7,
#             "answer_time": 20,
#             "topic_id": 101,
#             "type_id": 1
#         },
#         {
#             "id": 2,
#             "difficulty_level": 0.6,
#             "selection_times": 15,
#             "last_selection": 5,
#             "answer_time": 30,
#             "topic_id": 102,
#             "type_id": 1
#         },
#         {
#             "id": 3,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 10,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 4,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 3,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 5,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 4,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 6,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 5,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 7,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 2,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 8,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 3,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 2
#         },
#         {
#             "id": 9,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 4,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 1
#         },
#         {
#             "id": 10,
#             "difficulty_level": 0.6,
#             "selection_times": 20,
#             "last_selection": 1,
#             "answer_time": 40,
#             "topic_id": 103,
#             "type_id": 1
#         },
#     ]
# }
#
# print(generate(json_data))
#
