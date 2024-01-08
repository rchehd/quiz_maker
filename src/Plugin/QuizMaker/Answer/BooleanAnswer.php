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
final class BooleanAnswer extends QuestionAnswer {

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    return self::NEUTRAL;
  }

}
