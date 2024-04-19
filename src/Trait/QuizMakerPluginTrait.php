<?php

namespace Drupal\quiz_maker\Trait;

/**
 * Provides a entity helper for entity types class.
 *
 * @internal
 */
trait QuizMakerPluginTrait {

  /**
   * Get available plugins.
   *
   * @return array
   *   Array of plugins.
   */
  protected function getPlugins() {
    $options = [];
    foreach ($this->pluginManager->getDefinitions() as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = $plugin_definition['label'];
    }

    return $options;
  }

}
