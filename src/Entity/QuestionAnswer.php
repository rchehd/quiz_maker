<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the question answer entity class.
 *
 * @ContentEntityType(
 *   id = "question_answer",
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
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuestionAnswerForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuestionAnswerForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "inline_form" = "Drupal\quiz_maker\Form\InlineQuestionAnswerForm",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *   },
 *   base_table = "question_answer",
 *   data_table = "question_answer_field_data",
 *   translatable = FALSE,
 *   admin_permission = "administer question_answer types",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "langcode" = "langcode",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/quiz-maker/question-answer",
 *     "add-form" = "/question-answer/add/{question_answer_type}",
 *     "add-page" = "/question-answer/add",
 *     "canonical" = "/question-answer/{question_answer}",
 *     "edit-form" = "/question-answer/{question_answer}/edit",
 *     "delete-form" = "/question-answer/{question_answer}/delete",
 *     "delete-multiple-form" = "/admin/quiz-maker/question-answer/delete-multiple",
 *   },
 *   bundle_entity_type = "question_answer_type",
 *   field_ui_base_route = "entity.question_answer_type.edit_form",
 * )
 */
abstract class QuestionAnswer extends ContentEntityBase implements QuestionAnswerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * The "Correct" answer status.
   */
  const CORRECT = 'correct';

  /**
   * The "In-correct" answer status.
   */
  const IN_CORRECT = 'in-correct';

  /**
   * The "Neutral" answer status.
   */
  const NEUTRAL = 'neutral';

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

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

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

    $fields['answer'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Answer'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_correct'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is correct'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

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

  /**
   * {@inheritDoc}
   */
  public function isCorrect(): bool {
    return (bool) $this->get('is_correct')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function setCorrect(bool $value): void {
    $this->set('is_correct', $value);
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string {
    return $this->get('answer')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function isAlwaysCorrect(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isAlwaysInCorrect(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getViewHtmlTag(): string {
    return 'li';
  }

}
