<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the question answer entity class.
 *
 * @ContentEntityType(
 *   id = "quiz_maker_question_answer",
 *   label = @Translation("Question Answer"),
 *   label_collection = @Translation("Question Answers"),
 *   label_singular = @Translation("question answer"),
 *   label_plural = @Translation("question answers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count question answers",
 *     plural = "@count question answers",
 *   ),
 *   bundle_label = @Translation("Question Answer type"),
 *   handlers = {
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuestionAnswerListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuestionAnswerForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuestionAnswerForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "quiz_maker_question_answer",
 *   translatable = FALSE,
 *   admin_permission = "administer quiz_maker_question_answer types",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/question-answer",
 *     "add-form" = "/question-answer/add/{quiz_maker_question_answer_type}",
 *     "add-page" = "/question-answer/add",
 *     "canonical" = "/question-answer/{quiz_maker_question_answer}",
 *     "edit-form" = "/question-answer/{quiz_maker_question_answer}/edit",
 *     "delete-form" = "/question-answer/{quiz_maker_question_answer}/delete",
 *     "delete-multiple-form" = "/admin/content/question-answer/delete-multiple",
 *   },
 *   bundle_entity_type = "quiz_maker_question_answer_type",
 *   field_ui_base_route = "entity.quiz_maker_question_answer_type.edit_form",
 * )
 */
abstract class QuestionAnswer extends ContentEntityBase implements QuestionAnswerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setSetting('target_type', 'quiz_maker_question')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['data'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of question data.'));

    $fields['score_for_true'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Score'))
      ->setDescription(t('Answer score if it will be answered rightly'))
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'number',
      ]);

    $fields['score_for_false'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Score'))
      ->setDescription(t('Answer score if it will be answered wrong'))
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'number',
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the question answer was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the question answer was last edited.'));

    return $fields;
  }

}
