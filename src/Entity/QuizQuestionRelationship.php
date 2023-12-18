<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizQuestionRelationshipInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the quiz question relationship entity class.
 *
 * @ContentEntityType(
 *   id = "quiz_maker_qq_relationship",
 *   label = @Translation("Quiz Question Relationship"),
 *   label_collection = @Translation("Quiz Question Relationships"),
 *   label_singular = @Translation("quiz question relationship"),
 *   label_plural = @Translation("quiz question relationships"),
 *   label_count = @PluralTranslation(
 *     singular = "@count quiz question relationships",
 *     plural = "@count quiz question relationships",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuizQuestionRelationshipListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuizQuestionRelationshipForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuizQuestionRelationshipForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\quiz_maker\Routing\QuizQuestionRelationshipHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "quiz_maker_qq_relationship",
 *   admin_permission = "administer quiz_maker_qq_relationship",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/quiz-question-relationship",
 *     "add-form" = "/quiz-question-relationship/add",
 *     "canonical" = "/quiz-question-relationship/{quiz_maker_qq_relationship}",
 *     "edit-form" = "/quiz-question-relationship/{quiz_maker_qq_relationship}",
 *     "delete-form" = "/quiz-question-relationship/{quiz_maker_qq_relationship}/delete",
 *     "delete-multiple-form" = "/admin/content/quiz-question-relationship/delete-multiple",
 *   },
 * )
 */
final class QuizQuestionRelationship extends ContentEntityBase implements QuizQuestionRelationshipInterface {

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

    $fields['quiz_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Quiz'))
      ->setSetting('target_type', 'quiz_maker_quiz')
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

    $fields['quiz_vid'] = BaseFieldDefinition::create('integer')
      ->setLabel('Quiz revision ID');

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

    $fields['question_vid'] = BaseFieldDefinition::create('integer')
      ->setLabel('Quiz revision ID');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the quiz question relationship was created.'))
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
      ->setDescription(t('The time that the quiz question relationship was last edited.'));

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuiz(): QuizInterface {
    return $this->get('quiz_id')->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): QuestionInterface {
    return $this->get('question_id')->entity;
  }

}
