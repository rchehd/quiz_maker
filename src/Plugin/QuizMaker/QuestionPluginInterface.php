<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * The Question Plugin interface.
 */
interface QuestionPluginInterface {

  /**
   * Get plugin entity.
   *
   * @return \Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getEntity(): QuestionInterface;

  /**
   * Get question answer wrapper id.
   *
   * @return string
   *   The unique question id.
   */
  public function getQuestionAnswerWrapperId(): string;

  /**
   * Build Answering form.
   *
   * Example how to build answering form:
   * $form = [
   *   $this->getQuestionAnswerWrapperId() => [
   *     '#type' => 'example',
   *     '#title' => $this->t('Example'),
   *   ]
   * ];
   * Or
   * $form['wrapper'] = [
   *    '#type' => 'container',
   * ]
   *
   * $form['wrapper']['answer_form'] = [
   *    $this->getQuestionAnswerWrapperId() => [
   *      '#type' => 'example',
   *      '#title' => $this->t('Example'),
   *    ]
   *  ];
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface|null $question_response
   *   Question response. Used for set default value for answering form.
   * @param bool $allow_change_response
   *   TRUE if question allow to change response, otherwise FALSE.
   *
   * @return array
   *   Form array.
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array;

  /**
   * Question form validation.
   *
   * @param array $form
   *   The form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void;

  /**
   * Get default answers.
   *
   * @return array
   *   Return array if arrays with default answer data, or empty array if there
   *   aren't default answers.
   */
  public function getDefaultAnswersData(): array;

  /**
   * Check if response is correct.
   *
   * @param array $answers_ids
   *   The response answers ids.
   *
   * @return bool
   *   TRUE if correct, otherwise FALSE.
   */
  public function isResponseCorrect(array $answers_ids): bool;

  /**
   * Get question response view on result view.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   * @param int $mark_mode
   *   The mark mode:
   *      - 0: 'correct/incorrect',
   *      - 1: 'chosen/not-chosen'.
   *
   * @return array
   *   The render array.
   */
  public function getResponseView(QuestionResponseInterface $response, int $mark_mode = 0): array;

  /**
   * Handles form submission for the question type.
   *
   * @param array $form
   *   The form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The question answer.
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array;

}
