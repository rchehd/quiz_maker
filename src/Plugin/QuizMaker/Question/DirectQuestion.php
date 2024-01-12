<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "direct_question",
 *   label = @Translation("Direct question"),
 *   description = @Translation("Direct question."),
 * )
 */
class DirectQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $questionResponse = NULL, bool $allow_change_response = TRUE): array {
    return [
      'direct_answer' => [
        '#type' => 'textarea',
        '#title' => $this->t('Write an answer'),
        '#disabled' => !$allow_change_response
      ]
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->getValue('direct_answer')) {
      $form_state->setErrorByName('direct_answer', $this->t('Choose the answer, please.'));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    return [
      'response' => $form_state->getValue('direct_answer')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $response_data): bool {
    return FALSE;
  }

}
