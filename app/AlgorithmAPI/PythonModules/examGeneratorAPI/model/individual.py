from typing import List

from model.chromosome import Chromosome


class Individual:
    def __init__(self, chromosomes: List[Chromosome]):
        self.chromosomes = chromosomes

    def fitness(self, df_ct, et):
        total_fitness = sum(chromosome.fitness(df_ct, et) for chromosome in self.chromosomes)
        average_fitness = total_fitness / len(self.chromosomes)
        return average_fitness

    def print(self):
        print('\n\t\tIndividual:{'
              '\n\t\t\tchromosomes: [', end='')
        for chromosome in self.chromosomes:
            chromosome.print()
        print('\n\t\t\t]'
              '\n\t\t}', end='')
