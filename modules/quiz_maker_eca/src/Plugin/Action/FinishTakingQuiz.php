<?php

namespace Drupal\quiz_maker_eca\Plugin\Action;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\quiz_maker\Service\QuizManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eca\Plugin\Action\ActionBase;

/**
 * Load the currently logged in user into the token environment.
 *
 * @Action(
 *   id = "quiz_maker_finish_taking_quiz",
 *   label = @Translation("Quiz maker: finish quiz."),
 *   description = @Translation("Finish taking quiz.")
 * )
 */
class FinishTakingQuiz extends ConfigurableActionBase {

  /**
   * Thw quiz manager service.
   *
   * @var \Drupal\quiz_maker\Service\QuizManager
   */
  protected QuizManager $quizManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->quizManager = $container->get('quiz_maker.quiz_manager');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    $quiz_result = $this->quizManager->getUserLastQuizResult($this->currentUser);
    if ($quiz_result instanceof QuizResultInterface) {
      $this->quizManager->finishQuiz($quiz_result, $this->languageManager->getCurrentLanguage()->getId());
    }
  }

}