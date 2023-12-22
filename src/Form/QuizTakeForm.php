<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\QuizInterface;

/**
 * Class of Quiz take form.
 */
class QuizTakeForm extends FormBase {

  use StringTranslationTrait;

  protected int $question_number = 0;

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'quiz_maker_quiz_take_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, QuizInterface $quiz_maker_quiz = NULL) {
    $form['question'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'question'
      ],
    ];

    if ($quiz_maker_quiz) {
      $questions = $quiz_maker_quiz->getQuestions();

      /** @var \Drupal\quiz_maker\QuestionInterface $current_question */
      $current_question = $questions[$this->question_number];
      $form['question']['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $current_question->getQuestion(),
      ];

      $form['question']['answer_form'] = $current_question->getAnsweringForm();

      $form['question']['navigation']['actions'] = [
        '#type' => 'actions',
      ];

      if ($this->question_number < (count($questions) - 1)) {
        $form['question']['navigation']['actions']['next'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
          '#submit' => ['::getNextQuestion'],
          '#ajax' => [
            'callback' => '::updateQuestionForm',
            'wrapper' => 'question',
            'event' => 'click',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Go to the next question...'),
            ]
          ]
        ];
      }

      if ($this->question_number > 0) {
        $form['question']['navigation']['actions']['previous'] = [
          '#type' => 'submit',
          '#value' => $this->t('Previous'),
          '#submit' => ['::getPreviousQuestion'],
          '#ajax' => [
            'callback' => '::updateQuestionForm',
            'wrapper' => 'question',
            'event' => 'click',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Go to the next question...'),
            ],
          ],
        ];
      }

      if ($this->question_number === (count($questions) - 1)) {
        $form['question']['navigation']['actions']['finish'] = [
          '#type' => 'submit',
          '#value' => $this->t('Finish'),
          '#submit' => ['::submitForm'],
        ];
      }

    }
    else {
      $form['question']['error'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Quiz doesn\'t have th questions.')
      ];


    }
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * Get update question form ans save answer.
   *
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\quiz_maker\QuizInterface|null $quiz_maker_quiz
   *   The quiz.
   *
   * @return mixed
   *   The form array.
   */
  public function updateQuestionForm(array &$form, FormStateInterface $form_state, QuizInterface $quiz_maker_quiz = NULL) {
    return $form['question'];
  }

  /**
   * Get previous next via ajax.
   *
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function getNextQuestion(array &$form, FormStateInterface $form_state): void {
    $this->question_number++;
    $form_state->setRebuild(TRUE);
  }

  /**
   * Get previous question via ajax.
   *
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function getPreviousQuestion(array &$form, FormStateInterface $form_state): void {
    $this->question_number--;
    $form_state->setRebuild(TRUE);
  }

}