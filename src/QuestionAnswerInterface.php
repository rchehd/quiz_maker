<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question answer entity type.
 */
interface QuestionAnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Answer form.
   *
   * @return array
   *   Form.
   */
  public function getAnswerForm(): array;

  /**
   * Answer data.
   *
   * @return array
   *   The data.
   */
  public function getData(): array;

  /**
   * Build form for creating answer of question.
   *
   * @return array
   *   The build array.
   */
  public function buildAnswerForm(): array;

  /**
   * Handles form submission for the question answer type.
   *
   * @param array $form
   *   The form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitAnswerForm(array &$form, FormStateInterface $form_state): void;

}
