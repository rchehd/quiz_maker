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
  public function getAnsweringForm(QuestionResponseInterface $questionResponse = NULL, bool $allow_change_response = TRUE): array {
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
          '#default_value' => $questionResponse?->getResponseData() ?? [],
          '#disabled' => !$allow_change_response
        ]
      ];
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->getValue('multiple_choice_answer')) {
      $form_state->setErrorByName('multiple_choice_answer', $this->t('Choose the answer, please.'));
    }
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
  public function isResponseCorrect(array $response_data): bool {
    $correct_answers = $this->getCorrectAnswers();
    $correct_answers_ids = array_map(function($correct_answer) {
      return $correct_answer->id();
    }, $correct_answers);
    $answers_ids = array_values($response_data['response']);
    $answers_ids = array_filter($answers_ids, function($answers_id) {
      return is_string($answers_id);
    });
    return $correct_answers_ids === $answers_ids;
  }

  /**
   * {@inheritDoc}
   */
  public function hasReferencedAnswers(): bool {
    return TRUE;
  }

}
