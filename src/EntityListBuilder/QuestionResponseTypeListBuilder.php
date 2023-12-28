<?php

namespace Drupal\quiz_maker\EntityListBuilder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of question response type entities.
 *
 * @see \Drupal\quiz_maker\Entity\QuestionResponseType
 */
final class QuestionResponseTypeListBuilder extends ConfigEntityListBuilder {

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
      'No question response types available. <a href=":link">Add question response type</a>.',
      [':link' => Url::fromRoute('entity.question_response_type.add_form')->toString()],
    );

    return $build;
  }

}
