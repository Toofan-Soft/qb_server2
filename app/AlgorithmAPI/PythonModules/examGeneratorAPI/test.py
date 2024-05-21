import random


def test(on_update):
    for i in range(2):
        value = on_update(random.randint(3, 5))
        print(value)


test(lambda x: (
    print("Received value:", x),
    y := x * 2,  # Calculate y as x * 2
    print("Calculated value:", y),  # Print the calculated value of y
    y  # Return y
)[3])  # Access the fourth element of the tuple, which is y