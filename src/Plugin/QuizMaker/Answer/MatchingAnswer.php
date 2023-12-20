<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "matching_answer",
 *   label = @Translation("Matching answer"),
 *   description = @Translation("Matching answer.")
 * )
 */
final class MatchingAnswer extends QuestionAnswer {

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

  /**
   * {@inheritDoc}
   */
  public function buildAnswerForm(): array {

    $form['answer_text'] = [
      '#type' => 'text_format',
      '#title' => 'Answer',
      '#format' => 'full_html',
      '#allowed_formats' => ['full_html'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitAnswerForm(array &$form, FormStateInterface $form_state): void {
    $this->set('data', ['answer' => $form_state->get('c')]);
  }

}
