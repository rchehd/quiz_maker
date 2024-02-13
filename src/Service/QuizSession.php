<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\quiz_maker\Entity\Question;
use Drupal\quiz_maker\Entity\QuizResult;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Quiz Session class.
 */
class QuizSession {

  use StringTranslationTrait;

  /**
   * The quiz key in quiz session.
   */
  const QUIZ = 'quiz';

  /**
   * The quiz result key in quiz session.
   */
  const QUIZ_RESULT = 'quiz_result';

  /**
   * The current question number key in quiz session.
   */
  const CURRENT_QUESTION_NUMBER = 'current_question_number';

  /**
   * The questions key in quiz session.
   */
  const QUESTIONS = 'questions';

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * The langcode.
   *
   * @var string
   */
  protected string $langcode;

  /**
   * Private tempstore.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected PrivateTempStore $session;

  /**
   * Constructs a QuizManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
    protected EventDispatcherInterface $eventDispatcher,
    protected PrivateTempStoreFactory $privateTempStoreFactory,
    protected QuizResultManager $quizResultManager,
    protected AccountInterface $currentUser,
    protected LanguageManagerInterface $languageManager,
    protected TimeInterface $time,
  ) {
    $this->session = $privateTempStoreFactory->get('quiz_session');
    $this->logger = $loggerChannelFactory->get('quiz_maker');
    $this->langcode = $languageManager->getCurrentLanguage()->getId();
  }

  /**
   * Get Quiz session data.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return array
   *   The data
   */
  public function getSessionData(QuizInterface $quiz): array {
    return $this->hasSession($quiz) ? $this->session->get($quiz->id()) : [];
  }

  /**
   * Check if user have quiz session.
   *
   * @return bool
   *   TRUE when has, otherwise FALSE.
   */
  public function hasSession(QuizInterface $quiz): bool {
    if ($this->session->get($quiz->id())) {
      $session_data = $this->session->get($quiz->id());
      return isset($session_data[self::QUIZ_RESULT]);
    }
    return FALSE;
  }

  /**
   * Start quiz session - set params.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return bool
   *   TRUE when session was started successfully, otherwise FALSE.
   */
  public function startSession(QuizInterface $quiz): bool {
    $quizResult = $this->quizResultManager->createQuizResult($this->currentUser, $quiz, $this->langcode);
    $questions = $quiz->getQuestions();
    if ($quiz->randomizeQuestionSequence()) {
      shuffle($questions);
    }

    $questions_ids = array_map(function ($question) {
      return $question->id();
    }, $questions);

    try {
      $this->session->set($quiz->id(), [
        self::QUIZ => $quiz->id(),
        self::QUIZ_RESULT => $quizResult->id(),
        self::CURRENT_QUESTION_NUMBER => 0,
        self::QUESTIONS => $questions_ids,
      ]);

      return TRUE;
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Finish quiz - clear all session data.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param array $response_data
   *   The response data.
   *
   * @return bool
   *   TRUE when session was finished successfully, otherwise FALSE.
   */
  public function finishSession(QuizInterface $quiz, array $response_data): bool {
    if ($response_data) {
      $this->quizResultManager->updateQuizResult($this->getQuizResult($quiz), $this->getCurrentQuestion($quiz), $response_data, $this->langcode);
    }
    $this->quizResultManager->completeQuizResult($this->getQuizResult($quiz), $this->langcode);
    // Delete session for quiz.
    try {
      $this->session->delete($quiz->id());
      return TRUE;
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Get current question.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return \Drupal\quiz_maker\QuestionInterface|null
   *   The question or null.
   */
  public function getCurrentQuestion(QuizInterface $quiz): ?QuestionInterface {
    $session_data = $this->getSessionData($quiz);
    if ($session_data) {
      $question_number = $session_data[self::CURRENT_QUESTION_NUMBER];
      $question_ids = $session_data[self::QUESTIONS];
      if (isset($question_ids[$question_number])) {
        return Question::load($question_ids[$question_number]);
      }
    }

    return NULL;
  }

  /**
   * Get current question number.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return int
   *   The question number.
   */
  public function getCurrentQuestionNumber(QuizInterface $quiz): int {
    $session_data = $this->getSessionData($quiz);
    return $session_data[self::CURRENT_QUESTION_NUMBER] ?? 0;
  }

  /**
   * Set current question number.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param int $number
   *   The question number.
   *
   * @return bool
   *   TRUE when question number was updated, otherwise FALSE.
   */
  public function setCurrentQuestionNumber(QuizInterface $quiz, int $number): bool {
    return $this->updateSessionData($quiz, self::CURRENT_QUESTION_NUMBER, $number);
  }

  /**
   * Get quiz result.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface|null
   *   The quiz result or null.
   */
  public function getQuizResult(QuizInterface $quiz): ?QuizResultInterface {
    $session_data = $this->getSessionData($quiz);
    $quiz_result_id = $session_data[self::QUIZ_RESULT] ?? NULL;
    if ($quiz_result_id) {
      return QuizResult::load($quiz_result_id);
    }

    return NULL;
  }

  /**
   * Get questions of current session.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return array
   *   The array of questions.
   */
  public function getQuestions(QuizInterface $quiz): array {
    $session_data = $this->getSessionData($quiz);
    $question_ids = $session_data[self::QUESTIONS];
    if ($question_ids) {
      $question = Question::loadMultiple($question_ids);
      // Reset array keys to use it as sequence.
      return array_values($question);
    }

    return [];
  }

  /**
   * Increment current question number.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param array $response_data
   *   Response data.
   *
   * @return int
   *   The new question number.
   */
  public function incrementQuestionNumber(QuizInterface $quiz, array $response_data): int {
    $this->quizResultManager->updateQuizResult($this->getQuizResult($quiz), $this->getCurrentQuestion($quiz), $response_data, $this->langcode);
    $question_number = $this->getCurrentQuestionNumber($quiz);
    $question_number++;
    $this->updateSessionData($quiz, self::CURRENT_QUESTION_NUMBER, $question_number);
    return $question_number;
  }

  /**
   * Decrement current question number.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return int
   *   The new question number.
   */
  public function decrementQuestionNumber(QuizInterface $quiz): int {
    $question_number = $this->getCurrentQuestionNumber($quiz);
    $question_number--;
    $this->updateSessionData($quiz, self::CURRENT_QUESTION_NUMBER, $question_number);
    return $question_number;
  }

  /**
   * Get how much time left to end of quiz if quiz has time limit.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param int $time_limit
   *   The timestamp of rime limit.
   *
   * @return int
   *   The timestamp of time left.
   */
  public function getTimeLeft(QuizInterface $quiz, int $time_limit): int {
    $end_time = (int) $this->getQuizResult($quiz)->get('created')->value + $time_limit;
    return $end_time - $this->time->getCurrentTime();
  }

  /**
   * Update quiz session data.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param string $key
   *   The key of data array.
   * @param mixed $value
   *   The value of data array.
   *
   * @return bool
   *   TRUE when data was update successfully, otherwise FALSE.
   */
  private function updateSessionData(QuizInterface $quiz, string $key, mixed $value): bool {
    // Check if user has quiz session.
    if (!$this->hasSession($quiz)) {
      return FALSE;
    }
    // Set new key value and save.
    $session_data = $this->getSessionData($quiz);
    $session_data[$key] = $value;
    try {
      $this->session->set($quiz->id(), $session_data);
      return TRUE;
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Kill quiz session.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return bool
   *   TRUE when session was killed successfully, otherwise FALSE.
   */
  public function kill(QuizInterface $quiz): bool {
    try {
      $this->session->delete($quiz->id());
      return TRUE;
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

}
