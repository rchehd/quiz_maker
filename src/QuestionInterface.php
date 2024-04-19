<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question entity type.
 */
interface QuestionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get Question plugin.
   *
   * @return ?\Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginInterface
   *   The plugin object.
   */
  public function getInstance(): ?QuestionPluginInterface;

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
   * Get max score.
   *
   * @return int
   *   The score.
   */
  public function getMaxScore(): int;

  /**
   * Get question term.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   The term if it exists, otherwise null.
   */
  public function getTag(): ?TermInterface;

  /**
   * Is question enabled?
   *
   * @return bool
   *   TRUE if enabled, otherwise FALSE.
   */
  public function isEnabled(): bool;

}
