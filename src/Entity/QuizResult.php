<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
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
 *   },
 *   base_table = "quiz_result",
 *   admin_permission = "administer quiz_result types",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/quiz-result",
 *     "add-form" = "/quiz-result/add/{quiz_result_type}",
 *     "add-page" = "/quiz-result/add",
 *     "canonical" = "/quiz-result/{quiz_result}",
 *     "edit-form" = "/quiz-result/{quiz_result}/edit",
 *     "delete-form" = "/quiz-result/{quiz_result}/delete",
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

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuiz(): QuizInterface {
    return $this->get('field_quiz')->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getUser(): AccountInterface {
    return \Drupal::currentUser();
  }

  /**
   * {@inheritDoc}
   */
  public function getStatus(): string {
    return 'test';
  }

  /**
   * {@inheritDoc}
   */
  public function getPassRate(): ?int {
    return 0;
  }

  /**
   * {@inheritDoc}
   */
  public function getScore(): int {
    return 0;
  }

  /**
   * {@inheritDoc}
   */
  public function isPassed(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function setAnswer(QuestionAnswerInterface $answer): void {
    // TODO: Implement setAnswer() method.
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
  public static function getWorkflowId(QuizResultInterface $quiz_result) {
    return QuizResultType::load($quiz_result->bundle())->getWorkflowId();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponses(): ?array {
    if ($this->hasField('field_question_response')) {
      return $this->get('field_question_response')->referencedEntities();
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(QuestionInterface $question): ?QuestionResponseInterface {
    if ($this->hasField('field_question_response')) {
      $responses = $this->get('field_question_response')->referencedEntities();
      foreach ($responses as $response) {
        if ($response->get('question_id')->target_id === $question->id()) {
          return $response;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function addResponse(QuestionResponseInterface $response): void {
    $responses = $this->get('field_question_response')->referencedEntities();
    if ($responses) {
      $response_ids = array_map(function ($responses) {
        return $responses->id();
      }, $responses);
    }
    else {
      $response_ids = [];
    }
    $response_ids[] = $response->id();
    $this->set('field_question_response', $response_ids);
    $this->save();
  }

  /**
   * {@inheritDoc}
   */
  public function getActiveQuestion(): ?QuestionInterface {
    $responses = $this->get('field_question_response')->referencedEntities();
    $questions = $this->getQuiz()->getQuestions();
    $answered_question_ids = array_map(function ($responses) {
      return $responses->getQuestion()->id();
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
      return NULL;
    }
  }

}
