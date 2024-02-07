<?php

namespace Drupal\quiz_maker\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizResultInterface;

/**
 * ResponseEvent class.
 */
class ResponseEvent extends Event {

  /**
   * Class constructor.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $quizResult
   *   The quiz result.
   * @param \Drupal\quiz_maker\QuestionResponseInterface $questionResponse
   *   The question user's response.
   */
  public function __construct(
    protected QuizResultInterface $quizResult,
    protected QuestionResponseInterface $questionResponse,
  ) {}

  /**
   * Get question user response.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The user's response.
   */
  public function getUserResponse(): QuestionResponseInterface {
    return $this->questionResponse;
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
