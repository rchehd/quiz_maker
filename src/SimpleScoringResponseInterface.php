<?php

namespace Drupal\quiz_maker;

/**
 * Defines a common interface for question response that have an simple scoring option.
 */
interface SimpleScoringResponseInterface {

  /**
   * TRUE if response is correct.
   *
   * @param int $response_id
   *   The current response id.
   * @param int $answer_id
   *   The current answer id.
   * @param array $response_ids
   *   The array if response ids.
   * @param array $answer_ids
   *   The array if answer ids.
   *
   * @return bool
   *   TRUE if response is correct, otherwise FALSE.
   */
  public function isResponseCorrect(int $response_id, int $answer_id, array $response_ids, array $answer_ids): bool;

}
