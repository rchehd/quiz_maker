<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "boolean_question",
 *   label = @Translation("Boolean question"),
 *   description = @Translation("Boolean question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\Answer\BooleanAnswer",
 *   answer_plugin_id = "boolean_answer",
 * )
 */
class BooleanQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    return [
      '#type' => 'radios',
      '#title' => $this->t('Select an answer'),
      '#options' => [
        'true' => $this->t('True'),
        'false' => $this->t('False'),
      ],
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
