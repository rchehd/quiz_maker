<?php

namespace Drupal\quiz_maker;

/**
 * Defines a common interface for question that have an simple scoring option.
 */
interface SimpleScoringQuestionInterface {

  /**
   * TRUE if question score should calculate by simple way.
   *
   * Explanation: Calculate the answer score considering an exact match or a
   * partial match (every matching have wight in answer).
   *
   * @return bool
   *   TRUE if simple scoring, otherwise FALSE.
   */
  public function isSimpleScore(): bool;

}
