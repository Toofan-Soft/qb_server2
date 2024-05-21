from typing import List

import numpy as np

from model.chromatid import Chromatid
from model.chromosome import Chromosome
from model.gene import Gene
from model.individual import Individual
from model.population import Population
from model.dataset import get_best_questions


def init(individuals_count, chromosomes_count, chromatids_and_gens):
    individuals: List[Individual] = []

    for _ in range(individuals_count):
        chromosomes: List[Chromosome] = []

        for _ in range(chromosomes_count):
            chromatids: List[Chromatid] = []

            for chromatid, gens_count in chromatids_and_gens:
                genes: List[Gene] = []

                for ques in np.random.choice(get_best_questions(chromatid), size=gens_count, replace=False):
                    genes.append(ques.to_gene())

                chromatids.append(Chromatid(chromatid, genes))

            chromosomes.append(Chromosome(chromatids))

        individuals.append(Individual(chromosomes))

    population = Population(individuals)

    return population
