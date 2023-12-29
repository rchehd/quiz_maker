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
   * @return array
   *   Form array.
   */
  public function getAnsweringForm(): array;

  /**
   * Handles form submission for the question type.
   *
   * @param array $form
   *   The form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   The question answer.
   */
  public function submitAnswer(array &$form, FormStateInterface $form_state): mixed;

  /**
   * Get question text.
   *
   * @return ?string
   *   The question text.
   */
  public function getQuestion(): ?string;

  /**
   * Has referenced answers.
   *
   * Check if question has referenced answers or there should be only hardcode
   * answers build by answering form.
   *
   * @return bool
   *   TRUE when has, otherwise FALSE.
   */
  public function hasReferencedAnswers(): bool;

  /**
   * Get question's answers if it has.
   *
   * @return array|bool
   *   Array of answers if it has, otherwise FALSE.
   */
  public function getAnswers(): array|bool;

}
