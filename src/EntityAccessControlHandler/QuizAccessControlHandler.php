<?php

namespace Drupal\quiz_maker\EntityAccessControlHandler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the quiz entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class QuizAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    return match($operation) {
      'view' => AccessResult::allowedIfHasPermissions($account, ['view quiz', 'administer quiz types'], 'OR'),
      'update' => AccessResult::allowedIfHasPermissions($account, ['edit quiz', 'administer quiz types'], 'OR'),
      'delete' => AccessResult::allowedIfHasPermissions($account, ['delete quiz', 'administer quiz types'], 'OR'),
      'view all revisions' => AccessResult::allowedIfHasPermission($account, 'view all quiz revisions'),
      'view revision' => AccessResult::allowedIfHasPermission($account, 'view quiz revision'),
      'revert revision' => AccessResult::allowedIfHasPermission($account, 'revert quiz revision'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete quiz revision'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['create quiz', 'administer quiz types'], 'OR');
  }

}
