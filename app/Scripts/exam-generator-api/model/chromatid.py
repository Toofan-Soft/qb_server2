from typing import List

from gene import Gene


class Chromatid:
    def __init__(self, id, genes: List[Gene]):
        self.id = id
        self.genes = genes

    def print(self):
        print('\n\t\t\t\t\t\tChromatid:{'
              '\n\t\t\t\t\t\t\tid: {self.id}, '
              '\n\t\t\t\t\t\t\tgenes: [', end='')
        for gene in self.genes:
            gene.print()
        print('\n\t\t\t\t\t\t\t]'
              '\n\t\t\t\t\t\t}', end='')
