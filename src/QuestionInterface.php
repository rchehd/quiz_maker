<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question entity type.
 */
interface QuestionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get Answering form.
   *
   * @return array
   *   Form array.
   */
  public function getAnsweringForm(): array;

}
