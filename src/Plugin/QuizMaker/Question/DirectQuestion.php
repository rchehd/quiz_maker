<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "direct_question",
 *   label = @Translation("Direct question"),
 *   description = @Translation("Direct question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\Answer\DirectAnswer",
 *   answer_plugin_id = "direct_answer",
 * )
 */
class DirectQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    return [
      '#type' => 'textarea',
      '#title' => $this->t('Write an answer'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function submitAnswer(array &$form, FormStateInterface $form_state): mixed {
    $test = self::get('answer_plugin_id');
    return $form_state->getValue('answer');
  }

  public function hasReferencedAnswers(): bool {
    return FALSE;
  }

}
