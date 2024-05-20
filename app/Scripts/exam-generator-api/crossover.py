import numpy as np

from model.dataset import dataset


def __crossover(first_genes, second_genes):
    genes = first_genes
    genes_ids = [gene.id for gene in first_genes]

    for curr_gen in second_genes:
        if curr_gen.id in genes_ids:
            replacement = next(q for q in dataset.questions if q.id not in genes_ids)
            new_gene = replacement.getGene()
            genes.append(new_gene)
            genes_ids.append(new_gene.id)
        else:
            genes.append(curr_gen)
            genes_ids.append(curr_gen.id)

    return genes


def crossover(first_individual, second_individual, pc):
    for chromosome1, chromosome2 in zip(first_individual.chromosomes, second_individual.chromosomes):
        for chromatid1, chromatid2 in zip(chromosome1.chromatids, chromosome2.chromatids):
            r = np.random.random()
            if r < pc:
                cp = np.random.randint(1, len(chromatid1.genes))
                child1 = __crossover(list(chromatid1.genes[:cp]), list(chromatid2.genes[cp:]))
                child2 = __crossover(list(chromatid2.genes[:cp]), list(chromatid1.genes[cp:]))

                chromatid1.genes = child1
                chromatid2.genes = child2
