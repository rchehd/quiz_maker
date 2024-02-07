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
    if ($this->hasField('field_score')) {
      return $this->get('field_score')->value;
    }
    return NULL;
  }

}
