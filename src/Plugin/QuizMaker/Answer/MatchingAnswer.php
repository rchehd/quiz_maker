<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "matching_answer",
 *   label = @Translation("Matching answer"),
 *   description = @Translation("Matching answer.")
 * )
 */
class MatchingAnswer extends QuestionAnswer {

  /**
   * Get matching question.
   *
   * @return string
   *   The matching question.
   */
  public function getMatchingQuestion(): string {
    return $this->get('field_matching_question')->value;
  }

  /**
   * Get matching answer.
   *
   * @return string
   *   The matching answer.
   */
  public function getMatchingAnswer(): string {
    return $this->get('field_matching_answer')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    return self::NEUTRAL;
  }

}
