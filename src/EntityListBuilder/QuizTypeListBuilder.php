<?php

namespace Drupal\quiz_maker\EntityListBuilder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of quiz type entities.
 *
 * @see \Drupal\quiz_maker\Entity\QuizType
 */
final class QuizTypeListBuilder extends ConfigEntityListBuilder {

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
      'No quiz types available. <a href=":link">Add quiz type</a>.',
      [':link' => Url::fromRoute('entity.quiz_type.add_form')->toString()],
    );

    return $build;
  }

}
