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
   * @return \Drupal\quiz_maker\QuestionInterface[]
   *   Array of Questions or FALSE.
   */
  public function getQuestions(): array;

  /**
   * Get question tags.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   Array of term interface.
   */
  public function getQuestionTags(): array;

  /**
   * Get pass rate.
   *
   * @return ?int
   *   The pass rate.
   */
  public function getPassRate(): ?int;

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

  /**
   * Get user draft results.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   * @param array $conditions
   *   The conditions.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|\Drupal\quiz_maker\QuizResultInterface[]
   *   The array of quiz result entities.
   */
  public function getResults(AccountInterface $user, array $conditions = []): array;

  /**
   * Get quiz max score.
   *
   * @return int
   *   The score.
   */
  public function getMaxScore(): int;

  /**
   * Get quiz attempts count.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   *
   * @return int
   *   The count of completed attempts.
   */
  public function getCompletedAttempts(AccountInterface $user): int;

  /**
   * Check if quiz require to manually assessment.
   *
   * @return bool
   *   TRUE if require, otherwise FALSE.
   */
  public function requireManualAssessment(): bool;

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
   * Check if user has access to take quiz and get a reason if no.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   *
   * @return bool|array
   *   TRUE if allow take quiz, otherwise FALSE or array of reasons.
   */
  public function allowTaking(AccountInterface $user): bool|array;

  /**
   * Allow jump through questions.
   *
   * @return bool
   *   TRUE if allowed, otherwise FALSE.
   */
  public function allowJumping(): bool;

  /**
   * Get a time limit for quiz-taking attempts.
   *
   * @return ?int
   *   The time in seconds or NULL if not set.
   */
  public function getTimeLimit(): ?int;

  /**
   * Do question sequence to be randomized.
   *
   * @return bool
   *   TRUE when have to be, otherwise FALSE.
   */
  public function randomizeQuestionSequence(): bool;

}
