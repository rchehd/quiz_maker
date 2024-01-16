<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\QuestionAnswer;
use Drupal\quiz_maker\QuestionAnswerInterface;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;

/**
 * Class of QuizHelper service.
 */
class QuizHelper {

  use StringTranslationTrait;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Constructs a QuizManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    $this->logger = $loggerChannelFactory->get('quiz_maker');
  }

  /**
   * Get question result.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   * @param int $mark_mode
   *   The mark mode:
   *     - 0: 'correct/incorrect',
   *     - 1: 'chosen/not-chosen'.
   * @param bool $show_score
   *   TRUE when need to show score, otherwise FALSE.
   * @param string $list_style
   *   One of styles:
   *     - 'Number with bracket' (default),
   *     - 'Number with dot',
   *     - 'Letter with dot',
   *     - 'Letter with bracket',
   *     - 'Dot'.
   *
   * @return array
   *   The render array.
   */
  public function getQuestionResultView(QuestionInterface $question, QuestionResponseInterface $response, int $mark_mode = 0, bool $show_score = TRUE, string $list_style = 'Number with bracket'): array {
    $result_view = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['question-result']
      ]
    ];

    $result_view['question'] = [
      '#type' => 'html_tag',
      '#tag' => 'h4',
      '#value' => $question->getQuestion(),
    ];

    $result_view['answers'] = [
      '#type' => 'html_tag',
      '#tag' => 'ol',
      '#attributes' => [
        'class' => ['question-answers', $this->getListStyle($list_style)]
      ]
    ];

    $answers = $question->getAnswers();
    foreach ($answers as $answer) {
      if ($answer instanceof QuestionAnswerInterface) {
        $result_view['answers'][$answer->id()] = [
          '#type' => 'html_tag',
          '#tag' => 'li',
          '#value' => $answer->getAnswer(),
          '#attributes' => [
            'class' => match($mark_mode) {
              0 => [$answer->getResponseStatus($response)],
              1 => match ($answer->getResponseStatus($response)) {
                QuestionAnswer::CORRECT, QuestionAnswer::IN_CORRECT => ['chosen'],
                default => [],
              }
            }
          ]
        ];
      }
    }

    if ($show_score) {
      $result_view['score'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Score: @value', ['@value' => $response->getScore()]),
        '#attributes' => [
          'class' => ['question-score']
        ]
      ];
    }

    return $result_view;
  }

  /**
   * Get list style.
   *
   * @param string $style
   *   The style.
   *
   * @return string
   *   Style.
   */
  public function getListStyle(string $style): string {
    return match($style) {
      'Number with bracket' => 'response-list-number-with-bracket',
      'Number with dot' => 'response-list-number-with-dot',
      'Letter with dot' => 'response-list-letter-with-dot',
      'Letter with bracket' => 'response-list-letter-with-bracket',
      'Dot' => 'response-list-dot',
    };
  }

}
