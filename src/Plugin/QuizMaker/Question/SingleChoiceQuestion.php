<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "single_choice_question",
 *   label = @Translation("Single choice question"),
 *   description = @Translation("Single choice question."),
 * )
 */
class SingleChoiceQuestion extends QuestionPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    if ($answers = $this->getEntity()->getAnswers()) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      $default_answer = $question_response?->getResponses();
      if (!empty($default_answer)) {
        $default_answer = reset($default_answer);
      }
      return [
        $this->getQuestionAnswerWrapperId() => [
          '#type' => 'radios',
          '#title' => $this->t('Select an answer'),
          '#options' => $options,
          '#default_value' => $default_answer,
          '#disabled' => !$allow_change_response
        ]
      ];
    }

    return [];
  }

}
