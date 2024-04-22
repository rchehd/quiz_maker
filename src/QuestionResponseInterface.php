<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question response entity type.
 */
interface QuestionResponseInterface {

  /**
   * Get question.
   *
   * @return \Drupal\quiz_maker\QuestionInterface|null
   *   The question.
   */
  public function getQuestion(): ?QuestionInterface;

  /**
   * Get quiz.
   *
   * @return \Drupal\quiz_maker\QuizInterface|null
   *   The quiz.
   */
  public function getQuiz(): ?QuizInterface;

  /**
   * Get response score.
   *
   * @return float
   *   The score.
   */
  public function getScore(): float;

  /**
   * Get response ids.
   *
   * @return array
   *   The response ids.
   */
  public function getResponses(): array;

  /**
   * TRUE if response was correct.
   *
   * @return bool
   *   TRUE if correct, otherwise FALSE.
   */
  public function isCorrect(): bool;

  /**
   * Set response data.
   *
   * @param array $data
   *   The data.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The response object.
   */
  public function setResponseData(array $data): QuestionResponseInterface;

  /**
   * Set is_correct value.
   *
   * @param bool $value
   *   The value.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The response object.
   */
  public function setCorrect(bool $value): QuestionResponseInterface;

  /**
   * Set quiz reference.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The response object.
   */
  public function setQuiz(QuizInterface $quiz): QuestionResponseInterface;

  /**
   * Set question reference.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The response object.
   */
  public function setQuestion(QuestionInterface $question): QuestionResponseInterface;

  /**
   * Calculate and set score.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   * @param bool $value
   *   TRUE id response is correct, otherwise FALSE.
   * @param ?float $score
   *   The score.
   * @param array $response_data
   *   The response data.
   *
   * @return \Drupal\quiz_maker\QuestionResponseInterface
   *   The response object.
   */
  public function setScore(QuestionInterface $question, bool $value, float $score = NULL, array $response_data = []): QuestionResponseInterface;

}
