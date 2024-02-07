<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Plugin\QuizMaker\Response\DirectResponse;
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
    if ($response instanceof DirectResponse) {
      return $response->getUserResponse() ?? t('Empty answer');
    }
    return parent::getAnswer();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    if (!$response->getResponses()) {
      return self::NEUTRAL;
    }
    $question = $response->getQuestion();
    if ($question->isResponseCorrect($response->getResponses())) {
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

  /**
   * {@inheritDoc}
   */
  public function getViewHtmlTag(): string {
    return 'div';
  }

}
