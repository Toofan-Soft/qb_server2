import model.dataset
from crossover_mutation import crossover_mutation
from fitness import calc
from population import init
from selection import select

__individuals_count = 8
__generations_count = 1000
__pc = 0.7
__pm = 0.01


def __init_dataset(json_data):
    for question in json_data:
        id = question.get('id')
        difficulty_level = question.get('difficulty_level')
        selection_times = question.get('selection_times')
        last_selection = question.get('last_selection')
        answer_time = question.get('answer_time')
        topic_id = question.get('topic_id')
        type_id = question.get('type_id')

        model.dataset.dataset.add(
            model.dataset.Question(id, difficulty_level, selection_times, last_selection, answer_time, topic_id,
                                   type_id)
        )
    
    model.dataset.dataset.build()


def __init_population(chromosomes_count, chromatids_and_gens):
    population = init(__individuals_count, chromosomes_count, chromatids_and_gens)
    return population


def __run_model(population, difficulty_coefficient, estimated_time):
    best_fitness_overall = None

    best_solution = None

    for i_gen in range(__generations_count):
        fitness_vals = calc(population, difficulty_coefficient, estimated_time)

        best_i = fitness_vals.argmax()
        best_fitness = fitness_vals[best_i]

        if best_fitness_overall is None or best_fitness > best_fitness_overall:
            best_fitness_overall = best_fitness
            best_solution = population.individuals[best_i]

        if best_fitness >= 0.95:
            break
        
        selected_pop = select(population, fitness_vals)
        crossover_mutation(selected_pop, __pc, __pm)
    
    return best_solution


def __get_result(individual):
    return [[gene.id for chromatid in chromosome.chromatids for gene in chromatid.genes] for chromosome in
            individual.chromosomes]


def generate_exam(questions, forms_count, question_types_and_questions_count, difficulty_level, estimated_time):
    __init_dataset(questions)

    chromatids_and_gens = []
    for item in question_types_and_questions_count:
        chromatids_and_gens.append((item["id"], item["count"]))

    population = __init_population(chromosomes_count=forms_count, chromatids_and_gens=chromatids_and_gens)
    best_individual = __run_model(population, difficulty_level, estimated_time)
    return __get_result(best_individual)
