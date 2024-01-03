<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Question type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "question_type",
 *   label = @Translation("Question type"),
 *   label_collection = @Translation("Question types"),
 *   label_singular = @Translation("question type"),
 *   label_plural = @Translation("questions types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count questions type",
 *     plural = "@count questions types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuestionTypeForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuestionTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuestionTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer question types",
 *   bundle_of = "question",
 *   config_prefix = "question_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/quiz-maker/structure/question_types/add",
 *     "edit-form" = "/admin/quiz-maker/structure/question_types/manage/{question_type}",
 *     "delete-form" = "/admin/quiz-maker/structure/question_types/manage/{question_type}/delete",
 *     "collection" = "/admin/quiz-maker/structure/question_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 * )
 */
final class QuestionType extends ConfigEntityBundleBase {

  /**
   * The machine name of this question type.
   */
  protected string $id;

  /**
   * The human-readable name of the question type.
   */
  protected string $label;

}
