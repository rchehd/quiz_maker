<?php

namespace Drupal\quiz_maker\Trait;

/**
 * Provides a scoring helper for question entity.
 *
 * @internal
 */
trait SimpleScoringQuestionTrait {

  /**
   * TRUE if question score should calculate by simple way.
   *
   * Explanation: Calculate the answer score considering an exact match or a
   * partial match (every matching have wight in answer).
   *
   * @return bool
   *   TRUE if simple scoring, otherwise FALSE.
   */
  public function isSimpleScore(): bool {
    if ($this->hasField('field_simple_scoring')) {
      return (bool) $this->get('field_simple_scoring')->getString();
    }
    return FALSE;
  }

}