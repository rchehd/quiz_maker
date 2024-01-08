<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz_maker\Entity\QuizResultType;
use Drupal\quiz_maker\Entity\QuizType;
use Drupal\quiz_maker\QuestionInterface;
use Drupal\quiz_maker\QuestionResponseInterface;
use Drupal\quiz_maker\QuizInterface;
use Drupal\quiz_maker\QuizResultInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Quiz manager service.
 */
final class QuizManager {

  use StringTranslationTrait;

  /**
   * Constructs a QuizManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RequestStack $requestStack,
    protected TimeInterface $time
  ) {}

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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createQuizResult(AccountInterface $user, QuizInterface $quiz): ?QuizResultInterface {
    $draft_results = $this->getDraftResults($user, $quiz);
    if ($draft_results) {
      // Return the newest draft result.
      return end($draft_results);
    }
    $quiz_result_type = $quiz->get('field_result_type')->target_id;
    $quiz_result = $this->entityTypeManager->getStorage('quiz_result')->create([
      'bundle' => $quiz_result_type,
      'label' => $this->t('Result of "@quiz_label"', ['@quiz_label' => $quiz->label()]),
      'state' => QuizResultType::DRAFT,
      'field_quiz' => $quiz->id(),
      'uid' => $user->id(),
      'attempt' => $this->getQuizAttempts($user, $quiz) + 1,
    ]);

    $quiz_result->save();

    if ($quiz_result instanceof QuizResultInterface) {
      return $quiz_result;
    }

    return NULL;
  }

  /**
   * Get user draft results.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|\Drupal\quiz_maker\QuizResultInterface[]
   *   The array if quiz result entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDraftResults(AccountInterface $user, QuizInterface $quiz): array {
    $quiz_result_type = $quiz->get('field_result_type')->target_id;
    return $this->entityTypeManager->getStorage('quiz_result')->loadByProperties([
      'bundle' => $quiz_result_type,
      'state' => QuizResultType::DRAFT,
      'field_quiz' => $quiz->id(),
      'uid' => $user->id(),
    ]);
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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateQuizResult(QuizResultInterface $result, QuestionInterface $question, array $response_data): void {
    // Get question response from result, if it doesn't exist - create new response,
    // otherwise - update current response.
    $response = $result->getResponse($question);
    if (!$response) {
      /** @var \Drupal\quiz_maker\QuestionResponseInterface $response */
      $response = $this->entityTypeManager->getStorage('question_response')->create([
        'bundle' => $this->getQuestionResponseType($question),
        'label' => $this->t('Response of "@question_label"', ['@question_label' => $question->label()]),
      ]);
      $response->setQuiz($result->getQuiz());
      $response->setQuestion($question);
      $response->setResponseData($response_data);
      $response->setCorrect($question->isResponseCorrect($response_data));
      $response->setScore($question, $question->isResponseCorrect($response_data));
      $response->save();
      $result->addResponse($response);
    }
    else {
      $response->setResponseData($response_data);
      $response->setCorrect($question->isResponseCorrect($response_data));
      $response->setScore($question, $question->isResponseCorrect($response_data));
      $response->save();
    }

  }

  /**
   * Set quiz as finished.
   *
   * @param \Drupal\quiz_maker\QuizResultInterface $result
   *   The quiz result.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function finishQuiz(QuizResultInterface $result): void {
    $result->calculateScore();
    $result->setStatus(QuizResultType::COMPLETED);
    $result->setFinishedTime($this->time->getCurrentTime());
    $result->save();
  }

  /**
   * Get question response type.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   *
   * @return false|mixed|null
   *   The response type.
   */
  public function getQuestionResponseType(QuestionInterface $question): mixed {
    if ($question->hasField('field_response')) {
      $target_bundles = $question->get('field_response')->getFieldDefinition()->getSetting('handler_settings')['target_bundles'];
      return reset($target_bundles);
    }
    return NULL;
  }

  /**
   * Get quiz attempts count.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getQuizAttempts(AccountInterface $user, QuizInterface $quiz): int {
    $results = $this->entityTypeManager->getStorage('quiz_result')->loadByProperties([
      'uid' => $user->id(),
      'field_quiz' => $quiz->id(),
      'state' => QuizResultType::COMPLETED,
    ]);

    return count($results);
  }

}
