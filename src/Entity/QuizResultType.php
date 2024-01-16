<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Quiz Result type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "quiz_result_type",
 *   label = @Translation("Quiz Result type"),
 *   label_collection = @Translation("Quiz Result types"),
 *   label_singular = @Translation("quiz result type"),
 *   label_plural = @Translation("quiz results types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count quiz results type",
 *     plural = "@count quiz results types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuizResultTypeForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuizResultTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuizResultTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer quiz_result types",
 *   bundle_of = "quiz_result",
 *   config_prefix = "quiz_result_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/quiz-maker/structure/quiz_result_types/add",
 *     "edit-form" = "/admin/quiz-maker/structure/quiz_result_types/manage/{quiz_result_type}",
 *     "delete-form" = "/admin/quiz-maker/structure/quiz_result_types/manage/{quiz_result_type}/delete",
 *     "collection" = "/admin/quiz-maker/structure/quiz_result_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "workflow",
 *   },
 * )
 */
final class QuizResultType extends ConfigEntityBundleBase {

  /**
   * Quiz result state - draft.
   */
  const DRAFT = 'draft';

  /**
   * Quiz result state - on review.
   */
  const ON_REVIEW = 'on_review';

  /**
   * Quiz result state - completed.
   */
  const COMPLETED = 'completed';

  /**
   * Quiz result state - evaluated.
   */
  const EVALUATED = 'evaluated';

  /**
   * The machine name of this quiz result type.
   */
  protected string $id;

  /**
   * The human-readable name of the quiz result type.
   */
  protected string $label;

  /**
   * The order type workflow ID.
   *
   * @var string
   */
  protected string $workflow;

  /**
   * Gets the quiz result type's workflow ID.
   *
   * Used by the $quiz_result->state field.
   *
   * @return string
   *   The quiz result type workflow ID.
   */
  public function getWorkflowId(): string {
    return $this->workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // The order type must depend on the module that provides the workflow.
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($this->getWorkflowId());
    $this->calculatePluginDependencies($workflow);

    return $this;
  }

}
