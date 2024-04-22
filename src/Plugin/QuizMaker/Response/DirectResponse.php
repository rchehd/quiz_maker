<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Response;

use Drupal\quiz_maker\Plugin\QuizMaker\QuestionResponsePluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionResponse(
 *    id = "direct_response",
 *    label = @Translation("Direct response"),
 *    description = @Translation("Direct response.")
 * )
 */
class DirectResponse extends QuestionResponsePluginBase {

  /**
   * {@inheritDoc}
   */
  public function setResponseData(array $data): QuestionResponseInterface {
    $this->entity->set('field_user_response', reset($data));
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponses(): array {
    $responses = $this->getUserResponse();
    return $responses ? [$responses] : [];
  }

  /**
   * Get user response.
   *
   * @return string|null
   *   The response.
   */
  public function getUserResponse(): ?string {
    return $this->entity->get('field_user_response')->value;
  }

}
