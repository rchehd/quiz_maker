<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\taxonomy\TermInterface;

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
   *   The question plugin instance.
   * @param \Drupal\quiz_maker\QuestionResponseInterface $response
   *   The response.
   * @param int $mark_mode
   *   The mark mode:
   *     - 0: 'correct/incorrect',
   *     - 1: 'chosen/not-chosen'.
   * @param bool $show_score
   *   TRUE when need to show score, otherwise FALSE.
   * @param ?int $list_style
   *   One of styles:
   *     - 0: 'Number with bracket',
   *     - 1: 'Number with dot',
   *     - 2: 'Letter with dot',
   *     - 3: 'Letter with bracket',
   *     - 4: 'Dot'.
   *
   * @return array
   *   The render array.
   */
  public function getQuestionResultView(QuestionInterface $question, QuestionResponseInterface $response, int $mark_mode = 0, bool $show_score = TRUE, int $list_style = NULL): array {
    $result_view = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['question-result'],
      ],
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
        'class' => ['question-answers', $this->getListStyle($list_style)],
      ],
    ];

    $result_view['answers'][] = $question->getResponseView($response, $mark_mode);

    if ($show_score) {
      $result_view['score'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Score: @value', ['@value' => $response->getScore()]),
        '#attributes' => [
          'class' => ['question-score'],
        ],
      ];
    }

    return $result_view;
  }

  /**
   * Get list style.
   *
   * @param ?int $style
   *   The style.
   *
   * @return string
   *   Style.
   */
  public function getListStyle(int $style = NULL): string {
    return match($style) {
      0 => 'response-list-number-with-bracket',
      2 => 'response-list-number-with-dot',
      3 => 'response-list-letter-with-dot',
      4 => 'response-list-letter-with-bracket',
      5 => 'response-list-dot',
      default => 'response-list-non-style'
    };
  }

  /**
   * Update all quizzes which contain question tag.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The tag.
   */
  public function updateQuizzesWithTag(TermInterface $term): void {
    try {
      $quizzes = $this->entityTypeManager->getStorage('quiz')->loadMultiple();
      /** @var \Drupal\quiz_maker\QuizInterface $quiz */
      foreach ($quizzes as $quiz) {
        $quiz_terms = $quiz->getQuestionTags();
        $quiz_term_ids = array_map(function ($quiz_term) {
          return $quiz_term->id();
        }, $quiz_terms);
        if (in_array($term->id(), $quiz_term_ids)) {
          $quiz->save();
        }
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
