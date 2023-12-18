<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "single_question",
 *   label = @Translation("Single question"),
 *   description = @Translation("Single question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswer\SingleQuestionAnswer",
 *   answer_plugin_id = "single_question_answer",
 * )
 */
class SingleQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    $form['answer'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an answer'),
      '#options' => [
        'option_1' => $this->t('Option 1'),
        'option_2' => $this->t('Option 2'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitAnswer(array &$form, FormStateInterface $form_state): mixed {
    $test = self::get('answer_plugin_id');
    return $form_state->getValue('answer');
  }

}
