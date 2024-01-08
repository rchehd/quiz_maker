<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the question response entity class.
 *
 * @ContentEntityType(
 *   id = "question_response",
 *   label = @Translation("Question Response"),
 *   label_collection = @Translation("Question Responses"),
 *   label_singular = @Translation("question response"),
 *   label_plural = @Translation("question responses"),
 *   label_count = @PluralTranslation(
 *     singular = "@count question responses",
 *     plural = "@count question responses",
 *   ),
 *   bundle_label = @Translation("Question Response type"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuestionResponseForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuestionResponseForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "question_response",
 *   admin_permission = "administer question_response types",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/quiz-maker/question-response",
 *     "add-form" = "/question-response/add/{question_response_type}",
 *     "add-page" = "/question-response/add",
 *     "canonical" = "/question-response/{question_response}",
 *     "edit-form" = "/question-response/{question_response}/edit",
 *     "delete-form" = "/question-response/{question_response}/delete",
 *     "delete-multiple-form" = "/admin/quiz-maker/question-response/delete-multiple",
 *   },
 *   bundle_entity_type = "question_response_type",
 *   field_ui_base_route = "entity.question_response_type.edit_form",
 * )
 */
abstract class QuestionResponse extends ContentEntityBase implements QuestionResponseInterface {

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

    $fields['label'] = BaseFieldDefinition::create('string')
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

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setSetting('target_type', 'question')
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
      ->setDisplayConfigurable('view', TRUE);

    $fields['quiz_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Quiz'))
      ->setSetting('target_type', 'quiz')
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
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_correct'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is correct'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Is correct')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['response'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of response data.'));

    $fields['score'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score')
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the question response was created.'))
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
      ->setDescription(t('The time that the question response was last edited.'));

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): ?QuestionInterface {
    return $this->get('question_id')->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuiz(): ?QuizInterface {
    return $this->get('quiz_id')->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function isCorrect(): bool {
    return $this->get('is_correct')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getScore(): int {
    return $this->get('score')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function setResponseData(array $data): void {
    $this->set('response', $data);
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
  public function setQuiz(QuizInterface $quiz): void {
    $this->set('quiz_id', $quiz->id());
  }

  /**
   * {@inheritDoc}
   */
  public function setQuestion(QuestionInterface $question): void {
    $this->set('question_id', $question->id());
  }

  /**
   * {@inheritDoc}
   */
  public function setScore(QuestionInterface $question, bool $value): void {
    if ($value) {
      $this->set('score', $question->getMaxScore());
    }
    else {
      $this->set('score', 0);
    }
  }

}
