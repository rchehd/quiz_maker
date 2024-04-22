<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\user\EntityOwnerTrait;

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
 *       "revision-delete" = "\Drupal\Core\Entity\Form\RevisionDeleteForm::class",
 *       "revision-revert" = "\Drupal\Core\Entity\Form\RevisionRevertForm::class",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class
 *     },
 *    "translation" = "Drupal\content_translation\ContentTranslationHandler",
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
 *     "add-form" = "/admin/quiz_maker/quiz/add/{quiz_type}",
 *     "add-page" = "/admin/quiz_maker/quiz/add",
 *     "canonical" = "/quiz/{quiz}",
 *     "edit-form" = "/admin/quiz_maker/quiz/{quiz}/edit",
 *     "delete-form" = "/admin/quiz_maker/quiz/{quiz}/delete",
 *     "delete-multiple-form" = "/admin/quiz-maker/quizzes/delete-multiple",
 *     "version-history" = "/admin/quiz_maker/quiz/{quiz}/revisions",
 *     "revision" = "/admin/quiz_maker/quiz/{quiz}/revision/{quiz_revision}/view",
 *     "revision-delete-form" = "/admin/quiz_maker/quiz/{quiz}/revision/{quiz_revision}/delete",
 *     "revision-revert-form" = "/admin/quiz_maker/quiz/{quiz}/revision/{quiz_revision}/revert",
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

    if ($questions = $this->getQuestionByTags()) {
      $this->set('questions', $questions);
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
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 1,
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

    $fields['questions'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Questions'))
      ->setDescription(t('<strong>Warning:</strong> Quiz has a reference on question revision, so if you create a new revision of a question - you need to re-add the question for this quiz'))
      ->setSetting('target_type', 'question')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['result_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Result type'))
      ->setSetting('target_type', 'quiz_result_type')
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->setDefaultValue('standard')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['access_period'] = BaseFieldDefinition::create('daterange')
      ->setLabel(t('Access period'))
      ->setDescription(t('The date and time during which this Quiz will be available. Leave blank to always be available.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'daterange_default',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['allow_changing_answers'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow changing answers'))
      ->setDescription(t('If the user is able to visit a previous question, allow them to change the answer.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['allow_jumping'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow jumping'))
      ->setDescription(t('Allow users to jump to any question using a menu or pager in this Quiz.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 8,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['allow_skipping'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow skipping'))
      ->setDescription(t('Allow users to skip questions in this Quiz.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 7,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['allow_backwards_navigation'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow backwards navigation'))
      ->setDescription(t('Allow users to go back and revisit questions already answered.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['manual_assessment'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Manual assessment'))
      ->setDescription(t('Enable, if you want to assess and set the score of quiz result manual.<br><strong>Warning: </strong><em>some type of question require manual assessment!</em>'))
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

    $fields['attempts'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Attempts'))
      ->setDescription(t('The number of times a user is allowed to take this Quiz. Anonymous users are only allowed to take Quiz that allow an unlimited number of attempts.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['pass_rate'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pass rate'))
      ->setDescription(t('Minimum grade percentage required to pass this quiz.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setSettings([
        'min' => 0,
        'max' => 100,
        'suffix' => '%',
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['time_limit'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Time Limit'))
      ->setDescription(t('Set the maximum allowed time in seconds for this Quiz. Use 0 for no limit.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setSettings([
        'min' => 0,
        'suffix' => 'sec',
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['questions_tag'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Questions tag'))
      ->setDescription(t('Add questions that has tags from "Questions Tags" taxonomy. (You can add multiple tags with a comma)'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['questions_tags' => 'questions_tags']])
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

    $fields['randomize_question_sequence'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Randomize question sequence.'))
      ->setDescription(t('Randomize question sequence instead of chosen sequence.'))
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
      ->setDescription(t('The time that the quiz was last edited.'));

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestions(): array {
    $result = [];
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $questions = $this->get('questions')->referencedEntities();
    foreach ($questions as $question) {
      /** @var \Drupal\quiz_maker\Entity\Question $question */
      if ($question->hasTranslation($langcode) && $question->isEnabled()) {
        $result[] = $question->getTranslation($langcode);
      }
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestionTags(): array {
    return $this->get('questions_tag')->referencedEntities();
  }

  /**
   * {@inheritDoc}
   */
  public function getResults(AccountInterface $user, array $conditions = []): array {
    $result_type = $this->getResultType();
    try {
      $result_storage = \Drupal::entityTypeManager()->getStorage('quiz_result');
      $query = $result_storage->getQuery();
      $query->condition('bundle', $result_type)
        ->condition('uid', $user->id());

      if ($conditions) {
        foreach ($conditions as $key => $value) {
          $query->condition($key, $value);
        }
      }

      $result_ids = $query->accessCheck(FALSE)->execute();

      if ($result_ids) {
        return $result_storage->loadMultiple($result_ids);
      }

    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('quiz_maker')->error($e->getMessage());
      return [];
    }

    return [];
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
  public function getCompletedAttempts(AccountInterface $user): int {
    try {
      $results = \Drupal::entityTypeManager()->getStorage('quiz_result')
        ->loadByProperties([
          'uid' => $user->id(),
          'quiz' => $this->id(),
          'state' => QuizResultType::COMPLETED,
        ]);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('quiz_maker')->error($e->getMessage());
      return 0;
    }

    return count($results);
  }

  /**
   * {@inheritDoc}
   */
  public function requireManualAssessment(): bool {
    return (bool) $this->get('manual_assessment')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowSkipping(): bool {
    return (bool) $this->get('allow_skipping')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowBackwardNavigation(): bool {
    return (bool) $this->get('allow_backwards_navigation')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowChangeAnswer(): bool {
    return (bool) $this->get('allow_changing_answers')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function allowTaking(AccountInterface $user): bool|array {
    $reasons_to_forbidden = [];
    $quiz_attempts = $this->getAllowedAttempts();
    $access_period = $this->getAccessPeriod();
    // Do not allow to take quiz if user used all the attempts.
    $completed_results = $this->getResults($user, ['state' => QuizResultType::COMPLETED]);
    if ($quiz_attempts && $quiz_attempts <= count($completed_results)) {
      $reasons_to_forbidden[] = t('You used all attempts.');
    }
    // Do not allow to take quiz if access period is expired.
    $now = \Drupal::time()->getCurrentTime();
    if ($access_period && ($now < $access_period['start_date'] || $now > $access_period['end_date'])) {
      $reasons_to_forbidden[] = t('Quiz not available because of access period.');
    }

    $reviewed_result = $this->getResults($user, [
      'state' => QuizResultType::ON_REVIEW,
    ]);

    if ($reviewed_result) {
      $reasons_to_forbidden[] = t('Your last quiz result is on the assessment now, please wait for the completion of the assessment to do the next attempt.');
    }

    if (empty($this->getQuestions())) {
      $reasons_to_forbidden[] = t('Quiz doesn\'t have any question.');
    }

    return !empty($reasons_to_forbidden) ? $reasons_to_forbidden : TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function allowJumping(): bool {
    return (bool) $this->get('allow_jumping')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function getAllowedAttempts(): ?int {
    return $this->get('attempts')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getPassRate(): ?int {
    return $this->get('pass_rate')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getResultType(): string {
    return $this->get('result_type')->target_id;
  }

  /**
   * {@inheritDoc}
   */
  public function getAccessPeriod(): array {
    $access_period = $this->get('access_period')->getValue();
    if ($access_period) {
      $access_period = reset($access_period);
      if ($access_period['value'] && $access_period['end_value']) {
        return [
          'start_date' => strtotime($access_period['value']),
          'end_date' => strtotime($access_period['end_value']),
        ];
      }
    }
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getTimeLimit(): ?int {
    if ($this->get('time_limit')->getString()) {
      return (int) $this->get('time_limit')->getString();
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function randomizeQuestionSequence(): bool {
    return (bool) $this->get('randomize_question_sequence')->getString();
  }

  /**
   * Get questions by tags.
   *
   * @return array
   *   Array of questions.
   */
  private function getQuestionByTags(): array {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $questions = [];
    $tags = $this->getQuestionTags();

    if (empty($tags)) {
      return $questions;
    }

    $tag_ids = array_map(function ($tag) {
      return $tag->id();
    }, $tags);

    try {
      $query = $this->entityTypeManager()->getStorage('question')->getQuery();
      $question_ids = $query
        ->accessCheck(FALSE)
        ->condition('tag', $tag_ids, 'IN')
        ->execute();

      foreach ($question_ids as $question_id) {
        $question = Question::load($question_id);
        if ($question->hasTranslation($langcode)) {
          $questions[] = $question->getTranslation($langcode);
        }
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('quiz_maker')->error($e->getMessage());
    }

    return $questions;
  }

}
