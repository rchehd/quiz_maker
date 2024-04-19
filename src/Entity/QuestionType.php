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
 *     "plugin" = "plugin",
 *     "answer_type" = "answer_type",
 *     "response_type" = "response_type",
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
 *     "plugin",
 *     "answer_type",
 *     "response_type",
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

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected string $plugin;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected string $answer_type;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected string $response_type;

  /**
   * Get question type plugin id.
   *
   * @return ?string
   *   The plugin id.
   */
  public function getPluginId(): ?string {
    if (($plugin_key = $this->getEntityType()->getKey('plugin')) && isset($this->{$plugin_key})) {
      return $this->{$plugin_key};
    }

    return NULL;
  }

  /**
   * Get question answer type.
   *
   * @return ?string
   *   The answer type.
   */
  public function getAnswerType(): ?string {
    if (($plugin_key = $this->getEntityType()->getKey('answer_type')) && isset($this->{$plugin_key})) {
      return $this->{$plugin_key};
    }

    return NULL;
  }

  /**
   * Get question response type.
   *
   * @return ?string
   *   The response type.
   */
  public function getResponseType(): ?string {
    if (($plugin_key = $this->getEntityType()->getKey('response_type')) && isset($this->{$plugin_key})) {
      return $this->{$plugin_key};
    }

    return NULL;
  }

}
