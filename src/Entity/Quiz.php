<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\user\EntityOwnerTrait;
use http\Exception\BadUrlException;

/**
 * Defines the quiz entity class.
 *
 * @ContentEntityType(
 *   id = "quiz",
 *   label = @Translation("Quiz"),
 *   label_collection = @Translation("Quizzes"),
 *   label_singular = @Translation("quiz"),
 *   label_plural = @Translation("quizzes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count quizzes",
 *     plural = "@count quizzes",
 *   ),
 *   bundle_label = @Translation("Quiz type"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\quiz_maker\EntityAccessControlHandler\QuizAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuizForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuizForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "quiz",
 *   data_table = "quiz_field_data",
 *   revision_table = "quiz_revision",
 *   revision_data_table = "quiz_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer quiz types",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/quiz-maker/quizzes",
 *     "add-form" = "/quiz/add/{quiz_type}",
 *     "add-page" = "/quiz/add",
 *     "canonical" = "/quiz/{quiz}",
 *     "edit-form" = "/quiz/{quiz}/edit",
 *     "delete-form" = "/quiz/{quiz}/delete",
 *     "delete-multiple-form" = "/admin/quiz-maker/quizzes/delete-multiple",
 *   },
 *   bundle_entity_type = "quiz_type",
 *   field_ui_base_route = "entity.quiz_type.edit_form",
 * )
 */
class Quiz extends RevisionableContentEntityBase implements QuizInterface {

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
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
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

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
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

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the quiz was created.'))
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
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the quiz was last edited.'));

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestions(): array|bool {
    if ($this->hasField('field_questions')) {
      return $this->get('field_questions')->referencedEntities();
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isPassed(AccountInterface $user): bool {
    $completed_results = $this->getResults($user, QuizResultType::COMPLETED, [
      'passed' => 1,
    ]);

    if (count($completed_results)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function requiresManualEvaluation(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function allowSkipping(): bool {
    return (bool) $this->get('field_allow_skipping')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowBackwardNavigation(): bool {
    return (bool) $this->get('field_backwards_navigation')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowChangeAnswer(): bool {
    return (bool) $this->get('field_allow_changing_answers')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function getAllowedAttempts(): ?int {
    return $this->get('field_attempts')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getMaxScore(): int {
    $questions = $this->getQuestions();
    $max_score = 0;
    foreach ($questions as $question) {
      /** @var \Drupal\quiz_maker\QuestionInterface $question */
      $max_score += $question->getMaxScore();
    }
    return $max_score;
  }

  /**
   * {@inheritDoc}
   */
  public function getPassRate(): int {
    return $this->get('field_pass_rate')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getAccessPeriod(): array {
    $access_period = $this->get('field_access_period')->getValue();
    if ($access_period) {
      $access_period = reset($access_period);
      return [
        'start_date' => strtotime($access_period['value']),
        'end_date' => strtotime($access_period['end_value']),
      ];
    }
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getResultType(): string {
    return $this->get('field_result_type')->target_id;
  }

}
