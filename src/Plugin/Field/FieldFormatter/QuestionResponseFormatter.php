<?php

namespace Drupal\quiz_maker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

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
  public static function defaultSettings(): array {
    $setting = ['foo' => 'bar'];
    return $setting + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $elements['foo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foo'),
      '#default_value' => $this->getSetting('foo'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [
      $this->t('Foo: @foo', ['@foo' => $this->getSetting('foo')]),
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
      $element[$delta] = $this->getQuestionResult($question, $response);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getName() === 'field_question_response';
  }

  protected function getQuestionResult(QuestionInterface $question, QuestionResponseInterface $response): array {
    $answers = $question->getAnswers();
    $result_view = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['question-result']
      ]
    ];
    $result_view['question'] = [
      '#type' => 'html_tag',
      '#tag' => 'h4',
      '#value' => $question->getQuestion(),
    ];

    $result_view['answers'] = [
      '#type' => 'html_tag',
      '#tag' => 'ol',
      '#attributes' => [
        'class' => ['question-answers']
      ]
    ];


    foreach ($answers as $answer) {
      /** @var \Drupal\quiz_maker\QuestionAnswerInterface $answer */
      $result_view['answers'][$answer->id()] = [
        '#type' => 'html_tag',
        '#tag' => 'li',
        '#value' => $answer->getAnswer(),
        '#attributes' => [
          'class' => [$answer->getResponseStatus($response)]
        ]
      ];
    }

    $result_view['score'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Total score: @value', ['@value' =>  $response->getScore()]),
      '#attributes' => [
        'class' => ['question-score']
      ]
    ];

    return $result_view;
  }

}
