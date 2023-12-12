<?php

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a quiz entity type.
 */
interface QuizInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
