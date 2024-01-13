<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Inline entity form for question answer entity.
 */
class InlineQuestionAnswerForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);

    $fields['is_correct'] = [
      'type' => 'field',
      'label' => $this->t('Is correct'),
      'weight' => 3,
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function isTableDragEnabled($element) {
    return TRUE;
  }

}
