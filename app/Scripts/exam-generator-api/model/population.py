from typing import List

from model.individual import Individual


class Population:
    def __init__(self, individuals: List[Individual]):
        self.individuals = individuals

    def print(self):
        print('Population:{'
              '\n\tindividuals: [', end='')
        for individual in self.individuals:
            individual.print()
        print('\n\t]'
              '\n}', end='')
