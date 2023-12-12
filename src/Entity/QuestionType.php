<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Question type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "quiz_maker_question_type",
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
 *   admin_permission = "administer quiz_maker_question types",
 *   bundle_of = "quiz_maker_question",
 *   config_prefix = "quiz_maker_question_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/quiz_maker_question_types/add",
 *     "edit-form" = "/admin/structure/quiz_maker_question_types/manage/{quiz_maker_question_type}",
 *     "delete-form" = "/admin/structure/quiz_maker_question_types/manage/{quiz_maker_question_type}/delete",
 *     "collection" = "/admin/structure/quiz_maker_question_types",
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
