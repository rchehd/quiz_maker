<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class of response plugin.
 */
abstract class QuestionResponsePluginBase extends PluginBase implements QuestionResponseInterface, ContainerFactoryPluginInterface {

  /**
   * The response entity.
   *
   * @var \Drupal\quiz_maker\Entity\QuestionResponse
   */
  protected QuestionResponse $entity;

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
    if (!isset($this->configuration['response_id'])) {
      throw new PluginException($this->t('Question response id wasn\'t found in plugin configuration'));
    }

    $this->entity = QuestionResponse::load($this->configuration['response_id']);
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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getEntity(): QuestionResponse {
    if (!isset($this->configuration['response_id'])) {
      throw new PluginException($this->t('Response id is not found in plugin configuration'));
    }

    return QuestionResponse::load($this->configuration['response_id']);
  }

  /**
   * {@inheritDoc}
   */
  public function setScore(QuestionInterface $question, bool $value, float $score = NULL, array $response_data = []): QuestionResponseInterface {
    if ($value) {
      $this->entity->set('score', $score ?? $question->getMaxScore());
    }
    else {
      $this->entity->set('score', 0);
    }

    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function setResponseData(array $data): QuestionResponseInterface {
    $this->entity->set('responses', $data);
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponses(): array {
    $result = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $responses = $this->entity->get('responses')->referencedEntities();
    foreach ($responses as $response) {
      if ($response->hasTranslation($langcode)) {
        $result[] = $response->getTranslation($langcode);
      }
    }
    if ($result) {
      return array_map(function ($result) {
        return $result->id();
      }, $result);
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): ?Question {
    /** @var \Drupal\quiz_maker\Entity\Question $entity */
    $entity = $this->entity->get('question_id')->entity;
    if ($entity->hasTranslation($this->languageManager->getCurrentLanguage()->getId())) {
      return $entity->getTranslation($this->languageManager->getCurrentLanguage()->getId());
    }
    return $entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuiz(): ?QuizInterface {
    /** @var \Drupal\quiz_maker\QuizInterface $entity */
    $entity = $this->entity->get('quiz_id')->entity;
    if ($entity->hasTranslation($this->languageManager->getCurrentLanguage()->getId())) {
      return $entity->getTranslation($this->languageManager->getCurrentLanguage()->getId());
    }
    return $entity;
  }

  /**
   * {@inheritDoc}
   */
  public function isCorrect(): bool {
    return $this->entity->get('is_correct')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getScore(): float {
    return $this->entity->get('score')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function setCorrect(bool $value): QuestionResponseInterface {
    $this->entity->set('is_correct', $value);
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function setQuiz(QuizInterface $quiz): QuestionResponseInterface {
    $this->entity->set('quiz_id', $quiz);
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function setQuestion(QuestionInterface $question): QuestionResponseInterface {
    $this->entity->set('question_id', $question);
    return $this->entity;
  }

}
