## INTRODUCTION

The Quiz Maker module is a module to create different kind of quizzes.

The primary use case for this module is:

- You need to create quiz.
- You need to create testing on time.
- You need to create testing with evaluation.
- Etc.

## CONFIGURATION
- Configure different type of quizzes with needed params.
- Configure different type of quiz results with needed params.
- Configure different type of questions, by default module provide next type of question:
  - Boolean question
  - Direct question (Warning: be carefully with translation this question type, because user answer can't be translated)
  - Single choice question
  - Multiple choice question
  - Matching question
- Configure different type of answers
- Configure different type of responses

## FEATURES
- You can create you own question types as plugin, for this you need to create:
  - Question plugin and add Question type with the same id with UI or configs.
  - Question Answer plugin and add Question Answer type with the same id with UI or configs. 
  It is Answers that can be added to question.
  - Question Response plugin and add Question Response type with the same id with UI or configs. 
  It is user Response that will be created after submit Question answering form
- Quiz and Questions support revisionable and translatable interfaces.
- Quiz have many setting that will help you to use it in different situation.


## MAINTAINERS

Current maintainers for Drupal 10:

- Roman Chekhaniuk (r_cheh) - https://www.drupal.org/u/r_cheh

