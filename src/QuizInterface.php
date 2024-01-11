<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\Entity\QuizResultType;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a quiz entity type.
 */
interface QuizInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get questions.
   *
   * @return \Drupal\quiz_maker\QuestionInterface[]|bool
   *   Array of Questions or FALSE.
   */
  public function getQuestions(): array|bool;

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

  /**
   * Check if quiz allows to skip questions.
   *
   * @return bool
   *   TRUE if allowed, otherwise FALSE.
   */
  public function allowSkipping(): bool;

  /**
   * Check if quiz allows to backward navigation.
   *
   * @return bool
   *   TRUE if allowed, otherwise FALSE.
   */
  public function allowBackwardNavigation(): bool;

  /**
   * Check if quiz allows to change answer.
   *
   * @return bool
   *   TRUE if allowed, otherwise FALSE.
   */
  public function allowChangeAnswer(): bool;

  /**
   * Get maximum possible score od quiz.
   *
   * @return int
   *   The score.
   */
  public function getMaxScore(): int;

  /**
   * Get pass rate.
   *
   * @return int
   *   The pass rate.
   */
  public function getPassRate(): int;

  /**
   * Get quiz allowed attempts.
   *
   * @return ?int
   *   The count of attempts or null if it not set.
   */
  public function getAllowedAttempts(): ?int;

  /**
   * Get access period.
   *
   * @return array
   *   Array with two keys: "start_date" and "and_date".
   */
  public function getAccessPeriod(): array;

  /**
   * Get result type.
   *
   * @return string
   *   The result type id.
   */
  public function getResultType(): string;

}
