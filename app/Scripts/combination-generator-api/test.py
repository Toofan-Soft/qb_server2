import json
from typing import List


class Question:
    def __init__(self, id, choices):
        self.id = id
        self.choices = choices

    class Choice:
        def __init__(self, id, is_correct):
            self.id = id
            self.is_correct = is_correct

    @classmethod
    def from_json(cls, data):
        # id = data["id"]
        # content = data["content"]
        # choices = [cls.Choice(choice["id"], choice["content"], choice["isCorrect"]) for choice in data["choices"]]
        # return cls(data["id"], choices)
        return cls(data["id"], [cls.Choice(choice["id"], choice["isCorrect"]) for choice in data["choices"]])


class Combination:
    def __init__(self, choices):
        self.choices = choices

    class Choice:
        class Basic:
            def __init__(self, id, is_correct):
                self.id = id
                self.is_correct = is_correct

        class Compound:
            def __init__(self, ids, is_correct):
                self.ids = ids
                self.is_correct = is_correct

        @staticmethod
        def CORRECT_NOTHING():
            return Combination.Choice.Basic(0, True)

        @staticmethod
        def INCORRECT_NOTHING():
            return Combination.Choice.Basic(0, False)

        @staticmethod
        def CORRECT_ALL():
            return Combination.Choice.Basic(float('inf'), True)

        @staticmethod
        def INCORRECT_ALL():
            return Combination.Choice.Basic(float('inf'), False)

        @staticmethod
        def to_compound(choices):
            ids = [choice.id for choice in choices]
            return Combination.Choice.Compound(ids, all(choice.is_correct for choice in choices))

    @staticmethod
    def uncombine(value):
        choices = []
        for item in value.split(","):
            is_correct = False
            if item.endswith("•"):
                item = item[:-1]
                is_correct = True

            if "." in item:
                choices.append(Combination.Choice.Compound(list(map(int, item.split("."))), is_correct))
            else:
                if item == "∞":
                    choices.append(Combination.Choice.Basic(float('inf'), is_correct))
                else:
                    choices.append(Combination.Choice.Basic(int(item), is_correct))

        return Combination(choices)

    def combine(self):
        combined_str = ""
        for choice in self.choices:
            if isinstance(choice, Combination.Choice.Basic):
                combined_str += (str(choice.id) if choice.id != float('inf') else "∞") + (
                    "•" if choice.is_correct else "") + ","
            elif isinstance(choice, Combination.Choice.Compound):
                combined_str += ".".join(str(self.get_index(id)) for id in choice.ids) + (
                    "•" if choice.is_correct else "") + ","
        return combined_str[:-1]

    def get_index(self, id):
        for i, c in enumerate(self.choices):
            if isinstance(c, Combination.Choice.Basic) and c.id == id:
                return i
        return -1


def generate(choices):
    # correct_list = [choice for choice in choices if choice.is_correct]
    # incorrect_list = [choice for choice in choices if not choice.is_correct]

    correct_list = [Combination.Choice.Basic(choice.id, choice.is_correct) for choice in choices if choice.is_correct]
    incorrect_list = [Combination.Choice.Basic(choice.id, choice.is_correct) for choice in choices if not choice.is_correct]

    one_correct_set = get_all_groups(correct_list, 1)
    two_correct_set = get_all_groups(correct_list, 2)
    three_correct_set = get_all_groups(correct_list, 3)

    one_incorrect_set = get_all_groups(incorrect_list, 1)
    two_incorrect_set = get_all_groups(incorrect_list, 2)
    three_incorrect_set = get_all_groups(incorrect_list, 3)

    all_combinations = []

    # region 0 Real Correct
    all_combinations.extend([Combination(it) for it in merge2(three_incorrect_set, [[Combination.Choice.CORRECT_NOTHING()]])])
    all_combinations.extend([Combination(it) for it in merge2(two_incorrect_set, [[Combination.Choice.INCORRECT_ALL(), Combination.Choice.CORRECT_NOTHING()]])])
    # endregion

    # region 1 Real Correct
    all_combinations.extend([Combination(it) for it in merge2(one_correct_set, three_incorrect_set)])
    all_combinations.extend([Combination(it) for it in merge3(one_correct_set, two_incorrect_set, [[Combination.Choice.INCORRECT_ALL()]])])
    all_combinations.extend([Combination(it) for it in merge3(one_correct_set, two_incorrect_set, [[Combination.Choice.INCORRECT_NOTHING()]])])
    all_combinations.extend([Combination(it) for it in merge3(one_correct_set, one_incorrect_set, [[Combination.Choice.INCORRECT_ALL(), Combination.Choice.INCORRECT_NOTHING()]])])
    all_combinations.extend([Combination(it) for it in mix([[choice for choice in combination] for combination in merge2(one_correct_set, two_incorrect_set)], 2)])
    # endregion

    # region 2 Real Correct
    all_combinations.extend([Combination(it) for it in merge2(
        [[Combination.Choice.Basic(choice.id, False) for choice in it] for it in two_correct_set],
        [[Combination.Choice.CORRECT_ALL(), Combination.Choice.INCORRECT_NOTHING()]]
    )])

    all_combinations.extend([Combination(it) for it in merge2([[choice for choice in combination] for combination in mix(two_correct_set, 2)], one_incorrect_set)])
    # endregion

    # region 3 Real Correct
    all_combinations.extend([Combination(it) for it in merge2(
        [[Combination.Choice.Basic(choice.id, False) for choice in it] for it in three_correct_set],
        [[Combination.Choice.CORRECT_ALL()]]
    )])
    # endregion

    combined_list = []
    for index, combination in enumerate(all_combinations):
        combine = combination.combine()
        combined_list.append(combine)

    return combined_list


def mix(list, size):
    final = []

    for item in list:
        is_change = all(choice.is_correct for choice in item)
        for mix in get_all_groups(item, size):
            new_combination = []
            if not is_change:
                new_combination.extend(item)
            else:
                # new_combination.extend([choice.copy(is_correct=False) for choice in item])
                new_combination.extend([Combination.Choice.Basic(choice.id, False) for choice in item])
            new_combination.append(Combination.Choice.to_compound(mix))
            final.append(new_combination)

    return final


def merge2(first, second):
    final = []

    for fir in first:
        for sec in second:
            final.append(fir + sec)

    return final


def merge3(first, second, third):
    final = []

    for fir in first:
        for sec in second:
            for thi in third:
                final.append(fir + sec + thi)

    return final


def get_all_groups(input_list, group_size):
    result = []
    generate_groups(input_list, 0, group_size, [], result)
    return result


def generate_groups(input_list, current_index, remaining_elements, current_group, result):
    if remaining_elements == 0:
        result.append(list(current_group))  # Add a copy of the current_group to the result
        return

    if current_index == len(input_list):
        return  # End recursion if we reach the end of the list

    # Include the current element in the group
    current_group.append(input_list[current_index])
    generate_groups(input_list, current_index + 1, remaining_elements - 1, current_group, result)

    # Exclude the current element from the group
    current_group.pop()
    generate_groups(input_list, current_index + 1, remaining_elements, current_group, result)



# # Example usage:
# choices = [
#     Choice.Basic(1, True),
#     Choice.Basic(2, False),
#     Choice.Basic(3, True)
# ]
#
# compound_choice = Choice.to_compound(choices)
# print(compound_choice.ids)
# print(compound_choice.is_correct)





# Read JSON file and convert to list of Question objects
def load_questions_from_json(file_path):
    questions = []
    with open(file_path, "r") as file:
        data = json.load(file)
        for item in data:
            question = Question.from_json(item)
            questions.append(question)
    return questions


# Example usage:
questions = load_questions_from_json("questions.json")
# for question in questions:
    # print("Question ID:", question.id)
    # # print("Question Content:", question.content)
    # print("Choices:")
    # for choice in question.choices:
    #     print("\tChoice ID:", choice.id)
    #     # print("\tChoice Content:", choice.content)
    #     print("\tIs Correct:", choice.is_correct)
    # print()
    # generate(question.choices)

result = generate(questions[0].choices)

for i, e in enumerate(result):
    print((i+1), ') ', e)

# for item in result:
#     print(item)


# input
# [
#   {
#     "id": 1,
#     "choices": [
#       {
#         "id": 1,
#         "is_correct": true
#       },
#         ...
#     ]
#   },
#   {
#     "id": 2,
#     "choices": [
#       ...
#     ]
#   },
#     ...
# ]

# output
# {
#     "combinations": [
#         "1,2,3,0",
#         "1,2,3,∞",
#         "1,2,1.2,3",
#         ...
#     ]
# }


