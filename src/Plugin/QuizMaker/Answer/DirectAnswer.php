<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "direct_answer",
 *   label = @Translation("Direct answer"),
 *   description = @Translation("Direct answer.")
 * )
 */
class DirectAnswer extends QuestionAnswer {

  /**
   * {@inheritDoc}
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string {
    if ($response) {
      return $response?->getUserResponse();
    }
    return parent::getAnswer();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $responses = $response->getResponses();
    $answer_position = array_search($this->id(), $responses);
    $answer_original_position = $this->getAnswerOriginalWeight($response->getQuestion());
    if ($answer_position === $answer_original_position) {
      return self::CORRECT;
    }
    else {
      return self::IN_CORRECT;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function isAlwaysCorrect(): bool {
    return TRUE;
  }

}
