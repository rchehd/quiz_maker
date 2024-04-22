<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Entity\QuestionResponse;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswerPluginBase;
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
class DirectAnswer extends QuestionAnswerPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string {
    if ($response instanceof QuestionResponse && $response->getPluginInstance() instanceof DirectResponse) {
      return $response->getPluginInstance()->getUserResponse() ?? t('Empty answer');
    }
    return parent::getAnswer();
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    if (!$response->getResponses()) {
      return QuestionAnswer::NEUTRAL;
    }
    $question = $response->getQuestion();
    if ($question->isResponseCorrect($response->getResponses())) {
      return QuestionAnswer::CORRECT;
    }
    else {
      return QuestionAnswer::IN_CORRECT;
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
