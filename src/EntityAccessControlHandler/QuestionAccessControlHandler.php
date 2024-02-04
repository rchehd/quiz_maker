<?php

namespace Drupal\quiz_maker\EntityAccessControlHandler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the question entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class QuestionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    return match($operation) {
      'view' => AccessResult::allowedIfHasPermissions($account, ['view question', 'administer question types'], 'OR'),
      'update' => AccessResult::allowedIfHasPermissions($account, ['edit question', 'administer question types'], 'OR'),
      'delete' => AccessResult::allowedIfHasPermissions($account, ['delete question', 'administer question types'], 'OR'),
      'view all revisions' => AccessResult::allowedIfHasPermission($account, 'view all question revisions'),
      'view revision' => AccessResult::allowedIfHasPermission($account, 'view question revision'),
      'revert revision' => AccessResult::allowedIfHasPermission($account, 'revert question revision'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete question revision'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['create question', 'administer question types'], 'OR');
  }

}
