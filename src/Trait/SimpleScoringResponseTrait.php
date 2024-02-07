<?php

namespace Drupal\quiz_maker\Trait;

use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\SimpleScoringQuestionInterface;

/**
 * Provides a scoring helper for question answer entity.
 *
 * @internal
 */
trait SimpleScoringResponseTrait {

  /**
   * Implements \Drupal\quiz_maker\SimpleScoringResponseInterface::setScore().
   */
  public function setScore(QuestionInterface $question, bool $value, float $score = NULL, array $response_data = []): QuestionResponseInterface {
    $is_simple_score = $question instanceof SimpleScoringQuestionInterface ? $question->isSimpleScore() : FALSE;
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
      $total_score = round(($total_score / $max_score) * $question->getMaxScore(), 2);
      $result = parent::setScore($question, TRUE, $total_score, $response_data);
    }
    else {
      $result = parent::setScore($question, $value);
    }

    return $result;
  }

}
