<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a quiz entity type.
 */
interface QuizInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Add question to quiz.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   */
  public function addQuestion(QuestionInterface $question): void;

  /**
   * Delete question.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   */
  public function deleteQuestion(QuestionInterface $question): void;

  /**
   * Get questions.
   *
   * @return \Drupal\quiz_maker\QuestionInterface[]|bool
   *   Array of Questions or FALSE.
   */
  public function getQuestions(): array|bool;

  /**
   * Get results.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface[]|bool
   *   Array of Quiz Results or FALSE.
   */
  public function getAllResults(): array|bool;

  /**
   * Get user result.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface|null
   *   The quiz result.
   */
  public function getUserResult(AccountInterface $user): ?QuizResultInterface;

  /**
   * Is quiz passed?
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   *
   * @return bool
   *   TRUE if passed, otherwise FALSE.
   */
  public function isPassed(AccountInterface $user): bool;

  /**
   * Require manually evaluation?
   *
   * @return bool
   *   TRUE if required, otherwise FALSE.
   */
  public function requiresManualEvaluation(): bool;

}
