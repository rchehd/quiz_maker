<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\quiz_maker\Entity\QuestionAnswer;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "single_question_answer",
 *   label = @Translation("Single question answer"),
 *   description = @Translation("Single question answer.")
 * )
 */
final class SingleQuestionAnswer extends QuestionAnswer {

  /**
   * {@inheritDoc}
   */
  public function getAnswerForm(): array {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getData(): array {
    return [];
  }

}
