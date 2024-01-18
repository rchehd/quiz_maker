<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\Trait\SimpleScoringResponseTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionResponse(
 *    id = "multiple_choice_response",
 *    label = @Translation("Multiple choice response"),
 *    description = @Translation("Multiple choice response.")
 * )
 */
class MultipleChoiceResponse extends QuestionResponse {

  use SimpleScoringResponseTrait;

  /**
   * {@inheritDoc}
   */
  protected function isResponseCorrect(int $response_id, int $answer_id, array $response_ids, array $answer_ids): bool {
    return in_array($response_id, $answer_ids);
  }

}
