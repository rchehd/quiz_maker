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
  public function getAnsweringForm(QuestionResponseInterface $questionResponse = NULL, bool $allow_change_response = TRUE): array {
    $answers = $this->get('field_answers')->referencedEntities();
    if ($answers) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      return [
        'boolean_answer' => [
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
    if (!$form_state->getValue('boolean_answer')) {
      $form_state->setErrorByName('boolean_answer', $this->t('Choose the answer, please.'));
    }
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
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $response_data): bool {
    $correct_answers = $this->getCorrectAnswers();
    $correct_answers_ids = array_map(function($correct_answer) {
      return $correct_answer->id();
    }, $correct_answers);
    $answers_ids = $response_data['response'];
    return reset($correct_answers_ids) === $answers_ids;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultAnswersData(): array {
    return [
      [
        'label' => $this->t('True'),
        'answer' => $this->t('True'),
        'is_correct' => $this->isBooleanState(TRUE),
      ],
      [
        'label' => $this->t('False'),
        'answer' => $this->t('False'),
        'is_correct' => $this->isBooleanState(FALSE),
      ]
    ];
  }

  /**
   * Get answer state.
   *
   * @param bool $state
   *   The state: TRUE or FALSE.
   *
   * @return bool
   *   TRUE if it is current state, otherwise FALSE.
   */
  private function isBooleanState(bool $state): bool {
    return $state === (bool) $this->get('field_boolean_state')->getString();
  }

}
