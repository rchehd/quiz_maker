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
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Quiz manager service.
 */
class QuizManager {

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
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo
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
   *
   * @return \Drupal\quiz_maker\QuizResultInterface|null
   *   The quiz result or null.
   */
  public function startQuiz(AccountInterface $user, QuizInterface $quiz): ?QuizResultInterface {
    $draft_results = $quiz->getResults($user, [
      'state' => QuizResultType::DRAFT,
    ]);
    if ($draft_results) {
      // Return the newest draft result.
      return end($draft_results);
    }
    $quiz_result_type = $quiz->getResultType();
    try {
      $quiz_result = $this->entityTypeManager->getStorage('quiz_result')->create([
        'bundle' => $quiz_result_type,
        'label' => $this->t('Result of "@quiz_label"', ['@quiz_label' => $quiz->label()]),
        'state' => QuizResultType::DRAFT,
        'quiz' => $quiz->id(),
        'uid' => $user->id(),
        'attempt' => $quiz->getCompletedAttempts($user) + 1,
      ]);
      $quiz_result->save();
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }

    if ($quiz_result instanceof QuizResultInterface) {
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
   *   The question.
   * @param array $response_data
   *   The response data.
   */
  public function updateQuiz(QuizResultInterface $result, QuestionInterface $question, array $response_data): void {
    // Get question response from result, if it doesn't exist - create new response,
    // otherwise - update current response.
    $response = $result->getResponse($question);
    if (!$response) {
      /** @var \Drupal\quiz_maker\QuestionResponseInterface $response */
      try {
        $response = $this->entityTypeManager->getStorage('question_response')->create([
          'bundle' => $question->getResponseType(),
          'label' => $this->t('Response of "@question_label"', ['@question_label' => $question->label()]),
        ]);
        $response->setQuiz($result->getQuiz())
          ->setQuestion($question)
          ->setResponseData($response_data)
          ->setCorrect($question->isResponseCorrect($response_data))
          ->setScore($question, $question->isResponseCorrect($response_data), $question->getMaxScore(), $response_data)
          ->save();
        $result->addResponse($response)->save();
      }
      catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
        $this->logger->error($e->getMessage());
      }
    }
    else {
      try {
        $response->setResponseData($response_data)
          ->setCorrect($question->isResponseCorrect($response_data))
          ->setScore($question, $question->isResponseCorrect($response_data), $question->getMaxScore(), $response_data)
          ->save();
      }
      catch (EntityStorageException $e) {
        $this->logger->error($e->getMessage());
      }
    }

  }

  /**
   * Set quiz as finished.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $result
   *   The quiz result.
   */
  public function finishQuiz(QuizResultInterface $result): void {
    $score = $this->calculateScore($result);
    $state = $result->getQuiz()->requireManualAssessment() ? QuizResultType::ON_REVIEW : QuizResultType::COMPLETED;
    try {
      $result->setScore($score)
        ->setPassed($score >= $result->getQuiz()->getPassRate())
        ->setState($state)
        ->setFinishedTime($this->time->getCurrentTime())
        ->save();
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
    return round(($score / $quiz_result->getQuiz()->getMaxScore()) * 100);
  }

}
