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
  public function getAnsweringForm(QuestionResponseInterface $questionResponse = NULL, bool $allow_change_response = TRUE): array {
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
          '#default_value' => $questionResponse?->getResponseData(),
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
    if (!$form_state->getValue('single_choice_answer')) {
      $form_state->setErrorByName('single_choice_answer', $this->t('Choose the answer, please.'));
    }
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
  public function isResponseCorrect(array $response_data): bool {
    $correct_answers = $this->getCorrectAnswers();
    $correct_answers_ids = array_map(function ($correct_answer) {
      return $correct_answer->id();
    }, $correct_answers);
    $answers_ids = $response_data['response'];
    return reset($correct_answers_ids) === $answers_ids;
  }

}
