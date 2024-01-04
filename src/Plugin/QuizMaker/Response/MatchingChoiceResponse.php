<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Entity\QuestionResponse;

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
  public function getResponseData(): mixed {
    $data = $this->get('response');
    return $data;
  }

}
