<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\SimpleScoringQuestionInterface;
use Drupal\quiz_maker\Trait\SimpleScoringQuestionTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "multiple_choice_question",
 *   label = @Translation("Multiple question"),
 *   description = @Translation("Multiple question."),
 *   answer_bundle = "multiple_choice_answer",
 *   response_bundle = "multiple_choice_response",
 * )
 */
class MultipleChoiceQuestion extends Question implements SimpleScoringQuestionInterface {

  use StringTranslationTrait;
  use SimpleScoringQuestionTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    if ($answers = $this->getAnswers()) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      return [
        $this->getQuestionAnswerWrapperId() => [
          '#type' => 'checkboxes',
          '#title' => $this->t('Select an answer'),
          '#options' => $options,
          '#default_value' => $question_response?->getResponses() ?? [],
          '#disabled' => !$allow_change_response
        ]
      ];
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getResponse(array &$form, FormStateInterface $form_state): array {
    $responses = parent::getResponse($form, $form_state);
    if (!$responses) {
      return [];
    }
    $responses = array_filter($responses, function ($response) {
      return $response != 0;
    });
    return array_values($responses);
  }

}
