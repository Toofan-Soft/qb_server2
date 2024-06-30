import math
from typing import List

from model.chromatid import Chromatid
from model.dataset import st_prob_of, ls_prob_of, topics, topics_count
from model.trait import Code


class Chromosome:
    def __init__(self, chromatids: List[Chromatid]):
        self.chromatids = chromatids

    def __probs(self):
        difficulty_level_prob = sum(
            trait.value for chromatid in self.chromatids for gene in chromatid.genes for trait in gene.traits if
            trait.code == Code.DIFFICULTY_LEVEL)
        difficulty_level_r_prob = sum(
            1 for chromatid in self.chromatids for gene in chromatid.genes for trait in gene.traits if
            trait.code == Code.DIFFICULTY_LEVEL and trait.value >= 0.5)

        selection_time_prob = sum(
            (1 - st_prob_of(trait.value)) for chromatid in self.chromatids for gene in chromatid.genes for trait in
            gene.traits if trait.code == Code.SELECTION_TIMES)
        last_selection_prob = sum(
            ls_prob_of(trait.value) for chromatid in self.chromatids for gene in chromatid.genes for trait in
            gene.traits if trait.code == Code.LAST_SELECTION)

        answer_time_prob = sum(
            trait.value for chromatid in self.chromatids for gene in chromatid.genes for trait in gene.traits if
            trait.code == Code.ANSWER_TIME)

        topic_prob = [(topic, sum(
            1 for chromatid in self.chromatids for gene in chromatid.genes for trait in gene.traits if
            trait.code == Code.TOPIC and trait.value == topic)) for topic in topics()]

        return ChromosomeProb(
            difficulty_level_prob,
            difficulty_level_r_prob,
            selection_time_prob,
            last_selection_prob,
            answer_time_prob,
            topic_prob
        )

    def fitness(self, df_ct, et):
        chromosome_probs = self.__probs()

        count = sum(len(chromatid.genes) for chromatid in self.chromatids)

        final_difficulty_level_prob = 1 - abs(df_ct - (chromosome_probs.p_df / count))
        final_difficulty_level_r_prob = 1 - abs(df_ct - (chromosome_probs.p_dfr / count))

        final_selection_time_prob = chromosome_probs.p_st / count
        final_last_selection_prob = chromosome_probs.p_ls / count

        final_answer_time_prob = 1 - abs(0.5 - chromosome_probs.p_at / et)  # 0.5 is e_ratio

        # final_topic_prob = (-sum(((counter / count) * math.log(counter / count))
                                #  for (ch, counter) in chromosome_probs.p_ch if counter != 0)) / math.log(topics_count())

        final_topic_prob = 1.0 if topics_count() == 1 else (-sum(((counter / count) * math.log(counter / count))
                            for (ch, counter) in chromosome_probs.p_ch if counter != 0)) / math.log(topics_count())

        probs = [
            final_difficulty_level_prob,
            final_difficulty_level_r_prob,
            final_selection_time_prob,
            final_last_selection_prob,
            final_answer_time_prob,
            final_topic_prob
        ]

        final_1 = sum(1 for prob in probs if prob >= 0.9) / len(probs)
        final_2 = sum(prob for prob in probs) / len(probs)

        final_p = (final_1 + final_2) / 2

        return final_p

    def print(self):
        print('\n\t\t\t\tChromosome:{'
              '\n\t\t\t\t\tchromatids: [', end='')
        for chromatid in self.chromatids:
            chromatid.print()
        print('\n\t\t\t\t\t]'
              '\n\t\t\t\t}', end='')


class ChromosomeProb:
    def __init__(self, p_df, p_dfr, p_st, p_ls, p_at, p_ch):
        self.p_df = p_df
        self.p_dfr = p_dfr
        self.p_st = p_st
        self.p_ls = p_ls
        self.p_at = p_at
        self.p_ch = p_ch
