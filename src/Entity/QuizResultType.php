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
 *     "add-form" = "/admin/structure/quiz_result_types/add",
 *     "edit-form" = "/admin/structure/quiz_result_types/manage/{quiz_result_type}",
 *     "delete-form" = "/admin/structure/quiz_result_types/manage/{quiz_result_type}/delete",
 *     "collection" = "/admin/structure/quiz_result_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 * )
 */
final class QuizResultType extends ConfigEntityBundleBase {

  /**
   * The machine name of this quiz result type.
   */
  protected string $id;

  /**
   * The human-readable name of the quiz result type.
   */
  protected string $label;

}
