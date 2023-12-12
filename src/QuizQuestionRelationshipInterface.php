<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a quiz question relationship entity type.
 */
interface QuizQuestionRelationshipInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Get quiz.
   *
   * @return \Drupal\quiz_maker\QuizInterface
   *   The quiz.
   */
  public function getQuiz(): QuizInterface;

  /**
   * Get question.
   *
   * @return \Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getQuestion(): QuestionInterface;

}
