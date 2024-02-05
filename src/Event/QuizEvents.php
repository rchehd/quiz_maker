<?php

namespace Drupal\quiz_maker\Event;

/**
 * Quiz events class.
 */
final class QuizEvents {

  /**
   * Name of event when user start quiz.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizEvent
   */
  const QUIZ_START = 'quiz.start';

  /**
   * Name of event when user update quiz (answer a question).
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizEvent
   */
  const QUIZ_UPDATE = 'quiz.update';

  /**
   * Name of event when user finish quiz.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\QuizEvent
   */
  const QUIZ_FINISH = 'quiz.finish';

}
