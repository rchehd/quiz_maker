<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question answer entity type.
 */
interface QuestionAnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Check is answer is correct.
   *
   * @return bool
   *   TRUE id correct, otherwise FALSE.
   */
  public function isCorrect(): bool;

  /**
   * Set "is correct" value.
   *
   * @param bool $value
   *   The value.
   */
  public function setCorrect(bool $value): void;

  /**
   * Return answer text if it exists.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface|null $response
   *   The answer response.
   *
   * @return string|null
   *   The answer text or null.
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string;

  /**
   * Get response status.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   *
   * @return string
   *   The answer status:
   *     Correct - response is correct,
   *     In-correct - response isn't correct.
   *     Neutral - user didn't choose this answer.
   */
  public function getResponseStatus(QuestionResponseInterface $response): string;

}
