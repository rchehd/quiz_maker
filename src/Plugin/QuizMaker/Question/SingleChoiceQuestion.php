<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "single_choice_question",
 *   label = @Translation("Single choice question"),
 *   description = @Translation("Single choice question."),
 * )
 */
class SingleChoiceQuestion extends Question {

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
        'single_choice_answer' => [
          '#type' => 'radios',
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
      'response' => $form_state->getValue('single_choice_answer')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function hasReferencedAnswers(): bool {
    return TRUE;
  }

}
