<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Entity\QuestionResponse;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionResponse(
 *    id = "boolean_response",
 *    label = @Translation("Boolean response"),
 *    description = @Translation("Boolean response.")
 * )
 */
class BooleanResponse extends QuestionResponse {

  /**
   * {@inheritDoc}
   */
  public function getResponseData(): mixed {
    $data = $this->get('response')->response;
    return $data;
  }

}
