<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Question;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionPluginBase;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestion(
 *   id = "boolean_question",
 *   label = @Translation("Boolean question"),
 *   description = @Translation("Boolean question."),
 * )
 */
class BooleanQuestion extends QuestionPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getAnsweringForm(QuestionResponseInterface $question_response = NULL, bool $allow_change_response = TRUE): array {
    $answers = $this->getEntity()->getAnswers();
    if ($answers) {
      $options = [];
      foreach ($answers as $answer) {
        $options[$answer->id()] = $answer->getAnswer();
      }
      $response = $question_response?->getResponses();
      return [
        $this->getQuestionAnswerWrapperId() => [
          '#type' => 'radios',
          '#title' => $this->t('Select an answer'),
          '#options' => $options,
          '#default_value' => $response ? reset($response) : NULL,
          '#disabled' => !$allow_change_response
        ]
      ];
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultAnswersData(): array {
    return [
      [
        'label' => $this->t('True'),
        'answer' => $this->t('True'),
        'is_correct' => $this->isBooleanState(TRUE),
      ],
      [
        'label' => $this->t('False'),
        'answer' => $this->t('False'),
        'is_correct' => $this->isBooleanState(FALSE),
      ]
    ];
  }

  /**
   * Get answer state.
   *
   * @param bool $state
   *   The state: TRUE or FALSE.
   *
   * @return bool
   *   TRUE if it is current state, otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function isBooleanState(bool $state): bool {
    return $state === (bool) $this->getEntity()->get('field_boolean_state')->getString();
  }

}
