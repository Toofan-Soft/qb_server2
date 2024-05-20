from crossover import crossover
from mutation import mutation


def crossover_mutation(selected_pop, pc, pm):
    s = len(selected_pop.individuals) // 2 * 2
    for i in range(0, s, 2):
        first_individual = selected_pop.individuals[i]
        second_individual = selected_pop.individuals[i+1]
        crossover(first_individual, second_individual, pc)
    for individual in selected_pop.individuals:
        mutation(individual, pm)
