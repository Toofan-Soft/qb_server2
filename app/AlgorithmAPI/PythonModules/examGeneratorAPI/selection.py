import numpy as np

from model.population import Population


def select(population, fitness_vals):
    probs = fitness_vals.copy()
    probs += abs(probs.min()) + 1
    probs /= probs.sum()

    N = len(population.individuals)
    indices = np.arange(N)

    selected_indices = np.random.choice(indices, size=N, p=probs)

    selected_population = Population(np.array(population.individuals)[selected_indices].tolist())
    return selected_population
