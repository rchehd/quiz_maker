<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

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
  public function getAnsweringForm(): array {
    return [
      'direct_answer' => [
        '#type' => 'textarea',
        '#title' => $this->t('Write an answer'),
      ]
    ];
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
  public function hasReferencedAnswers(): bool {
    return FALSE;
  }

}
