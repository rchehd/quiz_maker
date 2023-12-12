<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question answer entity type.
 */
interface QuestionAnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Answer form.
   *
   * @return array
   *   Form.
   */
  public function getAnswerForm(): array;

  /**
   * Answer data.
   *
   * @return array
   *   The data.
   */
  public function getData(): array;

}
