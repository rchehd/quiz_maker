<?php

namespace Drupal\quiz_maker\Trait;

/**
 * Provides a scoring helper for question entity.
 *
 * @internal
 */
trait SimpleScoringQuestionTrait {

  /**
   * Implements \Drupal\quiz_maker\SimpleScoringQuestionInterface::isSimpleScore().
   */
  public function isSimpleScore(): bool {
    if ($this->hasField('field_simple_scoring')) {
      return (bool) $this->get('field_simple_scoring')->getString();
    }
    return FALSE;
  }

}
