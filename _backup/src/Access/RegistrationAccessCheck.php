<?php

namespace Drupal\event_registration\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Custom access check for event registration routes.
 *
 * Provides permission-based access control for administrative routes.
 */
class RegistrationAccessCheck implements AccessInterface
{

  /**
   * Checks access for event registration routes.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to check access for.
   * @param \Symfony\Component\Routing\Route $route
   *   The route being accessed.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result. Returns allowed if user has 'administer event
   *   registrations' permission, forbidden otherwise.
   */
  public function access(AccountInterface $account, Route $route): AccessResultInterface
  {
    // Check if user has admin permission.
    if ($account->hasPermission('administer event registrations')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return AccessResult::forbidden()->cachePerPermissions();
  }

}
