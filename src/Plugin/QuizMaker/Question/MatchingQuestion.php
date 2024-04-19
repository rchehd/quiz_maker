<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Plugin\QuizMaker\Answer\MatchingAnswer;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\SimpleScoringQuestionInterface;
use Drupal\quiz_maker\Trait\SimpleScoringQuestionTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "matching_question",
 *   label = @Translation("Matching question"),
 *   description = @Translation("Matching question."),
 * )
 */
class MatchingQuestion extends QuestionPluginBase implements SimpleScoringQuestionInterface {

  use StringTranslationTrait;
  use SimpleScoringQuestionTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    $answers = $this->getEntity()->getAnswers();
    if ($answers) {
      $answer_form = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['matching-form']
        ]
      ];

      // To build answer form we have to set unique names to get right response
      // in form submit, because quiz could have several question of the same type.
      $answer_form['question_table_' . $this->getEntity()->id()] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['question-table']
        ]
      ];

      $answer_form['answer_table_' . $this->getEntity()->id()] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['answer-table']
        ]
      ];
      // The column of questions (non-draggable).
      $answer_form['question_table_' . $this->getEntity()->id()]['question_column_' . $this->getEntity()->id()] = $this->getMatchingTable($answers, 'getMatchingQuestion', $this->t('Question'), FALSE, FALSE);

      // The matching column of answers(draggable).
      // If question already has response - get answers from response,
      // otherwise get original answers and shuffle it.
      if ($question_response) {
        $answers = $this->entityTypeManager->getStorage('question_answer')->loadMultiple($question_response->getResponses());
        // When we get any error and answer array is empty.
        if (!$answers) {
          $answers = $this->getEntity()->getAnswers();
          shuffle($answers);
        }
      }
      else {
        $answers = $this->getEntity()->getAnswers();
        shuffle($answers);
      }

      $answer_form['answer_table_' . $this->getEntity()->id()][$this->getQuestionAnswerWrapperId()] = $this->getMatchingTable($answers, 'getMatchingAnswer', $this->t('Answer'), $allow_change_response);

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
    $responses = parent::getResponse($form, $form_state);
    if (!$responses) {
      return [];
    }
    return array_keys($responses);
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
   * @param bool $allow_change_response
   *   Allow to change response.
   * @param bool $draggable
   *   TRUE if table should be draggable.
   *
   * @return array
   *   The table.
   */
  private function getMatchingTable(array $answers, string $answer_function, mixed $title, bool $allow_change_response = TRUE, bool $draggable = TRUE): array {
    $table = [
      '#type' => 'table',
      '#header' => [
        $title,
      ],
      '#disabled' => !$allow_change_response
    ];

    if ($draggable && $allow_change_response) {
      $table['#header'][] = $this->t('Weight');
      $table['#tabledrag'] = [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ];
    }

    $i = 0;
    foreach ($answers as $answer) {
      $row = [
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $answer->{$answer_function}(),
        ],
        '#attributes' => [
          'class' => ['draggable'],
        ]
      ];
      if ($draggable) {
        $row['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $answer->label()]),
          '#title_display' => 'invisible',
          '#default_value' => $i,
          '#attributes' => ['class' => ['table-sort-weight']],
        ];
      }
      $table[$answer->id()] = $row;
      $i++;
    }

    return $table;
  }

}
