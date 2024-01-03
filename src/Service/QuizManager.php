<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\quiz_maker\QuizInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Quiz manager service.
 */
final class QuizManager {

  /**
   * Constructs a QuizManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RequestStack $requestStack,
  ) {}

  /**
   * @todo Add method description.
   */
  public function createQuizResult(QuizInterface $quiz) {
    $quiz_result_type = $quiz->get('field_result_type')->target_id;
    $this->entityTypeManager->getStorage('quiz_result')->create([
      'type' => $quiz_result_type,
    ]);
  }

}
