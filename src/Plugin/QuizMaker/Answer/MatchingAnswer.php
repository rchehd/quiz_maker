<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\Plugin\QuizMaker\QuestionAnswerPluginBase;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\SimpleScoringAnswerInterface;
use Drupal\quiz_maker\Trait\SimpleScoringAnswerTrait;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "matching_answer",
 *   label = @Translation("Matching answer"),
 *   description = @Translation("Matching answer.")
 * )
 */
class MatchingAnswer extends QuestionAnswerPluginBase implements SimpleScoringAnswerInterface {

  use SimpleScoringAnswerTrait;

  /**
   * {@inheritDoc}
   */
  public function isAlwaysCorrect(): bool {
    return TRUE;
  }

  /**
   * Get matching question.
   *
   * @return string
   *   The matching question.
   */
  public function getMatchingQuestion(): string {
    return $this->entity->get('field_matching_question')->value;
  }

  /**
   * Get matching answer.
   *
   * @return string
   *   The matching answer.
   */
  public function getMatchingAnswer(): string {
    return $this->entity->get('field_matching_answer')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getResponseStatus(QuestionResponseInterface $response): string {
    $responses = $response->getResponses();
    if (!$responses) {
      return QuestionAnswer::NEUTRAL;
    }
    $answer_position = array_search($this->entity->id(), $responses);
    $answer_original_position = $this->getAnswerOriginalWeight($response->getQuestion());
    if ($answer_position === $answer_original_position) {
      return QuestionAnswer::CORRECT;
    }
    else {
      return QuestionAnswer::IN_CORRECT;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getAnswer(QuestionResponseInterface $response = NULL): ?string {
    $matching_question = $this->getMatchingQuestion();
    if ($response) {
      $chosen_matching_answer = $this->getChosenMatchingAnswer($response->getQuestion(), $response->getResponses());
    }
    else {
      $chosen_matching_answer = $this->getMatchingAnswer();
    }

    $string = [
      '#theme' => 'question_matching_answer',
      '#matching_question' => $matching_question,
      '#matching_answer' => $chosen_matching_answer,
    ];

    return $this->renderer->render($string);

  }

  /**
   * Get chose answer in question.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   * @param array $response_answers
   *   The array of answers of response.
   *
   * @return string|null
   *   The matching answer.
   */
  private function getChosenMatchingAnswer(QuestionInterface $question, array $response_answers): ?string {
    $answer_number = $this->getAnswerOriginalWeight($question);
    if (isset($response_answers[$answer_number])) {
      $answer_id = $response_answers[$answer_number];
      foreach ($question->getAnswers() as $answer) {
        /** @var \Drupal\quiz_maker\Entity\QuestionAnswer $answer */
        $answer_plugin = $answer->getPluginInstance();
        if ($answer->id() === $answer_id && $answer_plugin instanceof MatchingAnswer) {
          return $answer_plugin->getMatchingAnswer();
        }
      }
    }
    return NULL;
  }

  /**
   * Get original answer weight.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   *
   * @return int
   *   The weight.
   */
  private function getAnswerOriginalWeight(QuestionInterface $question): int {
    $answers = $question->getAnswers();
    $answer_ids = array_map(function ($answer) {
      return $answer->id();
    }, $answers);
    return array_search($this->entity->id(), $answer_ids);
  }

}
