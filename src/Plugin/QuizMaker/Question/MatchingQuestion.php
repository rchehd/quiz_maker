<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "matching_question",
 *   label = @Translation("Matching question"),
 *   description = @Translation("Matching question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\Answer\MatchingAnswer",
 *   answer_plugin_id = "matching_answer",
 * )
 */
class MatchingQuestion extends Question {

  use StringTranslationTrait;


  public function buildAnswerForm(): array {
    $test = $this->getFieldDefinitions();
    $question_plugin_manager = \Drupal::service('plugin.manager.quiz_maker.question');
    $answer_plugin_manager = \Drupal::service('plugin.manager.quiz_maker.question_answer');
    $plugin_definitions = $question_plugin_manager->getDefinitions();
    $plugin_definitions2 = $answer_plugin_manager->getDefinitions();

//    $form = \Drupal::service('entity.form_builder')->getForm($entity);

    $test2 = $answer_plugin_manager->createInstance($plugin_definitions[$this->bundle()]['answer_plugin_id']);

    $form['answer_text'] = [
      '#type' => 'text_format',
      '#title' => 'Answer',
      '#format' => 'full_html',
      '#allowed_formats' => ['full_html'],
    ];

    return $form;
  }

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
