<?php

namespace Drupal\admin_toolbar_content;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Manages AdminToolbarContent plugins.
 */
interface AdminToolbarContentPluginManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface, CacheableDependencyInterface {

  /**
   * Given an entity, let plugins decide if a menu rebuild is required.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return void
   */
  public function menuLinkRebuild(EntityInterface $entity): void;

  /**
   * Dispatches the hook_menu_links_discovered_alter to the plugins.
   *
   * @param array $links
   *
   * @return void
   */
  public function menuLinksDiscoveredAlter(array &$links): void;

  /**
   * Dispatches the hook_preprocess_menu to the plugins.
   *
   * @param array $variables
   *
   * @return void
   */
  public function preprocessMenu(array &$variables): void;

}
