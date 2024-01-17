<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionResponse(
 *    id = "matching_choice_response",
 *    label = @Translation("Matching choice response"),
 *    description = @Translation("Matching choice response.")
 * )
 */
class MatchingChoiceResponse extends QuestionResponse {

  /**
   * {@inheritDoc}
   */
  public function setScore(QuestionInterface $question, bool $value, float $score = NULL, array $response_data = []): QuestionResponseInterface {
    $is_simple_score = $question->isSimpleScore();
    // When simple scoring disabled, we need to calculate score of every
    // right matching.
    if (!$is_simple_score && $response_data) {
      $answers = $question->getAnswers();
      $total_score = 0;
      $max_score = 0;
      // Add score for avery guessed matching.
      for ($i = 0; $i < count($answers); $i++) {
        $max_score += $answers[$i]->getScore();
        if ($response_data[$i] === (int) $answers[$i]->id()) {
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

}
