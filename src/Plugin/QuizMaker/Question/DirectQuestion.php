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
      /** @var \Drupal\quiz_maker\QuestionAnswerInterface $default_value */
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
    $languages = \Drupal::languageManager()->getLanguages();
    $response = reset($answers_ids);
    $results = [];
    // We need to check similarity with all translations of answer, because correct
    // answers can have a translation, but user response text doesn't, and user can
    // view every quiz result translation, but for each translation it will have the same text.
    // User can add several answers and response will compare with all of them.
    foreach ($correct_answers as $correct_answer) {
      foreach ($languages as $language) {
        if ($correct_answer->hasTranslation($language->getId())) {
          $correct_answer_translation = $correct_answer->getTranslation($language->getId());
        }
        else {
          $correct_answer_translation = $correct_answer;
        }
        /** @var \Drupal\quiz_maker\QuestionAnswerInterface $correct_answer_translation */
        $res = similar_text(strip_tags($correct_answer_translation->getAnswer()), $response, $perc);
        $results[] = $perc;
      }
    }

    $result = max($results);
    return $result >= $this->getSimilarity();
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

  /**
   * {@inheritDoc}
   */
  public function getResponseView(QuestionResponseInterface $response, int $mark_mode = 0): array {
    $result = parent::getResponseView($response, $mark_mode);
    // In direct question we need to show answer only one time, because direct
    // answers has only one user answer.
    return reset($result);
  }

}
