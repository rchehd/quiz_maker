<?php

namespace Drupal\quiz_maker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Question Answer type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "quiz_maker_question_answer_type",
 *   label = @Translation("Question Answer type"),
 *   label_collection = @Translation("Question Answer types"),
 *   label_singular = @Translation("question answer type"),
 *   label_plural = @Translation("question answers types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count question answers type",
 *     plural = "@count question answers types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\quiz_maker\Form\QuestionAnswerTypeForm",
 *       "edit" = "Drupal\quiz_maker\Form\QuestionAnswerTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\quiz_maker\EntityListBuilder\QuestionAnswerTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer quiz_maker_question_answer types",
 *   bundle_of = "quiz_maker_question_answer",
 *   config_prefix = "quiz_maker_question_answer_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/quiz_maker_question_answer_types/add",
 *     "edit-form" = "/admin/structure/quiz_maker_question_answer_types/manage/{quiz_maker_question_answer_type}",
 *     "delete-form" = "/admin/structure/quiz_maker_question_answer_types/manage/{quiz_maker_question_answer_type}/delete",
 *     "collection" = "/admin/structure/quiz_maker_question_answer_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 * )
 */
final class QuestionAnswerType extends ConfigEntityBundleBase {

  /**
   * The machine name of this question answer type.
   */
  protected string $id;

  /**
   * The human-readable name of the question answer type.
   */
  protected string $label;

}
