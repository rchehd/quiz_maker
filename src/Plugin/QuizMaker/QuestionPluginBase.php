<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Entity\QuestionType;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class of question plugin.
 */
abstract class QuestionPluginBase extends PluginBase implements QuestionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The answer entity.
   *
   * @var ?\Drupal\quiz_maker\Entity\Question
   */
  protected ?Question $entity;

  /**
   * Constructs a new Question.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (is_string($this->configuration['question'])) {
      $this->entity = Question::load($this->configuration['question']);
    }
    else {
      $this->entity = $this->configuration['question'] ?? NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity(): QuestionInterface {
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestionAnswerWrapperId(): string {
    return $this->entity->getAnswerType() . '_' . $this->entity->id();
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): ?string {
    return $this->entity->get('question')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswers(): ?array {
    $result = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $answers = $this->entity->get('answers')->referencedEntities();
    foreach ($answers as $answer) {
      if ($answer->hasTranslation($langcode)) {
        $result[] = $answer->getTranslation($langcode);
      }
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function getCorrectAnswers(): array {
    $answers = $this->getAnswers();
    return array_filter($answers, function ($answer) {
      return $answer->isCorrect();
    });
  }

  /**
   * {@inheritDoc}
   */
  public function getMaxScore(): int {
    return $this->entity->get('max_score')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getTag(): ?TermInterface {
    if ($this->entity->get('tag')->entity instanceof TermInterface) {
      return $this->entity->get('tag')->entity;
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function addAnswer(QuestionAnswerInterface $answer): void {
    $answers = $this->getAnswers();
    if ($answers) {
      $answer_ids = array_map(function ($answer) {
        return $answer->id();
      }, $answers);
    }
    else {
      $answer_ids = [];
    }

    if ($answer instanceof EntityInterface && !in_array($answer->id(), $answer_ids)) {
      $answer_ids[] = $answer->id();
      $this->entity->set('answers', $answer_ids);
    }

  }

  /**
   * {@inheritDoc}
   */
  public function isEnabled(): bool {
    return (bool) $this->entity->get('status')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseType(): ?string {
    $question_type = $this->entity->getEntityType();
    if ($question_type instanceof QuestionType) {
      return $question_type->getResponseType();
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswerType(): ?string {
    $question_type = $this->entity->getEntityType();
    if ($question_type instanceof QuestionType) {
      return $question_type->getAnswerType();
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultAnswersData(): array {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void {
    $question_form_id = $this->getQuestionAnswerWrapperId();
    if (!$form_state->getValue($question_form_id)) {
      $form_state->setErrorByName($question_form_id, t('Choose the answer, please.'));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    $question_form_id = $this->getQuestionAnswerWrapperId();
    $response = $form_state->getValue($question_form_id);
    if ($response) {
      return is_array($response) ? $response : [$response];
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $answers_ids): bool {
    $correct_answers = $this->getEntity()->getCorrectAnswers();
    $correct_answers_ids = array_map(function ($correct_answer) {
      /** @var \Drupal\Core\Entity\EntityInterface $correct_answer */
      return $correct_answer->id();
    }, $correct_answers);
    return array_map('intval', $correct_answers_ids) === array_map('intval', $answers_ids);
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseView(QuestionResponseInterface $response, int $mark_mode = 0): array {
    $result = [];
    $answers = $this->getEntity()->getAnswers();
    // Return list of answers with related class.
    foreach ($answers as $answer) {
      if ($answer instanceof QuestionAnswerInterface && $answer instanceof EntityInterface) {
        $result[$answer->id()] = [
          '#type' => 'html_tag',
          '#tag' => $answer->getViewHtmlTag(),
          '#value' => $answer->getAnswer($response),
          '#attributes' => [
            'class' => match($mark_mode) {
              default => [$answer->getResponseStatus($response)],
              1 => match ($answer->getResponseStatus($response)) {
                QuestionAnswer::CORRECT, QuestionAnswer::IN_CORRECT => ['chosen'],
                default => [],
              }
            }
          ]
        ];
      }
    }
    return $result;
  }

}
