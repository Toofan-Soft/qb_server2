from typing import List

from model.trait import Trait


class Gene:
    def __init__(self, _id, traits: List[Trait]):
        self.id = _id
        self.traits = traits

    def print(self):
        print('\n\t\t\t\t\t\tGene:{'
              f'\n\t\t\t\t\t\t\tid: {self.id}, '
              '\n\t\t\t\t\t\t\ttraits: [', end='')
        for trait in self.traits:
            trait.print()
        print('\n\t\t\t\t\t\t\t]'
              '\n\t\t\t\t\t\t}', end='')
