<?php

namespace Drupal\quiz_maker\Trait;

use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Provides a scoring helper for question answer entity.
 *
 * @internal
 */
trait SimpleScoringResponseTrait {

  /**
   * {@inheritDoc}
   */
  public function setScore(QuestionInterface $question, bool $value, float $score = NULL, array $response_data = []): QuestionResponseInterface {
    $is_simple_score = $question->isSimpleScore();
    // When simple scoring disabled, we need to calculate score of every
    // right matching.
    if (!$is_simple_score && $response_data) {
      $answers = $question->getCorrectAnswers();
      $answer_ids = array_map(function ($answer) {
        return (int) $answer->id();
      }, $answers);
      $total_score = 0;
      $max_score = 0;
      // Add score for avery guessed matching.
      for ($i = 0; $i < count($answers); $i++) {
        $max_score += $answers[$i]->getScore();
        if (isset($response_data[$i]) && $this->isResponseCorrect($response_data[$i], $answers[$i]->id(), $response_data, $answer_ids)) {
          $total_score += $answers[$i]->getScore();
        }
      }
      // Calculate the fraction from the question max score.
      $total_score = round(($total_score/$max_score) * $question->getMaxScore(), 2);
      $result = parent::setScore($question, TRUE, $total_score, $response_data);
    }
    else {
      $result = parent::setScore($question, $value);
    }

    return $result;
  }

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
  abstract protected function isResponseCorrect(int $response_id, int $answer_id, array $response_ids, array $answer_ids): bool;

}