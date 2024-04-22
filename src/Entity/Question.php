<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginInterface;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\Trait\EntityWithPluginTrait;
use Drupal\taxonomy\TermInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the question entity class.
 *
 * @ContentEntityType(
 *   id = "question",
 *   label = @Translation("Question"),
 *   label_collection = @Translation("Questions"),
 *   label_singular = @Translation("question"),
 *   label_plural = @Translation("questions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count questions",
 *     plural = "@count questions",
 *   ),
 *   bundle_label = @Translation("Question type"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\quiz_maker\EntityAccessControlHandler\QuestionAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\quiz_maker\Form\QuestionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" = "\Drupal\Core\Entity\Form\RevisionDeleteForm::class",
 *       "revision-revert" = "\Drupal\Core\Entity\Form\RevisionRevertForm::class",
 *     },
 *     "inline_form" = "Drupal\quiz_maker\Form\InlineQuestionForm",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *   },
 *   base_table = "question",
 *   data_table = "question_field_data",
 *   revision_table = "question_revision",
 *   revision_data_table = "question_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer question types",
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
 *     "collection" = "/admin/quiz-maker/question",
 *     "add-form" = "/question/add/{question_type}",
 *     "add-page" = "/question/add",
 *     "canonical" = "/question/{question}",
 *     "edit-form" = "/question/{question}/edit",
 *     "delete-form" = "/question/{question}/delete",
 *     "delete-multiple-form" = "/admin/quiz-maker/question/delete-multiple",
 *     "version-history" = "/question/{question}/revisions",
 *     "revision" = "/question/{question}/revision/{question_revision}/view",
 *     "revision-delete-form" = "/question/{question}/revision/{question_revision}/delete",
 *     "revision-revert-form" = "/question/{question}/revision/{question_revision}/revert",
 *   },
 *   bundle_entity_type = "question_type",
 *   field_ui_base_route = "entity.question_type.edit_form",
 * )
 */
class Question extends RevisionableContentEntityBase implements QuestionInterface, ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * The plugin instance.
   *
   * @var \Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginInterface|null
   */
  private ?QuestionPluginInterface $instance;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $this->instance = $this->getPluginInstance();
  }

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

    $fields['question'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Question'))
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

    $fields['answers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answers'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'question_answer')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['response_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Response type'))
      ->setSetting('target_type', 'question_response_type')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['tag'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tag'))
      ->setDescription(t('The tag of question (Question group).'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['max_score'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Max score'))
      ->setDescription(t('The unscaled calculated max score of this Question.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(1)
      ->setSettings([
        'min' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'score_field_formatter',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the question was created.'))
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
      ->setDescription(t('The time that the question was last edited.'));

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): ?string {
    return $this->instance->getQuestion();
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswers(): ?array {
    return $this->instance->getAnswers();
  }

  /**
   * {@inheritDoc}
   */
  public function getCorrectAnswers(): array {
    return $this->instance->getCorrectAnswers();
  }

  /**
   * {@inheritDoc}
   */
  public function getMaxScore(): int {
    return $this->instance->getMaxScore();
  }

  /**
   * {@inheritDoc}
   */
  public function getTag(): ?TermInterface {
    return $this->instance->getTag();
  }

  /**
   * {@inheritDoc}
   */
  public function addAnswer(QuestionAnswerInterface $answer): void {
    $this->instance->addAnswer($answer);
  }

  /**
   * {@inheritDoc}
   */
  public function isEnabled(): bool {
    return $this->instance->isEnabled();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseType(): ?string {
    return $this->instance->getResponseType();
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswerType(): ?string {
    return $this->instance->getAnswerType();
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestionAnswerWrapperId(): string {
    return $this->instance->getQuestionAnswerWrapperId();
  }

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    return $this->instance->getAnsweringForm($question_response, $allow_change_response);
  }

  /**
   * {@inheritDoc}
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void {
    $this->instance->validateAnsweringForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    return $this->getPluginInstance()->getResponse($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $answers_ids): bool {
    return $this->instance->isResponseCorrect($answers_ids);
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultAnswersData(): array {
    return $this->instance->getDefaultAnswersData();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseView(QuestionResponseInterface $response, int $mark_mode = 0): array {
    return $this->instance->getResponseView($response, $mark_mode);
  }

  /**
   * Get question plugin instance.
   *
   * @return \Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginInterface|null
   *   The plugin instance.
   */
  protected function getPluginInstance(): ?QuestionPluginInterface {
    $question_type = $this->getEntityType();
    if ($question_type instanceof QuestionType) {
      /** @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
      $plugin_manager = \Drupal::service('plugin.manager.quiz_maker.question');
      try {
        $question_instance = $plugin_manager->createInstance($question_type->getPluginId(), ['question_id' => $this->id()]);
        return $question_instance instanceof QuestionPluginInterface ? $question_instance : NULL;
      }
      catch (PluginException $e) {
        \Drupal::logger('quiz_maker')->error($e->getMessage());
        return NULL;
      }
    }

    return NULL;
  }

}
