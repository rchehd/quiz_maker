<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "single_question",
 *   label = @Translation("Single question"),
 *   description = @Translation("Single question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswer\SingleQuestionAnswer"
 *   answer_plugin_id = 'single_question_answer'
 * )
 */
final class SingleQuestion extends Question {

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    return [];
  }

}
