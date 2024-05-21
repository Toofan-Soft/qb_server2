import numpy as np


def calc(population, df_ct, et):
    fitness_vals = []

    for individual in population.individuals:
        fitness_vals.append(individual.fitness(df_ct, et))
    return np.array(fitness_vals)

