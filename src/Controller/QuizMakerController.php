<?php

namespace Drupal\quiz_maker\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\quiz_maker\QuizInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Quiz Maker routes.
 */
final class QuizMakerController extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountInterface $currentUser
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Take quiz form title.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz entity.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string|null
   *   The title.
   */
  public function getQuizTakeFormTitle(QuizInterface $quiz): string|TranslatableMarkup|null {
    return $quiz->label();
  }

  /**
   * Access to take quiz.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function quizTakeAccess(QuizInterface $quiz): AccessResultInterface {
    return AccessResult::forbiddenIf(!$quiz->allowToTake($this->currentUser()));
  }

}
