<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\quiz_maker\Entity\QuizResultType;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\quiz_maker\Service\QuizHelper;
use Drupal\quiz_maker\Service\QuizManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Quiz Maker form.
 */
class QuizResultReviewForm extends FormBase {

  /**
   * The quiz result.
   *
   * @var \Drupal\quiz_maker\QuizResultInterface
   */
  protected QuizResultInterface $quizResult;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Form constructor.
   *
   * @param \Drupal\quiz_maker\Service\QuizHelper $quizHelper
   *   The quiz helper service.
   * @param \Drupal\quiz_maker\Service\QuizManager $quizManager
   *   The quiz manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger factory.
   */
  public function __construct(
    protected QuizHelper $quizHelper,
    protected QuizManager $quizManager,
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    $this->logger = $loggerChannelFactory->get('quiz_maker');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('quiz_maker.quiz_helper'),
      $container->get('quiz_maker.quiz_manager'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'quiz_maker_quiz_result_review';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, QuizResultInterface $quiz_result = NULL): array {
    if ($quiz_result) {
      $this->quizResult = $quiz_result;

      $responses = $quiz_result->getResponses();
      foreach ($responses as $response) {
        $question = $response->getQuestion();

        $form[$question->id()] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['question-result-row']
          ]
        ];

        // Build user response.
        $form[$question->id()]['user_response'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['user-response']
          ]
        ];

        $form[$question->id()]['user_response']['question_response'] = $this->quizHelper->getQuestionResultView(
          $question,
          $response,
          1,
          FALSE
        );

        // Build correct answer list response.
        $form[$question->id()]['correct_answers'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['correct-answers']
          ]
        ];

        $correct_answers = $question->getCorrectAnswers();

        $form[$question->id()]['correct_answers']['title'] = [
          '#type' => 'html_tag',
          '#tag' => 'h4',
          '#value' => $this->t('Correct answers'),
        ];

        $form[$question->id()]['correct_answers']['answers'] = [
          '#type' => 'html_tag',
          '#tag' => 'ol',
          '#attributes' => [
            'class' => ['question-answers', $this->quizHelper->getListStyle('Dot')]
          ]
        ];

        foreach ($correct_answers as $correct_answer) {
          /** @var \Drupal\quiz_maker\QuestionAnswerInterface $correct_answer */
          $form[$question->id()]['correct_answers']['answers'][] = [
            '#type' => 'html_tag',
            '#tag' => 'li',
            '#value' => $correct_answer->getAnswer(),
          ];
        }

        // Build setting results values.
        $form[$question->id()]['results'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['quiz-result-form-data']
          ]
        ];

        $form[$question->id()]['results'][$question->id() . '_score'] = [
          '#type' => 'number',
          '#title' => $this->t('Score:'),
          '#description' => $this->t('<strong>Warning:</strong> you can\'t set the score more the max score of question - @score pts.', [
            '@score' => $question->getMaxScore(),
          ]),
          '#default_value' => (bool) $response->getScore() ? $response->getScore() : $question->getMaxScore(),
          '#min' => 0,
          '#max' => $question->getMaxScore(),
        ];

        $form[$question->id()]['results'][$question->id() . '_is_correct'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Correct'),
          '#default_value' => $response->isCorrect() ?? FALSE,
        ];

      }

    }

    $form['passed_check'] = [
      '#type' => 'radios',
      '#title' => $this->t('Passing check'),
      '#options' => [
        'automatically' => $this->t('Automatically (Pass rate - @pass_rate%)', [
          '@pass_rate' => $this->quizResult->getQuiz()->getPassRate()
        ]),
        'manually' => $this->t('Manually (forced)'),
      ],
      '#default_value' => 'automatically',
    ];

    $form['passed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Passed'),
      '#states' => [
        'visible' => [
          ':input[name="passed_check"]' => ['value' => 'manually'],
        ],
      ],
      '#default_value' => FALSE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Complete review'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $responses = $this->quizResult->getResponses();
    // Update all responses.
    foreach ($responses as $response) {
      /** @var \Drupal\quiz_maker\QuestionResponseInterface $response */
      $question = $response->getQuestion();
      $is_correct = $form_state->getValue($question->id() . '_is_correct');
      $score = $form_state->getValue($question->id() . '_score');
      try {
        $response
          ->setCorrect($is_correct)
          ->setScore($question, $is_correct, $score)
          ->save();
      }
      catch (EntityStorageException $e) {
        $this->logger->error($e->getMessage());
      }
    }
    // Update quiz result data.
    $passed_check = $form_state->getValue('passed_check');
    $new_score = $this->quizManager->calculateScore($this->quizResult);
    $this->quizResult->setScore($new_score);
    if ($passed_check === 'manually') {
      $passed = $form_state->getValue('passed');
      $this->quizResult->setPassed($passed);
    }
    else {
      $this->quizResult->setPassed($new_score >= $this->quizResult->getQuiz()->getPassRate());
    }

    try {
      $this->quizResult->setState(QuizResultType::EVALUATED)->save();
    }
    catch (EntityStorageException $e) {
      $this->logger->error($e->getMessage());
    }

    $form_state->setRedirect('view.quiz_results_on_review.result_list');
  }

}
