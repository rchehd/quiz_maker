<?php

namespace Drupal\quiz_maker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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

}
