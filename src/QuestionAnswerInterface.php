<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Form\FormStateInterface;
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
   * Return answer text if it exists.
   *
   * @return string|null
   *   The answer text or null.
   */
  public function getAnswer(): ?string;

}
