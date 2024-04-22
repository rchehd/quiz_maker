<?php

namespace Drupal\quiz_maker;

/**
 * Provides an interface defining a question answer entity type.
 */
interface QuestionAnswerInterface {

  /**
   * Check is answer is correct.
   *
   * @return bool
   *   TRUE id correct, otherwise FALSE.
   */
  public function isCorrect(): bool;

  /**
   * Set "is correct" value.
   *
   * @param bool $value
   *   The value.
   */
  public function setCorrect(bool $value): void;

  /**
   * Return answer text if it exists.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface|null $response
   *   The answer response.
   *
   * @return string|null
   *   The answer text or null.
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string;

  /**
   * Get response status.
   *
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   *
   * @return string
   *   The answer status:
   *     Correct - response is correct,
   *     In-correct - response isn't correct.
   *     Neutral - user didn't choose this answer.
   */
  public function getResponseStatus(QuestionResponseInterface $response): string;

  /**
   * Should answer should be always correct.
   *
   * @return bool
   *   TRUE if it should, otherwise FALSE.
   */
  public function isAlwaysCorrect(): bool;

  /**
   * Should answer should be always in-correct.
   *
   * @return bool
   *   TRUE if it should, otherwise FALSE.
   */
  public function isAlwaysInCorrect(): bool;

  /**
   * Get html tag to render answer in result view.
   *
   * @return string
   *   The tag.
   */
  public function getViewHtmlTag(): string;

}
