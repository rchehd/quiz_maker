<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class of question plugin.
 */
abstract class QuestionPluginBase extends PluginBase implements QuestionPluginInterface, ContainerFactoryPluginInterface {

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  public function getEntity(): QuestionInterface {
    if (!isset($this->configuration['question_id'])) {
      throw new PluginException($this->t('Question not fount in plugin configuration'));
    }

    return Question::load($this->configuration['question_id']);
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestionAnswerWrapperId(): string {
    return $this->getEntity()->getAnswerType() . '_' . $this->getEntity()->id();
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
    if ($this->getEntity() instanceof QuestionInterface) {
      $question_form_id = $this->getQuestionAnswerWrapperId();
      if (!$form_state->getValue($question_form_id)) {
        $form_state->setErrorByName($question_form_id, t('Choose the answer, please.'));
      }
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
      if ($answer instanceof QuestionAnswerInterface) {
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
