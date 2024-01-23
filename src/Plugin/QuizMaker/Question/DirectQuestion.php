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
 *   answer_bundle = "direct_answer",
 *   response_bundle = "direct_response",
 * )
 */
class DirectQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    $default_answer = $question_response?->getResponses();
    if ($default_answer) {
      /** @var \Drupal\quiz_maker\QuestionAnswerInterface $answer */
      $default_value = reset($default_answer);
    }

    return [
      'direct_answer' => [
        '#type' => 'textarea',
        '#title' => $this->t('Write an answer'),
        '#default_value' => $default_value ?? NULL,
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
      $form_state->getValue('direct_answer')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $answers_ids): bool {
    $correct_answers = $this->getCorrectAnswers();
    $correct_answer = reset($correct_answers);
    $response = reset($answers_ids);
    $result = similar_text($correct_answer->getAnswer(), $response, $perc);
    return $perc >= $this->getSimilarity();
  }

  /**
   * Get answer similarity.
   *
   * @return float
   *   The similarity.
   */
  public function getSimilarity(): float {
    return $this->get('field_similarity')->value;
  }

}
