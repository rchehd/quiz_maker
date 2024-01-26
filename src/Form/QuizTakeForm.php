<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Radios;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\quiz_maker\Entity\QuizResultType;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Drupal\quiz_maker\Service\QuizManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class of Quiz take form.
 */
class QuizTakeForm extends FormBase {

  use StringTranslationTrait;

  /**
   * Current question number.
   *
   * @var int
   */
  public int $questionNumber;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected ?Request $currentRequest;

  /**
   * Quiz result.
   *
   * @var \Drupal\quiz_maker\QuizResultInterface|null
   */
  protected ?QuizResultInterface $quizResult;

  /**
   * Time when quiz was started.
   *
   * @var int
   *   The timestamp.
   */
  protected int $started;

  /**
   * User quiz tempstore.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected PrivateTempStore $userQuizTempstore;

  /**
   * Form constructor.
   */
  public function __construct(
    RequestStack $requestStack,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QuizManager $quizManager,
    protected AccountInterface $currentUser,
    protected TimeInterface $time,
    protected PrivateTempStoreFactory $privateTempStoreFactory
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->questionNumber = -1;
    $this->started = $this->time->getCurrentTime();
    $this->userQuizTempstore = $this->privateTempStoreFactory->get('user_quiz_tempstore');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('quiz_maker.quiz_manager'),
      $container->get('current_user'),
      $container->get('datetime.time'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'quiz_take_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, QuizInterface $quiz = NULL) {
    // Create or get draft quiz result.
    $this->quizResult = $this->quizManager->startQuiz($this->currentUser, $quiz);

    $form['question'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'question'
      ],
      '#weight' => 1,
    ];

    if ($quiz) {
      $questions = $quiz->getQuestions();
      $time_limit = $quiz->getTimeLimit();

      if ($time_limit) {
        $end_time = (int) $this->quizResult->get('created')->value + $time_limit;
        $time_left = $end_time - $this->time->getCurrentTime();
        $form['timer_block'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'quiz-timer',
            'class' => ['quiz-timer']
          ],
          '#weight' => 0,
        ];

        $form['timer_block']['label'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t('Time left:')
        ];

        $form['timer_block']['value'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => gmdate('H:i:s', $time_left),
          '#attributes' => [
            'id' => ['quiz-timer-value']
          ],
        ];

        $form['#attached']['drupalSettings']['time_limit'] = $time_limit * 1000;
        $form['#attached']['drupalSettings']['started_time'] = (int) $this->quizResult->get('created')->value * 1000;
        $form['#attached']['library'][] = 'quiz_maker/quiz_timer';
      }

      $form_input = $form_state->getUserInput();

      if ($this->questionNumber == -1) {
        // When user open form - it will get the last active question if quiz
        // wasn't finished before.
        $active_question = $this->quizResult->getActiveQuestion();
        /** @var \Drupal\quiz_maker\QuestionInterface $current_question */
        $current_question = $active_question;
        $this->questionNumber = array_search($active_question, $questions);
      }
      elseif (isset($form_input['question_navigation']) && $form_input['question_navigation'] != NULL) {
        $current_question = $questions[(int) $form_input['question_navigation']];
        $this->questionNumber = (int) $form_input['question_navigation'];
      }
      else {
        $current_question = $questions[$this->questionNumber];
      }

      $form['question']['number'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Question @current/@all', [
          '@current' => $this->questionNumber + 1,
          '@all' => count($questions)
        ]),
        '#attributes' => [
          'class' => 'question-number'
        ]
      ];


      $options = [];
      $i = 1;
      foreach ($questions as $question) {
        $options[] = $i;
        $i++;
      }

      $form['question']['question_navigation'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#default_value' => $this->questionNumber,
        '#prefix' => '<div class="question-navigation">',
        '#suffix' => '</div>',
        '#ajax' => [
          'callback' => '::getQuestion',
          'wrapper' => 'question',
          'progress' => 'none',
        ],
        '#disabled' => !$quiz->allowJumping(),
      ];

      if (!$quiz->requireManualAssessment()) {
        // Add class to question number if it has response.
        $form['question']['question_navigation']['#after_build'] = ['::getQuestionNumberClass'];
      }


      $form['question']['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $current_question->getQuestion(),
      ];

      $current_question_response = $this->quizResult->getResponse($current_question);
      $allow_change_response = $quiz->allowChangeAnswer() || !$this->quizResult->getResponse($current_question);
      $form['question']['answer_form'] = $current_question->getAnsweringForm($current_question_response, $allow_change_response);

      $form['question']['navigation']['actions'] = [
        '#type' => 'actions',
      ];

      if ($this->questionNumber < (count($questions) - 1)) {
        $form['question']['navigation']['actions']['next'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
          '#submit' => ['::getNextQuestion'],
          '#ajax' => [
            'callback' => '::updateQuestionForm',
            'wrapper' => 'question',
            'event' => 'click',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Go to the next question...'),
            ]
          ]
        ];
      }

      if ($this->questionNumber > 0 && $quiz->allowBackwardNavigation()) {
        $form['question']['navigation']['actions']['previous'] = [
          '#type' => 'submit',
          '#value' => $this->t('Previous'),
          '#submit' => ['::getPreviousQuestion'],
          '#ajax' => [
            'callback' => '::updateQuestionForm',
            'wrapper' => 'question',
            'event' => 'click',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Go to the next question...'),
            ],
          ],
          '#limit_validation_errors' => [],
        ];
      }

      $form['question']['navigation']['actions']['finish'] = [
        '#type' => 'submit',
        '#value' => $this->t('Finish'),
        '#submit' => ['::submitForm'],
        '#attributes' => [
          'id' => ['quiz-finish']
        ],
      ];

      if ($this->questionNumber != (count($questions) - 1)) {
        $form['question']['navigation']['actions']['finish']['#attributes']['class'] = [
          'hidden'
        ];
      }

    }
    else {
      $form['question']['error'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Quiz doesn\'t have th questions.')
      ];

    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, QuizInterface $quiz = NULL) {
    $current_question = $this->getCurrentQuestion();
    $response_data = $current_question->getResponse($form, $form_state);
    if ($response_data) {
      $this->quizManager->updateQuiz($this->quizResult, $current_question, $response_data);
      $this->quizManager->finishQuiz($this->quizResult);
    }

    $form_state->setRedirect('entity.quiz_result.canonical', [
      'quiz_result' => $this->quizResult->id(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\quiz_maker\QuestionInterface $current_question */
    $current_question = $this->getCurrentQuestion();
    $quiz = $this->quizResult->getQuiz();
    if (!$quiz->allowSkipping()) {
      $current_question->validateAnsweringForm($form, $form_state);
    }

  }

  /**
   * Get update question form ans save answer.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function updateQuestionForm(array &$form, FormStateInterface $form_state, Request $request): mixed {
    return $form['question'];
  }

  /**
   * Get previous next via ajax.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function getNextQuestion(array &$form, FormStateInterface $form_state): void {
    $current_question = $this->getCurrentQuestion();
    $response_data = $current_question?->getResponse($form, $form_state);
    if (isset($response_data)) {
      $this->quizManager->updateQuiz($this->quizResult, $current_question, $response_data);
    }
    $this->questionNumber++;
    $user_input = $form_state->getUserInput();
    $user_input['question_navigation'] = $this->questionNumber;
    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);
  }

  public function getQuestion(array &$form, FormStateInterface $form_state) {
    $question_number = (int) $form_state->getValue('question_navigation') ;
    $this->questionNumber = $question_number;
    return $form['question'];
  }

  /**
   * Get previous question via ajax.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function getPreviousQuestion(array &$form, FormStateInterface $form_state): void {
    $this->questionNumber--;
    $user_input = $form_state->getUserInput();
    $user_input['question_navigation'] = $this->questionNumber;
    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Return current question.
   *
   * @return ?\Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  public function getCurrentQuestion(): ?QuestionInterface {
    $quiz = $this->currentRequest->get('quiz');
    if ($quiz instanceof QuizInterface) {
      $question = $quiz->getQuestions();
      return $question[$this->questionNumber];
    }
    return NULL;
  }

  /**
   * Add class to radios of question number.
   *
   * @param $element
   *   The element.
   * @param $form_state
   *   the form state.
   *
   * @return array
   *   The element array.
   */
  public function getQuestionNumberClass($element, $form_state): array {
    $question_numbers = array_keys($element['#options']);
    $questions = $this->quizResult->getQuiz()->getQuestions();
    foreach ($question_numbers as $question_number) {
      /** @var \Drupal\quiz_maker\QuestionInterface $question */
      $question = $questions[$question_number];
      $question_response = $this->quizResult->getResponse($question);
      if ($question_response) {
        $element[$question_number]['#attributes']['class'][] = $question_response->isCorrect() ? 'correct' : 'in-correct';
      }
    }
    return $element;
  }

}
