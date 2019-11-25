##### Assignment...
You are working at a crossfit box and need to design a workout-of-the-day program for the participants that night. This is a personalized sequence of exercises, After the warmup (not part of the assignment) the program consists out of 30 elements of 1 minute. The selection of elements is: Jumping jacks, push ups, front squats,back squats, pull ups, rings, short sprints, handstand practice, jumping rope Your participants will sign up with their name and whether they are beginner or not.


##### Rules...
- During the program participants get two breaks. Beginners get four breaks of 1 minute instead of two
- The program shouldn’t begin or end with breaks
- Beginners should do a maximum of 1 handstand practise during the WOD
- Jumping jacks, jumping rope and short sprints are cardio exercises and should not follow after each other
- The gym has limited space for the rings and pullups, a maximum of 2 participants may do either one of these exercises (ring + pull up combined max 2)


##### Conditions...
* The generator will be run automatically, so do not make it interactive.
* The application is built in PHP.
* The assignment should be made without the use of a framework.
* The assignment should be explained.
* Unit/Integrations test are valued, but not mandatory.
* The generator shows the planning of the WOD. This can be done using a HTML-page or using the STDOUT. The output should look something like this
* Since the program is meant to run automatically (either by page or command line invocation) the participant data must be delivered predefined to the generator (no external services). You may pick the names/levels from the example hereunder, you may think of your own


##### Output...
###### Starting the workout with Camille, Michael, Tom (beginner), Tim , Erik, Lars and Mathijs (beginner)
> 00:00 - 01:00 - Camille will do jumping jacks, Michael pushups, Tom will do front squat, Tim will do sprints Erik will do pull ups, Lars will do

> 01:00 - 02:00 - Camille will do front squat, Michael short sprints, Tom will do rings, Tim will do rings, Erik will do sprints

> ....

> 25:00 - 26:00 Camille will take a break, Michael short sprints, Tom will rings, Tim will take a break, Erik will do handstand practise, Lars pullups, Mathijs will take a break

> ....

> 29:00 - 30:00 Camille will do jumping jacks, Michael rings, Tom will do jumping jacks, Tim will do handstand practise, Erik will do front squats, Lars will do back squats, Mathijs will do short sprints


##### Code...
- randomizes member order, so you get a new workout pattern (program) every time
- registers exercises and rules
- randomizes exercises order, so you get a new workout pattern every time
- start workout: in a `do..while` loop try (based on some rules) to add an new exercise for an user
- the result is printed as a json or text
- main logic is in `Workout::start()` + `Workout::assign` and `extend Rule` classes
- as of yet, there are not tests, and not much testing was done 
- run: `./generator.php -f members.txt -o text` 
- run: `./generator.php -f members.txt -o text -vvv` in debug mode :)