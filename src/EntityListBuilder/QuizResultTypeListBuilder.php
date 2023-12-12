<?php

namespace Drupal\quiz_maker\EntityListBuilder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of quiz result type entities.
 *
 * @see \Drupal\quiz_maker\Entity\QuizResultType
 */
final class QuizResultTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No quiz result types available. <a href=":link">Add quiz result type</a>.',
      [':link' => Url::fromRoute('entity.quiz_maker_quiz_result_type.add_form')->toString()],
    );

    return $build;
  }

}
