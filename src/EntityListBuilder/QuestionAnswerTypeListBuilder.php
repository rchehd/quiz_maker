<?php

namespace Drupal\quiz_maker\EntityListBuilder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of question answer type entities.
 *
 * @see \Drupal\quiz_maker\Entity\QuestionAnswerType
 */
final class QuestionAnswerTypeListBuilder extends ConfigEntityListBuilder {

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
      'No question answer types available. <a href=":link">Add question answer type</a>.',
      [':link' => Url::fromRoute('entity.question_answer_type.add_form')->toString()],
    );

    return $build;
  }

}
