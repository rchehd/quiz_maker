<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Plugin\QuizMaker\Answer\MatchingAnswer;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "matching_question",
 *   label = @Translation("Matching question"),
 *   description = @Translation("Matching question."),
 * )
 */
class MatchingQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $questionResponse = NULL, bool $allow_change_response = TRUE): array {
    $answers = $this->get('field_answers')->referencedEntities();
    if ($answers) {
      $answer_form = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['matching-form']
        ]
      ];
      $answer_form['question_table'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['question-table']
        ]
      ];
      $answer_form['answer_table'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['answer-table']
        ]
      ];
      $answer_form['question_table']['form'] = $this->getMatchingTable($answers, 'getMatchingQuestion', $this->t('Question'), $allow_change_response);
      $answer_form['answer_table']['form'] = $this->getMatchingTable($answers, 'getMatchingAnswer', $this->t('Answer'), $allow_change_response);

      return $answer_form;
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function validateAnsweringForm(array &$form, FormStateInterface $form_state): void {
    // No need to validate, because answer will be gotten from default matching.
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    return [
      'response' => $form_state->getValue('question_table')
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function hasReferencedAnswers(): bool {
    return TRUE;
  }

  /**
   * Get Matching table.
   *
   * @param \Drupal\quiz_maker\Plugin\QuizMaker\Answer\MatchingAnswer[] $answers
   *   The Matching answers.
   * @param string $answer_function
   *   The matching answer function: 'getMatchingQuestion' or 'getMatchingAnswer'.
   * @param mixed $title
   *   The table title.
   *
   * @return array
   *   The table.
   */
  private function getMatchingTable(array $answers, string $answer_function, mixed $title, bool $allow_change_response = TRUE): array {
    $table = [
      '#type' => 'table',
      '#header' => [
        $title,
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
      '#disabled' => !$allow_change_response
    ];

    $i = 0;
    foreach ($answers as $answer) {
      $row = [
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $answer->{$answer_function}(),
        ],
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $answer->label()]),
          '#title_display' => 'invisible',
          '#default_value' => $i,
          '#attributes' => ['class' => ['table-sort-weight']],
        ],
        '#attributes' => [
          'class' => ['draggable'],
        ]
      ];
      $table[$answer->id()] = $row;
      $i++;
    }
    return $table;
  }

  /**
   * {@inheritDoc}
   */
  public function isResponseCorrect(array $response_data): bool {
    return FALSE;
  }

}
