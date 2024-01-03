<?php

namespace Drupal\quiz_maker\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
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

  /**
   * Constructs a QuizManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RequestStack $requestStack,
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
      'type' => $quiz_result_type,
      'state' => QuizResultType::DRAFT,
      'quiz_id' => $quiz->id(),
      'uid' => $user->id(),
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
      'type' => $quiz_result_type,
      'state' => QuizResultType::DRAFT,
      'quiz_id' => $quiz->id(),
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
    /** @var \Drupal\quiz_maker\QuestionResponseInterface $question_response */
    $question_response = $this->entityTypeManager->getStorage('question_response')->create([
      'type' => $this->getQuestionResponseType($question),
      'quiz_id' => $result->getQuiz()->id(),
      'question_id' => $question->id(),
    ]);
    $question_response->setResponseData($response_data);
    $question_response->save();
    $result->addResponse($question_response);
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
    $result->set('state', QuizResultType::COMPLETED);
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

}
