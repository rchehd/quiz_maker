<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class of answer plugin.
 */
abstract class QuestionAnswerPluginBase extends PluginBase implements QuestionAnswerPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The answer.
   *
   * @var ?\Drupal\quiz_maker\Entity\QuestionAnswer
   */
  protected ?QuestionAnswer $entity;

  /**
   * Constructs a new Answer.
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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LanguageManagerInterface $languageManager,
    protected RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (is_string($this->configuration['answer'])) {
      $this->entity = QuestionAnswer::load($this->configuration['answer']);
    }
    else {
      $this->entity = $this->configuration['answer'] ?? NULL;
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('renderer'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity(): QuestionAnswerInterface {
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string {
    return $this->entity->get('answer')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function isAlwaysCorrect(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isAlwaysInCorrect(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getViewHtmlTag(): string {
    return 'li';
  }

  /**
   * {@inheritDoc}
   */
  public function isCorrect(): bool {
    return (bool) $this->entity->get('is_correct')->getString();
  }

  /**
   * {@inheritDoc}
   */
  public function setCorrect(bool $value): void {
    $this->entity->set('is_correct', $value);
  }

}
