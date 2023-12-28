<?php declare(strict_types = 1);

namespace Drupal\quiz_maker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question response entity type.
 */
interface QuestionResponseInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
