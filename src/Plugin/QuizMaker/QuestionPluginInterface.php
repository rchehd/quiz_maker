<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * The Question Plugin interface.
 */
interface QuestionPluginInterface extends QuestionInterface {

  /**
   * Get plugin entity.
   *
   * @return \Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getEntity(): QuestionInterface;

}
