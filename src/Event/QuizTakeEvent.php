<?php

namespace Drupal\quiz_maker\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;

/**
 * QuizEvent class.
 */
class QuizTakeEvent extends Event {

  /**
   * Class constructor.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param \Drupal\quiz_maker\QuizResultInterface $quizResult
   *   The quiz result.
   */
  public function __construct(
    protected QuizInterface $quiz,
    protected QuizResultInterface $quizResult
  ) {}

  /**
   * Get quiz.
   *
   * @return \Drupal\quiz_maker\QuizInterface
   *   The quiz.
   */
  public function getQuiz(): QuizInterface {
    return $this->quiz;
  }

  /**
   * Get quiz result.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface
   *   The quiz result.
   */
  public function getQuizResult(): QuizResultInterface {
    return $this->quizResult;
  }

}
