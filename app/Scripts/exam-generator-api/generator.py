import json

import model.dataset
from crossover_mutation import crossover_mutation
from population import init
from fitness import calc
from selection import select

__individuals_count = 8
__generations_count = 10000
__pc = 0.7
__pm = 0.01


# question structure (id, difficulty_level, selection_times, last_selection, answer_time, topic_id)
def __init_dataset(questions):
    questions = json.loads(questions)

    for question in questions:
        id = question.get('id')
        difficulty_level = question.get('difficulty_level')
        selection_times = question.get('selection_times')
        last_selection = question.get('last_selection')
        answer_time = question.get('answer_time')
        topic_id = question.get('topic_id')
        type_id = question.get('type_id')

        model.dataset.dataset.add(
            model.dataset.Question(id, difficulty_level, selection_times, last_selection, answer_time, topic_id, type_id)
        )


def __init_population(chromosomes_count, chromatids_and_gens):
    population = init(__individuals_count, chromosomes_count, chromatids_and_gens)
    return population


def __run_model(population, difficulty_coefficient, estimated_time):
    best_fitness_overall = None

    for i_gen in range(__generations_count):
        fitness_vals = calc(population, difficulty_coefficient, estimated_time)

        best_i = fitness_vals.argmax()
        best_fitness = fitness_vals[best_i]

        if best_fitness_overall is None or best_fitness > best_fitness_overall:
            best_fitness_overall = best_fitness
            # x = best_fitness_overall.copy()
            best_solution = population.individuals[best_i]
            print(f'\t\t\t-f={best_fitness_overall}')
            # print_solution(best_solution, df_ct, et)

        print(f'\ri_gen = {i_gen:06}   -f={-best_fitness_overall:03}', end='')

        if best_fitness >= 0.95:
            print('\nFound optimal solution')
            break
        selected_pop = select(population, fitness_vals)
        crossover_mutation(selected_pop, __pc, __pm)

    print('\nend:')
    # print_solution(best_solution, df_ct, et)


# question structure (id, difficulty_level, selection_times, last_selection, answer_time, topic_id)
# question_types_and_questions_count structure (question_type_id, questions_count) -> [(1, 10), (2, 20), ...]
def generate_exam(questions, forms_count, question_types_and_questions_count, difficulty_level, estimated_time):
    __init_dataset(questions)
    population = __init_population(chromosomes_count=forms_count, chromatids_and_gens=question_types_and_questions_count)
    __run_model(population, difficulty_level, estimated_time)
    return


# questions_data = [
#     {
#         "id": 1,
#         "difficulty_level": "easy",
#         "selection_times": 10,
#         "last_selection": "2024-04-22",
#         "answer_time": 20,
#         "topic_id": 101
#     },
#     {
#         "id": 2,
#         "difficulty_level": "medium",
#         "selection_times": 15,
#         "last_selection": "2024-04-21",
#         "answer_time": 30,
#         "topic_id": 102
#     },
#     {
#         "id": 3,
#         "difficulty_level": "hard",
#         "selection_times": 20,
#         "last_selection": "2024-04-20",
#         "answer_time": 40,
#         "topic_id": 103
#     }
# ]
#
# questions_json = json.dumps(questions_data)
# print("JSON String:")
# print(questions_json)
#
# print(model.dataset.dataset.questions)
# __init_dataset(questions_json)
# print([question.id for question in model.dataset.dataset.questions])
