<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
  protected int $questionNumber;

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
   * Form constructor.
   */
  public function __construct(
    RequestStack $requestStack,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QuizManager $quizManager,
    protected AccountInterface $currentUser
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->questionNumber = -1;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('quiz_maker.manager'),
      $container->get('current_user'),
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
    $this->quizResult = $this->quizManager->createQuizResult($this->currentUser, $quiz);

    $form['question'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'question'
      ],
    ];

    if ($quiz) {
      $questions = $quiz->getQuestions();

      /** @var \Drupal\quiz_maker\QuestionInterface $current_question */
      if ($this->questionNumber == -1) {
        // When user open form - it will get the last active question if quiz
        // wasn't finished before.
        $active_question = $this->quizResult->getActiveQuestion();
        $current_question = $active_question;
        $this->questionNumber = array_search($active_question, $questions);
      }
      else {
        $current_question = $questions[$this->questionNumber];
      }

      $form['question']['number'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->t('Question @current/@all', [
          '@current' => $this->questionNumber + 1,
          '@all' => count($questions)
        ])
      ];

      $form['question']['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $current_question->getQuestion(),
      ];

      $current_question_response = $this->quizResult->getResponse($current_question);
      $form['question']['answer_form'] = $current_question->getAnsweringForm($current_question_response);

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

      if ($this->questionNumber === (count($questions) - 1)) {
        $form['question']['navigation']['actions']['finish'] = [
          '#type' => 'submit',
          '#value' => $this->t('Finish'),
          '#submit' => ['::submitForm'],
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
      $this->quizManager->updateQuizResult($this->quizResult, $current_question, $response_data);
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
   *
   * @return mixed
   *   The form array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
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
    if (isset($response_data['response'])) {
      $this->quizManager->updateQuizResult($this->quizResult, $current_question, $response_data);
    }
    $this->questionNumber++;
    $form_state->setRebuild(TRUE);
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
    $form_state->setRebuild(TRUE);
  }

  /**
   * Return current question.
   *
   * @return ?\Drupal\quiz_maker\QuestionInterface
   *   The question.
   */
  private function getCurrentQuestion(): ?QuestionInterface {
    $quiz = $this->currentRequest->get('quiz');
    if ($quiz instanceof QuizInterface) {
      $question = $quiz->getQuestions();
      return $question[$this->questionNumber];
    }
    return NULL;
  }

}
