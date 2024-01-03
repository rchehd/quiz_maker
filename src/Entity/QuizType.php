<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Quiz type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "quiz_type",
 *   label = @Translation("Quiz type"),
 *   label_collection = @Translation("Quiz types"),
 *   label_singular = @Translation("quiz type"),
 *   label_plural = @Translation("quizzes types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count quizzes type",
 *     plural = "@count quizzes types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuizTypeForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuizTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuizTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer quiz types",
 *   bundle_of = "quiz",
 *   config_prefix = "quiz_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/quiz-maker/structure/quiz_types/add",
 *     "edit-form" = "/admin/quiz-maker/structure/quiz_types/manage/{quiz_type}",
 *     "delete-form" = "/admin/quiz-maker/structure/quiz_types/manage/{quiz_type}/delete",
 *     "collection" = "/admin/quiz-maker/structure/quiz_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 * )
 */
final class QuizType extends ConfigEntityBundleBase {

  /**
   * The machine name of this quiz type.
   */
  protected string $id;

  /**
   * The human-readable name of the quiz type.
   */
  protected string $label;

}
