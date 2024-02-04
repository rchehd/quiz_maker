<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question entity type.
 */
interface QuestionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get Answering form.
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

  /**
   * Get question text.
   *
   * @return ?string
   *   The question text.
   */
  public function getQuestion(): ?string;

  /**
   * Get question's answers if it has.
   *
   * @return ?array
   *   Array of answers if it has, otherwise NULL.
   */
  public function getAnswers(): ?array;

  /**
   * Add answer to question.
   *
   * @param \Drupal\quiz_maker\QuestionAnswerInterface $answer
   *   The answer.
   */
  public function addAnswer(QuestionAnswerInterface $answer):void;

  /**
   * Get question correct answers.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface[]
   *   The array of answers.
   */
  public function getCorrectAnswers(): array;

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
   * Get max score.
   *
   * @return int
   *   The score.
   */
  public function getMaxScore(): int;

  /**
   * Get default answers.
   *
   * @return array
   *   Return array if arrays with default answer data, or empty array if there
   *   aren't default answers.
   */
  public function getDefaultAnswersData(): array;

  /**
   * Get response type.
   *
   * @return ?string
   *   The response type.
   */
  public function getResponseType(): ?string;

  /**
   * Get answer type.
   *
   * @return ?string
   *   The answer type.
   */
  public function getAnswerType(): ?string;

  /**
   * Is question enabled?
   *
   * @return bool
   *   TRUE if enabled, otherwise FALSE.
   */
  public function isEnabled(): bool;

}
