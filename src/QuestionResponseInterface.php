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

}
