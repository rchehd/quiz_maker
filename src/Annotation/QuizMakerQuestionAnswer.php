<?php

namespace Drupal\quiz_maker\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines quiz_maker_question annotation object.
 *
 * @Annotation
 */
final class QuizMakerQuestionAnswer extends Plugin {

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

}
