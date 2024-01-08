<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question response entity type.
 */
interface QuestionResponseInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get question.
   *
   * @return \Drupal\quiz_maker\QuestionInterface|null
   *   The question.
   */
  public function getQuestion(): ?QuestionInterface;

  /**
   * Get quiz.
   *
   * @return \Drupal\quiz_maker\QuizInterface|null
   *   The quiz.
   */
  public function getQuiz(): ?QuizInterface;

  /**
   * TRUE if response was correct.
   *
   * @return bool
   *   TRUE if correct, otherwise FALSE.
   */
  public function isCorrect(): bool;

  /**
   * Get response score.
   *
   * @return int
   *   The score.
   */
  public function getScore(): int;

  /**
   * Set response data.
   *
   * @param array $data
   *   The data.
   */
  public function setResponseData(array $data): void;

  /**
   * Get response data
   *
   * @return mixed
   *   The response data.
   */
  public function getResponseData(): mixed;

  /**
   * Set is_correct value.
   *
   * @param bool $value
   *   The value.
   */
  public function setCorrect(bool $value): void;

  /**
   * Set quiz reference.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   */
  public function setQuiz(QuizInterface $quiz): void;

  /**
   * Set question reference.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   */
  public function setQuestion(QuestionInterface $question): void;

  /**
   * Calculate and set score.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   * @param bool $value
   *   TRUE id response is correct, otherwise FALSE.
   */
  public function setScore(QuestionInterface $question, bool $value): void;

}
