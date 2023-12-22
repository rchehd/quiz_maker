<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;

/**
 * Plugin implementation of the quiz_maker_question.
 *
 * @QuizMakerQuestion(
 *   id = "multiple_choice_question",
 *   label = @Translation("Multiple question"),
 *   description = @Translation("Multiple question."),
 *   answer_class = "\Drupal\quiz_maker\Plugin\QuizMaker\Answer\MultipleChoiceAnswer",
 *   answer_plugin_id = "multiple_choice_answer",
 * )
 */
class MultipleChoiceQuestion extends Question {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(): array {
    $answers = $this->get('field_answers')->referencedEntities();
    if ($answers) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      return [
        '#type' => 'checkboxes',
        '#title' => $this->t('Select an answer'),
        '#options' => $options,
      ];
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function submitAnswer(array &$form, FormStateInterface $form_state): mixed {
    $test = self::get('answer_plugin_id');
    return $form_state->getValue('answer');
  }

  public function hasReferencedAnswers(): bool {
    return TRUE;
  }

}
