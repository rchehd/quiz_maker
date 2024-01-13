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
      $element[$delta] = $this->getQuestionResult($question, $response);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getName() === 'responses';
  }

  /**
   * Get question result.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   *
   * @return array
   *   The render array.
   */
  protected function getQuestionResult(QuestionInterface $question, QuestionResponseInterface $response): array {
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
        'class' => ['question-answers', $this->getListStyle()]
      ]
    ];

    $answers = $question->getAnswers();
    foreach ($answers as $answer) {
      if ($answer instanceof QuestionAnswerInterface) {
        $result_view['answers'][$answer->id()] = [
          '#type' => 'html_tag',
          '#tag' => 'li',
          '#value' => $answer->getAnswer(),
          '#attributes' => [
            'class' => [$answer->getResponseStatus($response)]
          ]
        ];
      }
    }

    $result_view['score'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Score: @value', ['@value' => $response->getScore()]),
      '#attributes' => [
        'class' => ['question-score']
      ]
    ];

    return $result_view;
  }

  /**
   * Get list style.
   *
   * @return string
   *   Style.
   */
  private function getListStyle(): string {
    $style = $this->getSetting('list_style');
    return match($style) {
      'Number with dot' => 'response-list-number-with-dot',
      'Number with bracket' => 'response-list-number-with-bracket',
      'Letter with dot' => 'response-list-letter-with-dot',
      'Letter with bracket' => 'response-list-letter-with-bracket',
      'Dot' => 'response-list-dot',
    };
  }

}
