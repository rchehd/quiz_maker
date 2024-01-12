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
   * Get score.
   *
   * @return int
   *   The score.
   */
  public function getScore(): int;

  /**
   * Get all answers.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface[]
   *   Array of answers or empty array.
   */
  public function getResponses(): array;

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
   * Get active question that need to be answered.
   *
   * User can answer only several question, and then continue taking quiz from
   * the last question.
   *
   * @return ?\Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getActiveQuestion(): ?QuestionInterface;

  /**
   * Is passed?
   *
   * @return bool
   *   TRUE if passed, otherwise FALSE.
   */
  public function isPassed(): bool;

  /**
   * Set score.
   *
   * @param int $score
   *   The score.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface
   *   The quiz result object.
   */
  public function setScore(int $score): QuizResultInterface;

  /**
   * Set passed value.
   *
   * @param bool $is_passed
   *   The score.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface
   *   The quiz result object.
   */
  public function setPassed(bool $is_passed): QuizResultInterface;

  /**
   * Add question response to quiz result.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The question response.
   */
  public function addResponse(QuestionResponseInterface $response): QuizResultInterface;

  /**
   * Set quiz result state.
   *
   * @param string $state
   *   The state.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface
   *   The quiz result object.
   */
  public function setState(string $state): QuizResultInterface;

  /**
   * Set finished time.
   *
   * @param int $timestamp
   *   The timestamp.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface
   *   The quiz result object.
   */
  public function setFinishedTime(int $timestamp): QuizResultInterface;

}
