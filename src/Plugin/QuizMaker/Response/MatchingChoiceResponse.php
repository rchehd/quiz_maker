<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Plugin\QuizMaker\QuestionResponsePluginBase;
use Drupal\quiz_maker\SimpleScoringResponseInterface;
use Drupal\quiz_maker\Trait\SimpleScoringResponseTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionResponse(
 *    id = "matching_choice_response",
 *    label = @Translation("Matching choice response"),
 *    description = @Translation("Matching choice response.")
 * )
 */
class MatchingChoiceResponse extends QuestionResponsePluginBase implements SimpleScoringResponseInterface {

  use SimpleScoringResponseTrait;

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(int $response_id, int $answer_id, array $response_ids, array $answer_ids): bool {
    return $response_id === $answer_id;
  }

}
