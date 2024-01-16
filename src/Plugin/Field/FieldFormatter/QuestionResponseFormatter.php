<?php

namespace Drupal\quiz_maker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\Service\QuizHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Question Response Formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "question_response_formatter",
 *   label = @Translation("Question Response Formatter"),
 *   field_types = {"entity_reference"},
 * )
 */
final class QuestionResponseFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    protected QuizHelper $quizHelper,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('quiz_maker.quiz_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $setting = ['list_style' => 'Numeric with dot'];
    return $setting + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $elements['list_style'] = [
      '#type' => 'radios',
      '#title' => $this->t('List style'),
      '#options' => [
        'Number with dot' => $this->t('Number with dot (ex. "@example")', ['@example' => '1.']),
        'Number with bracket' => $this->t('Number with bracket (ex. "@example")', ['@example' => '1)']),
        'Letter with dot' => $this->t('Letter with dot (ex. "@example")', ['@example' => 'a.']),
        'Letter with bracket' => $this->t('Letter with bracket (ex. "@example")', ['@example' => 'a)']),
        'Dot' => $this->t('Dot (ex. "@example")', ['@example' => '•']),
      ],
      '#default_value' => $this->getSetting('list_style'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [
      $this->t('List style: @style', ['@style' => $this->getSetting('list_style')]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];
    foreach ($items as $delta => $item) {
      $response_id = $item->get('target_id')->getValue();
      $response = QuestionResponse::load($response_id);
      $question = $response->getQuestion();
      $element[$delta] = $this->quizHelper->getQuestionResultView(
        $question,
        $response,
        0,
        TRUE,
        $this->getSetting('list_style')
      );
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition): bool {
    return $field_definition->getName() === 'responses';
  }

}
