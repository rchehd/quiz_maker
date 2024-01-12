<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
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
class SingleChoiceAnswer extends QuestionAnswer {

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
