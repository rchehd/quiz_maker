<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\quiz_maker\Annotation\QuizMakerQuestionAnswer;
use Drupal\quiz_maker\QuestionAnswerInterface;

/**
 * QuizMakerQuestionAnswer plugin manager.
 */
final class QuizMakerQuestionAnswerPluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/QuizMaker/Answer', $namespaces, $module_handler, QuestionAnswerInterface::class, QuizMakerQuestionAnswer::class);
    $this->alterInfo('question_answer_info');
    $this->setCacheBackend($cache_backend, 'question_answer_plugins');
  }

}
