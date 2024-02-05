<?php

namespace Drupal\quiz_maker_eca\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for ECA Quiz event plugins.
 */
class QuizMakerEventDeriver extends EventDeriverBase {

  /**
   * {@inheritDoc}
   */
  protected function definitions(): array {
    return QuizMakerEvent::definitions();
  }

}
