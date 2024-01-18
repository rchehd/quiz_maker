<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\Trait\SimpleScoringQuestionTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "multiple_choice_question",
 *   label = @Translation("Multiple question"),
 *   description = @Translation("Multiple question."),
 *   answer_bundle = "multiple_choice_answer",
 *   response_bundle = "multiple_choice_response",
 * )
 */
class MultipleChoiceQuestion extends Question {

  use StringTranslationTrait;
  use SimpleScoringQuestionTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    if ($answers = $this->getAnswers()) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      return [
        'multiple_choice_answer' => [
          '#type' => 'checkboxes',
          '#title' => $this->t('Select an answer'),
          '#options' => $options,
          '#default_value' => $question_response?->getResponses() ?? [],
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
    $responses = $form_state->getValue('multiple_choice_answer');
    $responses = array_filter($responses, function ($response) {
      return $response != 0;
    });
    return array_values($responses);
  }

}
