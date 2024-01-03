<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a quiz result entity type.
 */
interface QuizResultInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get quiz.
   *
   * @return \Drupal\quiz_maker\QuizInterface
   *   The quiz.
   */
  public function getQuiz(): QuizInterface;

  /**
   * Get user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user.
   */
  public function getUser(): AccountInterface;

  /**
   * Get result status.
   *
   * @return string
   *   The status.
   */
  public function getStatus(): string;

  /**
   * Get pass rate (can be without set pass rate).
   *
   * @return int|null
   *   The score.
   */
  public function getPassRate(): ?int;

  /**
   * Get score.
   *
   * @return int
   *   The score.
   */
  public function getScore(): int;

  /**
   * Is passed?
   *
   * @return bool
   *   TRUE if passed, otherwise FALSE.
   */
  public function isPassed(): bool;

  /**
   * Set answer.
   *
   * @param \Drupal\quiz_maker\QuestionAnswerInterface $answer
   *   The answer.
   */
  public function setAnswer(QuestionAnswerInterface $answer): void;

  /**
   * Get all answers.
   *
   * @return ?\Drupal\quiz_maker\QuestionResponseInterface[]
   *   Array of answers of FALSE.
   */
  public function getResponses(): ?array;

  /**
   * Get question response by question.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   *
   * @return ?\Drupal\quiz_maker\QuestionResponseInterface
   *   The answer or FALSE.
   */
  public function getResponse(QuestionInterface $question): ?QuestionResponseInterface;

  /**
   * Add question response to quiz result.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The question response.
   */
  public function addResponse(QuestionResponseInterface $response): void;

  /**
   * Get active question that need to be answered.
   *
   * User can answer only several question, and then continue taking quiz from
   * the last question.
   *
   * @return ?\Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getActiveQuestion(): ?QuestionInterface;

}
