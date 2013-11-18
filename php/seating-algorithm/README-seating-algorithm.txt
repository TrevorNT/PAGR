Restaurant Seating Algorithm
Author: Jacob Zadnik

#####################
## Version History ##
##################### 

    0.1.0:
        - Start of project
        - Wrote Table, TableGroup, and Restaurant classes
    0.2.0:
         - Algorithm is able to handle seating first round of groups at open tables

    0.3.0:
        - Added priority to single tables over pushing tables together

    0.4.0:
        - Added Party class and its extensions: the Reservation and WalkIn classes.
        - Modified Restaurant::FindOpenGroup() to take this new Group opject as a parameter
    
    0.4.1:
        - Fixed bug with time output if the group could not be seated
          

##############################
## Version numbering scheme ##
##############################

   A loosely followed version of 'Semantic Versioning 2.0.0' 
   will be used. Information about Semantic Versioning can be
   found at semver.org
