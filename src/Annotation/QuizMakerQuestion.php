<?php

namespace Drupal\quiz_maker\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines question annotation object.
 *
 * @Annotation
 */
final class QuizMakerQuestion extends Plugin {

  /**
   * The plugin ID.
   */
  public readonly string $id;

  /**
   * The human-readable name of the plugin.
   *
   * @ingroup plugin_translatable
   */
  public readonly string $title;

  /**
   * The description of the plugin.
   *
   * @ingroup plugin_translatable
   */
  public readonly string $description;

  /**
   * The class id of the plugin.
   */
  public readonly string $answer_class;

  /**
   * The answer plugin id of the plugin.
   */
  public readonly string $answer_plugin_id;

}
