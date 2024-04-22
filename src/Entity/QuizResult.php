<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the quiz result entity class.
 *
 * @ContentEntityType(
 *   id = "quiz_result",
 *   label = @Translation("Quiz Result"),
 *   label_collection = @Translation("Quiz Results"),
 *   label_singular = @Translation("quiz result"),
 *   label_plural = @Translation("quiz results"),
 *   label_count = @PluralTranslation(
 *     singular = "@count quiz results",
 *     plural = "@count quiz results",
 *   ),
 *   bundle_label = @Translation("Quiz Result type"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuizResultForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuizResultForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *   },
 *   base_table = "quiz_result",
 *   data_table = "quiz_result_field_data",
 *   admin_permission = "administer quiz_result types",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "langcode" = "langcode",
 *     "label" = "label",
 *     "state" = "state",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/quiz-result",
 *     "add-form" = "/admin/quiz_maker/quiz-result/add/{quiz_result_type}",
 *     "add-page" = "/admin/quiz_maker/quiz-result/add",
 *     "canonical" = "/quiz-result/{quiz_result}",
 *     "edit-form" = "/admin/quiz_maker/quiz-result/{quiz_result}/edit",
 *     "delete-form" = "/admin/quiz_maker/quiz-result/{quiz_result}/delete",
 *     "delete-multiple-form" = "/admin/content/quiz-result/delete-multiple",
 *   },
 *   bundle_entity_type = "quiz_result_type",
 *   field_ui_base_route = "entity.quiz_result_type.edit_form",
 * )
 */
class QuizResult extends ContentEntityBase implements QuizResultInterface {

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
      ->setTranslatable(TRUE)
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
      ->setLabel(t('Description'))
      ->setTranslatable(TRUE)
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

    $fields['responses'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question response'))
      ->setSetting('target_type', 'question_response')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'question_response_formatter',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['quiz'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Quiz'))
      ->setSetting('target_type', 'quiz')
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The quiz result state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'state_transition_form',
        'settings' => [
          'require_confirmation' => TRUE,
          'use_modal' => TRUE,
        ],
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setSetting('workflow_callback', ['\Drupal\quiz_maker\Entity\QuizResult', 'getWorkflowId']);

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

    $fields['attempt'] = BaseFieldDefinition::create('integer')
      ->setLabel('Attempt')
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
      ->setLabel(t('Quiz started time'))
      ->setDescription(t('The time that the quiz result was created.'))
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

    $fields['finished'] = BaseFieldDefinition::create('timestamp')
      ->setLabel('Quiz finished time')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the quiz result was last edited.'));

    $fields['passed'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Passed'))
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

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuiz(): QuizInterface {
    /** @var \Drupal\quiz_maker\QuizInterface $quiz */
    $quiz = $this->get('quiz')->entity;
    return $quiz;
  }

  /**
   * {@inheritDoc}
   */
  public function getUser(): AccountInterface {
    /** @var \Drupal\Core\Session\AccountInterface $user */
    $user = $this->get('uid')->entity;
    return $user;
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
  public function setScore(int $score): QuizResultInterface {
    $this->set('score', $score);
    return $this;
  }

  /**
   * Gets the workflow ID for the state field.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $quiz_result
   *   The order.
   *
   * @return string
   *   The workflow ID.
   */
  public static function getWorkflowId(QuizResultInterface $quiz_result): string {
    return QuizResultType::load($quiz_result->bundle())->getWorkflowId();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponses(): array {
    $result = [];
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $responses = $this->get('responses')->referencedEntities();
    foreach ($responses as $response) {
      if ($response->hasTranslation($langcode)) {
        $result[] = $response;
      }
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(QuestionInterface $question): ?QuestionResponseInterface {
    /** @var \Drupal\quiz_maker\Entity\Question $question */
    if ($this->hasField('responses')) {
      $responses = $this->get('responses')->referencedEntities();
      foreach ($responses as $response) {
        if ($response->get('question_id')->target_id === $question->id()) {
          $langcode = $question->language()->getId();
          if ($response->hasTranslation($langcode)) {
            return $response->getTranslation($langcode);
          }
          return $response;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getActiveQuestion(array $questions): ?QuestionInterface {
    $responses = $this->getResponses();
    $answered_question_ids = array_map(function ($responses) {
      /** @var \Drupal\quiz_maker\Entity\Question $question */
      $question = $responses->getQuestion();
      return $question->id();
    }, $responses);
    if (!$responses) {
      return reset($questions);
    }
    else {
      foreach ($questions as $question) {
        if (!in_array($question->id(), $answered_question_ids)) {
          return $question;
        }
      }
      return reset($questions);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getState(): string {
    return $this->get('state')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function setPassed(bool $is_passed): QuizResultInterface {
    $this->set('passed', $is_passed ? 1 : 0);
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function setState(string $state): QuizResultInterface {
    $this->set('state', $state);
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function setFinishedTime(int $timestamp): QuizResultInterface {
    $this->set('finished', $timestamp);
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function isPassed(): bool {
    return (bool) $this->get('passed')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function addResponse(QuestionResponseInterface $response): QuizResultInterface {
    /** @var \Drupal\quiz_maker\Entity\QuestionResponse $response */
    $responses = $this->getResponses();
    if ($responses) {
      $response_ids = array_map(function ($response) {
        /** @var \Drupal\quiz_maker\Entity\QuestionResponse $response */
        return $response->id();
      }, $responses);
    }
    else {
      $response_ids = [];
    }
    $response_ids[] = $response->id();
    $this->set('responses', $response_ids);
    return $this;
  }

}
