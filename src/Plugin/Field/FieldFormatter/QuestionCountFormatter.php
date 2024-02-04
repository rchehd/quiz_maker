<?php

namespace Drupal\quiz_maker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Entity Count' formatter.
 *
 * @FieldFormatter(
 *   id = "question_count",
 *   label = @Translation("Question Count"),
 *   field_types = {"entity_reference", "entity_reference_revisions"},
 * )
 */
final class QuestionCountFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];
    $element[0] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => count($items),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition): bool {
    return $field_definition->getName() === 'questions';
  }

}
