<?php

namespace Drupal\quiz_maker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Builds the response.
   */
  public function manageQuestions(QuizInterface $quiz): array {

    $questions = $quiz->getQuestions();

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Manage questions!'),
    ];

    return $build;
  }

  /**
   * Builds the response.
   */
  public function manageResults(QuizInterface $quiz): array {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Manage results!'),
    ];

    return $build;
  }

  public function takeQuiz(QuizInterface $quiz) {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Take quiz!'),
    ];

    return $build;
  }

  public function getQuizTakeFormTitle(QuizInterface $quiz) {
    return $quiz->label();
  }

}
