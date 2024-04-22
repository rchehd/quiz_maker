<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\AnswerBaseInterface;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * The Question Plugin interface.
 */
interface QuestionAnswerPluginInterface extends QuestionAnswerInterface {

  /**
   * Get plugin entity.
   *
   * @return \Drupal\quiz_maker\QuestionAnswerInterface
   *   The question.
   */
  public function getEntity(): QuestionAnswerInterface;

}
