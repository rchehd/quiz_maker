<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

class InlineQuestionAnswerForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    $form = parent::entityForm($entity_form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);

    $fields['field_is_correct'] = [
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