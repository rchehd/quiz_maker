<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\QuizInterface;

class QuizQuestionsForm extends FormBase {

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'quiz_question_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, QuizInterface $quiz_maker_quiz = NULL) {
    $questions = $quiz_maker_quiz->getQuestions();
    $header = ['Question'];

    $header = array_merge($header, [
      'Revision',
      'Operations',
      'Weight',
      'Parent',
    ]);

    $rows = [];
    foreach ($questions as $question) {
      $rows[] = $question->label();
    }

    // Display questions in this quiz.
    $form['question_list'] = [
      '#type' => 'table',
      '#title' => $this->t('Questions in this @quiz', ['@quiz' => $quiz_maker_quiz->label()]),
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('There are currently no questions in this @quiz. Assign existing questions by using the question browser below. You can also use the links above to create new questions.', ['@quiz' => $quiz_maker_quiz->label()]),
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'qqr-pid',
          'source' => 'qqr-id',
          'hidden' => TRUE,
          'limit' => 1,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}