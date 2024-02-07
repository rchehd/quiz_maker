<?php

namespace Drupal\quiz_maker;

/**
 * Defines a common interface for question answer that have an simple scoring option.
 */
interface SimpleScoringAnswerInterface {

  /**
   * Get answer score.
   *
   * @return ?int
   *   The score.
   */
  public function getScore(): ?int;

}
