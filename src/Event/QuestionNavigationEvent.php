<?php

namespace Drupal\quiz_maker\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;

/**
 * QuestionNavigationEvent class.
 */
class QuestionNavigationEvent extends Event {

  /**
   * Class constructor.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param \Drupal\quiz_maker\QuestionInterface $currentQuestion
   *   The current question.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   * @param int $nextQuestionNumber
   *   The next question number.
   */
  public function __construct(
    protected QuizInterface $quiz,
    protected QuestionInterface $currentQuestion,
    protected AccountInterface $user,
    public int &$nextQuestionNumber,
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
   * Get current question.
   *
   * @return \Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getCurrentQuestion(): QuestionInterface {
    return $this->currentQuestion;
  }

  /**
   * Get previous question.
   *
   * @return int
   *   The question number.
   */
  public function getNextQuestionNumber(): int {
    return $this->nextQuestionNumber;
  }

  /**
   * Get user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user.
   */
  public function getUser(): AccountInterface {
    return $this->user;
  }

}
