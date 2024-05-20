import numpy as np

from model.dataset import get_best_questions


def mutation(individual, pm):
    for chromosome in individual.chromosomes:
        r = np.random.random()
        if r < pm:
            for chromatid in chromosome.chromatids:
                if r < pm:
                    m = np.random.randint(len(chromatid.genes))

                    new_ques = None

                    while new_ques is None:
                        best_questions = get_best_questions(chromatid.id)

                        index = np.random.randint(len(best_questions))
                        ques = best_questions[index]
                        if ques.id not in [gene.id for gene in chromosome.genes]:
                            new_ques = ques

                    chromatid.genes[m] = new_ques.getGene()
    return individual
