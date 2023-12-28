<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\quiz_maker\Annotation\QuizMakerQuestionResponse;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * QuizMakerQuestionResponse plugin manager.
 */
final class QuizMakerQuestionResponsePluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/QuizMaker/Response', $namespaces, $module_handler, QuestionResponseInterface::class, QuizMakerQuestionResponse::class);
    $this->alterInfo('question_response_info');
    $this->setCacheBackend($cache_backend, 'question_response_plugins');
  }

}
