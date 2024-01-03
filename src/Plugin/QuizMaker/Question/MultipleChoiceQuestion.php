<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "multiple_choice_question",
 *   label = @Translation("Multiple question"),
 *   description = @Translation("Multiple question."),
 * )
 */
class MultipleChoiceQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    $answers = $this->get('field_answers')->referencedEntities();
    if ($answers) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      return [
        'multiple_choice_answer' => [
          '#type' => 'checkboxes',
          '#title' => $this->t('Select an answer'),
          '#options' => $options,
        ]
      ];
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    return [
      'response' => $form_state->getValue('multiple_choice_answer')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function hasReferencedAnswers(): bool {
    return TRUE;
  }

}
