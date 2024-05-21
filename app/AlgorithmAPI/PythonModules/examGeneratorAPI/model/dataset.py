from typing import List

from model.gene import Gene
from model.trait import Trait, Code


global best_questions


class Question:
    def __init__(self, question_id, difficulty_level, selection_times, last_selection, answer_time, topic_id, type_id):
        self.id = question_id
        self.difficulty_level = difficulty_level
        self.selection_times = selection_times
        self.last_selection = last_selection
        self.answer_time = answer_time
        self.topic_id = topic_id
        self.type_id = type_id

    def to_gene(self):
        traits: List[Trait] = [Trait(Code.DIFFICULTY_LEVEL, self.difficulty_level),
                               Trait(Code.SELECTION_TIMES, self.selection_times),
                               Trait(Code.LAST_SELECTION, self.last_selection),
                               Trait(Code.ANSWER_TIME, self.answer_time),
                               Trait(Code.TOPIC, self.topic_id)]
        return Gene(self.id, traits)


class Dataset:
    def __init__(self, questions: List[Question]):
        self.questions = questions

        # Define Utilities..
        self.topics_count = 0

        self.st_levels = []
        self.st_max = 0
        self.ls_levels = []
        self.ls_max = 0

        self.topics = []

    def add(self, question: Question):
        self.questions.append(question)

    def build(self):
        self.topics_count = len({question.topic_id for question in self.questions})
        self.__build_st_utilities()
        self.__build_ls_utilities()
        self.__build_topics_utilities()

    def __build_st_utilities(self):
        levels = set(question.selection_times for question in self.questions)

        for i, level in enumerate(levels):
            self.st_levels.append(Level((i + 1), level))

        self.st_max = max(level.value for level in self.st_levels)

    def __build_ls_utilities(self):
        levels = set(question.last_selection for question in self.questions)

        for i, level in enumerate(levels):
            self.ls_levels.append(Level((i + 1), level))

        self.ls_max = max(level.value for level in self.ls_levels)

    def __build_topics_utilities(self):
        self.topics = list(set(question.topic_id for question in self.questions))




class Level:
    def __init__(self, _id, value):
        self.id = _id
        self.value = value


dataset = Dataset([])


def get_best_questions(type_id):
    return [question for question in dataset.questions if question.type_id == type_id]


def st_prob_of(value):
    prob = 0
    for level in dataset.st_levels:
        if level.value == value:
            prob = level.id / dataset.st_max
            break
    return prob


def ls_prob_of(value):
    prob = 0
    for level in dataset.ls_levels:
        if level.value == value:
            prob = level.id / dataset.ls_max
            break
    return prob


def topics():
    return dataset.topics


def topics_count():
    return len(dataset.topics)
