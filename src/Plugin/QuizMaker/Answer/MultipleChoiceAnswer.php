<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "multiple_choice_answer",
 *   label = @Translation("Multiple choice answer"),
 *   description = @Translation("Multiple choice answer.")
 * )
 */
class MultipleChoiceAnswer extends QuestionAnswer {

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $response_data = $response->getResponseData();
    if (in_array($this->id(), $response_data) && $this->isCorrect()) {
      return self::CORRECT;
    }
    elseif (in_array($this->id(), $response_data) && !$this->isCorrect()) {
      return self::IN_CORRECT;
    }

    return self::NEUTRAL;
  }

}
