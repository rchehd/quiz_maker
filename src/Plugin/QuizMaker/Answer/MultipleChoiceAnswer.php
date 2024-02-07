<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\SimpleScoringAnswerInterface;
use Drupal\quiz_maker\Trait\SimpleScoringAnswerTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "multiple_choice_answer",
 *   label = @Translation("Multiple choice answer"),
 *   description = @Translation("Multiple choice answer.")
 * )
 */
class MultipleChoiceAnswer extends QuestionAnswer implements SimpleScoringAnswerInterface {

  use SimpleScoringAnswerTrait;

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $responses = $response->getResponses();
    if (in_array($this->id(), $responses) && $this->isCorrect()) {
      return self::CORRECT;
    }
    elseif (in_array($this->id(), $responses) && !$this->isCorrect()) {
      return self::IN_CORRECT;
    }

    return self::NEUTRAL;
  }

}
