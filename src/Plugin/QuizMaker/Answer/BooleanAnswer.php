<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswerPluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "boolean_answer",
 *   label = @Translation("Boolean answer"),
 *   description = @Translation("Boolean answer.")
 * )
 */
class BooleanAnswer extends QuestionAnswerPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $responses = $response->getResponses();
    if (in_array($this->entity->id(), $responses) && $this->isCorrect()) {
      return QuestionAnswer::CORRECT;
    }
    elseif (in_array($this->entity->id(), $responses) && !$this->isCorrect()) {
      return QuestionAnswer::IN_CORRECT;
    }

    return QuestionAnswer::NEUTRAL;
  }

}
