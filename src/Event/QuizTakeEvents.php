<?php

namespace Drupal\quiz_maker\Event;

/**
 * Quiz events class.
 */
final class QuizTakeEvents {

  /**
   * Name of event when user start quiz.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizTakeEvent
   */
  const QUIZ_START = 'quiz.start';

  /**
   * Name of event when user update quiz (answer a question).
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizTakeEvent
   */
  const QUIZ_UPDATE = 'quiz.update';

  /**
   * Name of event when user finish quiz.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizTakeEvent
   */
  const QUIZ_FINISH = 'quiz.finish';

  /**
   * Name of event when user goes to next question.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuestionNavigationEvent
   */
  const NEXT_QUESTION = 'quiz_take.next_question';

  /**
   * Name of event when user goes to previous question.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuestionNavigationEvent
   */
  const PREVIOUS_QUESTION = 'quiz_take.previous_question';

  /**
   * Name of event when user goes to question using navigation bar.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuestionNavigationEvent
   */
  const NAVIGATE_TO_QUESTION = 'quiz_take.navigate_to_question';

}
