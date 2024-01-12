<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
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
class BooleanAnswer extends QuestionAnswer {

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $response_data = $response->getResponseData();
    if ($this->id() === $response_data && $this->isCorrect()) {
      return self::CORRECT;
    }
    elseif ($this->id() === $response_data && !$this->isCorrect()) {
      return self::IN_CORRECT;
    }
    return self::NEUTRAL;
  }

}
