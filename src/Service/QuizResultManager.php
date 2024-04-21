<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\QuizResultType;
use Drupal\quiz_maker\Event\QuizTakeEvent;
use Drupal\quiz_maker\Event\QuizTakeEvents;
use Drupal\quiz_maker\Event\ResponseEvent;
use Drupal\quiz_maker\Event\ResponseEvents;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Quiz manager service.
 */
class QuizResultManager {

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
    protected RequestStack $requestStack,
    protected TimeInterface $time,
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected EventDispatcherInterface $eventDispatcher
  ) {
    $this->logger = $loggerChannelFactory->get('quiz_maker');
  }

  /**
   * Create quiz result.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user (quiz participant).
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   * @param string $langcode
   *   The langcode.
   *
   * @return \Drupal\quiz_maker\QuizResultInterface|null
   *   The quiz result or null.
   */
  public function createQuizResult(AccountInterface $user, QuizInterface $quiz, string $langcode): ?QuizResultInterface {
    $draft_results = $quiz->getResults($user, [
      'state' => QuizResultType::DRAFT,
    ]);
    if ($draft_results) {
      // Return the newest draft result.
      /** @var \Drupal\quiz_maker\QuizResultInterface $result */
      $result = end($draft_results);
      if ($result->hasTranslation($langcode)) {
        $quiz_event = new QuizTakeEvent($quiz, $result);
        $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_START);
        return $result->getTranslation($langcode);
      }
      else {
        $quiz_event = new QuizTakeEvent($quiz, $result);
        $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_START);
        return $result;
      }
    }
    $quiz_result_type = $quiz->getResultType();
    try {
      $quiz_result = $this->entityTypeManager->getStorage('quiz_result')->create([
        'bundle' => $quiz_result_type,
        'label' => $this->t('Result of "@quiz_label"', ['@quiz_label' => $quiz->label()]),
        'state' => QuizResultType::DRAFT,
        'quiz' => $quiz,
        'uid' => $user->id(),
        'attempt' => $quiz->getCompletedAttempts($user) + 1,
        'langcode' => $langcode,
      ]);
      $quiz_result->save();
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }

    if ($quiz_result instanceof QuizResultInterface) {
      $quiz_event = new QuizTakeEvent($quiz, $quiz_result);
      $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_START);
      return $quiz_result;
    }

    return NULL;
  }

  /**
   * Update quiz result.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $result
   *   The quiz result.
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question plugin instance.
   * @param array $response_data
   *   The response data.
   * @param string $langcode
   *   The langcode.
   */
  public function updateQuizResult(QuizResultInterface $result, QuestionInterface $question, array $response_data, string $langcode): void {
    // Get question response from result, if it doesn't exist - create new response,
    // otherwise - update current response.
    $response = $result->getResponse($question);
    if (!$response) {
      $response = $this->createResponse($result, $question, $response_data, $langcode);
      if ($response) {
        try {
          $result->addResponse($response)->save();
        }
        catch (EntityStorageException $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }
    else {
      try {
        $response->setResponseData($response_data)
          ->setCorrect($question->isResponseCorrect($response_data))
          ->setScore($question, $question->isResponseCorrect($response_data), $question->getMaxScore(), $response_data)
          ->save();
        // Dispatch 'Response create' event.
        $response_event = new ResponseEvent($result, $response);
        $this->eventDispatcher->dispatch($response_event, ResponseEvents::RESPONSE_UPDATE);
      }
      catch (EntityStorageException $e) {
        $this->logger->error($e->getMessage());
      }
    }

    // Dispatch 'Quiz update' event.
    $quiz_event = new QuizTakeEvent($result->getQuiz(), $result);
    $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_UPDATE);
  }

  /**
   * Set quiz as finished.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $result
   *   The quiz result.
   * @param string $langcode
   *   The langcode.
   */
  public function completeQuizResult(QuizResultInterface $result, string $langcode): void {
    $questions = $result->getQuiz()->getQuestions();
    // Create empty responses if question was skipped by user.
    foreach ($questions as $question) {
      $response = $result->getResponse($question);
      if (!$response) {
        $response = $this->createResponse($result, $question, [], $langcode);
        if ($response) {
          try {
            $result->addResponse($response)->save();
            $quiz_event = new QuizTakeEvent($result->getQuiz(), $result);
            $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_FINISH);
          }
          catch (EntityStorageException $e) {
            $this->logger->error($e->getMessage());
          }
        }
      }
    }

    $score = $this->calculateScore($result);
    $state = $result->getQuiz()->requireManualAssessment() ? QuizResultType::ON_REVIEW : QuizResultType::COMPLETED;
    try {
      $result->setScore($score)
        ->setPassed($score >= $result->getQuiz()->getPassRate())
        ->setState($state)
        ->setFinishedTime($this->time->getCurrentTime())
        ->save();
      $quiz_event = new QuizTakeEvent($result->getQuiz(), $result);
      $this->eventDispatcher->dispatch($quiz_event, QuizTakeEvents::QUIZ_FINISH);
    }
    catch (EntityStorageException $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Calculate quiz result score.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $quiz_result
   *   The quiz result.
   */
  public function calculateScore(QuizResultInterface $quiz_result): int {
    $responses = $quiz_result->getResponses();
    $score = 0;
    foreach ($responses as $response) {
      $score = $score + $response->getScore();
    }
    return (int) round(($score / $quiz_result->getQuiz()->getMaxScore()) * 100);
  }

  /**
   * Create question response.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $result
   *   The quiz result.
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question instance.
   * @param array $response_data
   *   The response data.
   * @param string $langcode
   *   The langcode.
   *
   * @return ?\Drupal\quiz_maker\QuestionResponseInterface
   *   The response.
   */
  protected function createResponse(QuizResultInterface $result, QuestionInterface $question, array $response_data, string $langcode): ?QuestionResponseInterface {
    try {
      /** @var \Drupal\quiz_maker\QuestionResponseInterface $response */
      $response = $this->entityTypeManager->getStorage('question_response')->create([
        'bundle' => $question->getResponseType(),
        'label' => $this->t('Response of "@question_label"', ['@question_label' => $question->label()]),
        'langcode' => $langcode,
      ]);
      $response->setQuiz($result->getQuiz())
        ->setQuestion($question)
        ->setResponseData($response_data)
        ->setCorrect($question->isResponseCorrect($response_data))
        ->setScore($question, $question->isResponseCorrect($response_data), $question->getMaxScore(), $response_data)
        ->save();

      // Dispatch 'Response create' event.
      $question_event = new ResponseEvent($result, $response);
      $this->eventDispatcher->dispatch($question_event, ResponseEvents::RESPONSE_CREATE);
      return $response;
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      $this->logger->error($e->getMessage());
    }

    return NULL;
  }

}
