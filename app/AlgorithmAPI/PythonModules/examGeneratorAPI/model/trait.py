from enum import Enum


class Code(Enum):
    DIFFICULTY_LEVEL = 1
    SELECTION_TIMES = 2
    LAST_SELECTION = 3
    ANSWER_TIME = 4
    TOPIC = 5


class Trait:
    def __init__(self, code: Code, value):
        self.code = code
        self.value = value

    def print(self):
        print('\n\t\t\t\t\t\t\t\tTrait:{'
              f'\n\t\t\t\t\t\t\t\t\tcode: {self.code}, '
              f'\n\t\t\t\t\t\t\t\t\tvalue: {self.value}'
              '\n\t\t\t\t\t\t\t\t}',
              end='')
