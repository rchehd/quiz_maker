<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "boolean_question",
 *   label = @Translation("Boolean question"),
 *   description = @Translation("Boolean question."),
 * )
 */
class BooleanQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    return [
      'boolean_answer' => [
        '#type' => 'radios',
        '#title' => $this->t('Select an answer'),
        '#options' => [
          'true' => $this->t('True'),
          'false' => $this->t('False'),
        ],
      ]
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    return [
      'response' => $form_state->getValue('boolean_answer')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function hasReferencedAnswers(): bool {
    return FALSE;
  }

}
