<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Event\QuestionNavigationEvent;
use Drupal\quiz_maker\Event\QuizTakeEvents;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\Service\QuizSession;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class of Quiz take form.
 */
class QuizTakeForm extends FormBase {

  use StringTranslationTrait;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected ?Request $currentRequest;

  /**
   * Time when quiz was started.
   *
   * @var int
   *   The timestamp.
   */
  protected int $started;

  /**
   * Form constructor.
   */
  public function __construct(
    RequestStack $requestStack,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected TimeInterface $time,
    protected EventDispatcherInterface $eventDispatcher,
    protected QuizSession $quizSession,
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->started = $this->time->getCurrentTime();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
      $container->get('event_dispatcher'),
      $container->get('quiz_maker.quiz_session'),
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

    $form['question'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'question'
      ],
      '#weight' => 1,
    ];

    if ($quiz) {
      // Start quiz session if it isn't exist.
      if ($this->quizSession->hasSession($quiz) === FALSE) {
        $this->quizSession->startSession($quiz);
      }

      $questions = $this->quizSession->getQuestions($quiz);
      $time_limit = $quiz->getTimeLimit();

      if ($time_limit) {
        $time_left = $this->quizSession->getTimeLeft($quiz, $time_limit);
        $form['timer_block'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'quiz-timer',
            'class' => ['quiz-timer']
          ],
          '#weight' => 0,
        ];

        $form['timer_block']['value'] = [
          '#theme' => 'timer',
          '#hours' => gmdate('H', $time_left),
          '#minutes' => gmdate('i', $time_left),
          '#seconds' => gmdate('s', $time_left),
        ];

        $form['#attached']['drupalSettings']['time_limit'] = $time_limit * 1000;
        $form['#attached']['drupalSettings']['started_time'] = (int) $this->quizSession->getQuizResult($quiz)->get('created')->value * 1000;
        $form['#attached']['library'][] = 'quiz_maker/quiz_timer';
      }

      // Set data from user input.
      $user_input = $form_state->getUserInput();
      if ($user_input) {
        $question_number = $user_input['question_navigation'];
        $this->quizSession->setCurrentQuestionNumber($quiz, $question_number);
      }

      $current_question = $this->quizSession->getCurrentQuestion($quiz);

      $form['question']['number'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Question @current/@all', [
          '@current' => $this->quizSession->getCurrentQuestionNumber($quiz) + 1,
          '@all' => count($questions)
        ]),
        '#attributes' => [
          'class' => 'question-number'
        ]
      ];

      $form['question']['question_navigation'] = [
        '#type' => 'radios',
        '#options' => range(1, count($questions)),
        '#default_value' => $this->quizSession->getCurrentQuestionNumber($quiz),
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

      $current_question_response = $this->quizSession->getQuizResult($quiz)->getResponse($current_question);
      $allow_change_response = $quiz->allowChangeAnswer() || !$this->quizSession->getQuizResult($quiz)->getResponse($current_question);
      $form['question']['answer_form'] = $current_question->getAnsweringForm($current_question_response, $allow_change_response);

      $form['question']['navigation']['actions'] = [
        '#type' => 'actions',
      ];

      if ($this->quizSession->getCurrentQuestionNumber($quiz) < (count($questions) - 1)) {
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

      if ($this->quizSession->getCurrentQuestionNumber($quiz) > 0 && $quiz->allowBackwardNavigation()) {
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

      if ($this->quizSession->getCurrentQuestionNumber($quiz) != (count($questions)) - 1) {
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
    $quiz = $this->getQuiz();
    if ($quiz instanceof QuizInterface) {
      $current_question = $this->quizSession->getCurrentQuestion($quiz);
      $response_data = $current_question->getResponse($form, $form_state);
      $quiz_result_id = $this->quizSession->getQuizResult($quiz)->id();
      $this->quizSession->finishSession($quiz, $response_data);
      $form_state->setRedirect('entity.quiz_result.canonical', [
        'quiz_result' => $quiz_result_id,
      ]);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $quiz = $this->getQuiz();
    if ($quiz instanceof QuizInterface) {
      /** @var \Drupal\quiz_maker\QuestionInterface $current_question */
      $current_question = $this->quizSession->getCurrentQuestionNumber($quiz);
      if (!$quiz->allowSkipping()) {
        $current_question->validateAnsweringForm($form, $form_state);
      }
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
    $quiz = $this->getQuiz();
    if ($quiz instanceof QuizInterface) {
      $current_question = $this->quizSession->getCurrentQuestion($quiz);
      $response_data = $current_question?->getResponse($form, $form_state);
      $question_number = $this->quizSession->incrementQuestionNumber($quiz, $response_data);

      $question_navigation_event = new QuestionNavigationEvent($quiz, $current_question, $this->quizSession->getQuizResult($quiz)->getUser(), $question_number);
      $this->eventDispatcher->dispatch($question_navigation_event, QuizTakeEvents::NEXT_QUESTION);

      $user_input = $form_state->getUserInput();
      $user_input['question_navigation'] = $question_number;
      $form_state->setUserInput($user_input);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Get question.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form element.
   */
  public function getQuestion(array &$form, FormStateInterface $form_state): array {
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
    $quiz = $this->getQuiz();
    if ($quiz instanceof QuizInterface) {
      $current_question = $this->quizSession->getCurrentQuestion($quiz);
      $question_number = $this->quizSession->decrementQuestionNumber($quiz);

      $question_navigation_event = new QuestionNavigationEvent($quiz, $current_question, $this->quizSession->getQuizResult($quiz)->getUser(), $question_number);
      $this->eventDispatcher->dispatch($question_navigation_event, QuizTakeEvents::PREVIOUS_QUESTION);

      $user_input = $form_state->getUserInput();
      $user_input['question_navigation'] = $question_number;
      $form_state->setUserInput($user_input);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Add class to radios of question number.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The element array.
   */
  public function getQuestionNumberClass(array $element, FormStateInterface $form_state): array {
    $quiz = $this->getQuiz();
    if ($quiz instanceof QuizInterface) {
      $question_numbers = array_keys($element['#options']);
      $questions = $this->quizSession->getQuestions($quiz);
      foreach ($question_numbers as $question_number) {
        /** @var \Drupal\quiz_maker\QuestionInterface $question */
        $question = $questions[$question_number];
        $question_response = $this->quizSession->getQuizResult($quiz)->getResponse($question);
        if ($question_response) {
          $element[$question_number]['#attributes']['class'][] = $question_response->isCorrect() ? 'correct' : 'in-correct';
        }
      }
    }

    return $element;
  }

  /**
   * Get quiz from request.
   *
   * @return \Drupal\quiz_maker\QuizInterface|null
   *   The quiz.
   */
  private function getQuiz(): ?QuizInterface {
    $quiz = $this->currentRequest->get('quiz');
    if ($quiz instanceof QuizInterface) {
      return $quiz;
    }
    return NULL;
  }

}
