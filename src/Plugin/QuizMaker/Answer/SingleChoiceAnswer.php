<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswerPluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "single_choice_answer",
 *   label = @Translation("Single choice answer"),
 *   description = @Translation("Single choice answer.")
 * )
 */
class SingleChoiceAnswer extends QuestionAnswerPluginBase {

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
