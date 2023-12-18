<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\boll;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the quiz entity class.
 *
 * @ContentEntityType(
 *   id = "quiz_maker_quiz",
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
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuizListBuilder",
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
 *   base_table = "quiz_maker_quiz",
 *   data_table = "quiz_maker_quiz_field_data",
 *   revision_table = "quiz_maker_quiz_revision",
 *   revision_data_table = "quiz_maker_quiz_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer quiz_maker_quiz types",
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
 *     "collection" = "/admin/quiz/quizzes",
 *     "add-form" = "/quiz/add/{quiz_maker_quiz_type}",
 *     "add-page" = "/quiz/add",
 *     "canonical" = "/quiz/{quiz_maker_quiz}",
 *     "edit-form" = "/quiz/{quiz_maker_quiz}/edit",
 *     "delete-form" = "/quiz/{quiz_maker_quiz}/delete",
 *     "delete-multiple-form" = "/admin/quiz/quizzes/delete-multiple",
 *   },
 *   bundle_entity_type = "quiz_maker_quiz_type",
 *   field_ui_base_route = "entity.quiz_maker_quiz_type.edit_form",
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

    $fields['pass_rate'] = BaseFieldDefinition::create('integer')
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setSettings([
        'min' => 0,
        'max' => 100,
        'suffix' => '%',
      ])
      ->setDescription(t('Minimum grade percentage required to pass this quiz.'))
      ->setLabel(t('Grade required to pass'));

    $fields['backwards_navigation'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allowed backwards navigation'))
      ->setDefaultValue(1)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Allow users to go back and revisit questions already answered.'));

    $fields['quiz_date_range'] = BaseFieldDefinition::create('daterange')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'daterange_default',
      ])
      ->setDescription(t('The date and time during which this Quiz will be available. Leave blank to always be available.'))
      ->setLabel(t('Quiz date range'));

    $fields['attempts'] = BaseFieldDefinition::create('integer')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setLabel(t('Allowed number of attempts'))
      ->setDescription(t('The number of times a user is allowed to take this Quiz. Anonymous users are only allowed to take Quiz that allow an unlimited number of attempts.'));

    $fields['time_limit'] = BaseFieldDefinition::create('integer')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setSetting('min', 0)
      ->setDescription(t('Set the maximum allowed time in seconds for this Quiz. Use 0 for no limit.'))
      ->setLabel(t('Time limit'));

    $fields['max_score'] = BaseFieldDefinition::create('integer')
      ->setRevisionable(TRUE)
      ->setLabel(t('Calculated max score of this quiz.'));

    $fields['allow_skipping'] = BaseFieldDefinition::create('boolean')
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Allow users to skip questions in this Quiz.'))
      ->setLabel(t('Allow skipping'));

    $fields['allow_resume'] = BaseFieldDefinition::create('boolean')
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Allow users to leave this Quiz incomplete and then resume it from where they left off.'))
      ->setLabel(t('Allow resume'));

    $fields['allow_jumping'] = BaseFieldDefinition::create('boolean')
      ->setDefaultValue(0)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Allow users to jump to any question using a menu or pager in this Quiz.'))
      ->setLabel(t('Allow jumping'));

    $fields['allow_change'] = BaseFieldDefinition::create('boolean')
      ->setDefaultValue(1)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('If the user is able to visit a previous question, allow them to change the answer.'))
      ->setLabel(t('Allow changing answers'));

    $fields['allow_change_blank'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow changing blank answers'))
      ->setDescription(t('Allow users to go back and revisit questions already answered.'))
      ->setDefaultValue(0)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ]);

    $fields['build_on_last'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Each attempt builds on the last'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('fresh')
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
      ])
      ->setSetting('allowed_values', [
        'fresh' => t('Fresh attempt every time'),
        'correct' => t('Prepopulate with correct answers from last result'),
        'all' => t('Prepopulate with all answers from last result'),
      ])
      ->setDescription(t('Instead of starting a fresh Quiz, users can base a new attempt on the last attempt, with correct answers prefilled. Set the default selection users will see. Selecting "fresh attempt every time" will not allow the user to choose.'));

    $fields['show_passed'] = BaseFieldDefinition::create('boolean')
      ->setDefaultValue(1)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Show a message if the user has previously passed the Quiz.'))
      ->setLabel(t('Show passed message'));

    $fields['mark_doubtful'] = BaseFieldDefinition::create('boolean')
      ->setDefaultValue(0)
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDescription(t('Allow users to mark their answers as doubtful.'))
      ->setLabel(t('Mark doubtful'));

    $fields['review_options'] = BaseFieldDefinition::create('map')
      ->setRevisionable(TRUE)
      ->setLabel(t('Review options'));

    $fields['result_type'] = BaseFieldDefinition::create('entity_reference')
      ->setSetting('target_type', 'quiz_maker_quiz_result_type')
      ->setRequired(TRUE)
      ->setDefaultValue('quiz_result')
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setLabel(t('Result type to use'));

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
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
  public function addQuestion(QuestionInterface $question): void {
    // TODO: Implement addQuestion() method.
  }

  /**
   * {@inheritDoc}
   */
  public function deleteQuestion(QuestionInterface $question): void {
    // TODO: Implement deleteQuestion() method.
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestions(): array|bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getAllResults(): array|bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getUserResult(AccountInterface $user): ?QuizResultInterface {
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function isPassed(AccountInterface $user): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function requiresManualEvaluation(): bool {
    return FALSE;
  }

}
