<?php

namespace Drupal\quiz_maker\Trait;

/**
 * Provides a scoring helper for question answer entity.
 *
 * @internal
 */
trait SimpleScoringAnswerTrait {

  /**
   * Implements \Drupal\quiz_maker\SimpleScoringAnswerInterface::getScore().
   */
  public function getScore(): ?int {
    if ($this->entity->hasField('field_score')) {
      return $this->entity->get('field_score')->value;
    }
    return NULL;
  }

}
