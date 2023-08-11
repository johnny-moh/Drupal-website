<?php

namespace Drupal\admin_toolbar_content;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\node\Entity\Node;

/**
 * Manages admin toolbar content plugins.
 */
class AdminToolbarContentPluginManager extends DefaultPluginManager implements AdminToolbarContentPluginManagerInterface {

  /**
   * Constructs a new AdminToolbarContentPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   An object that implements CacheBackendInterface
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   An object that implements ModuleHandlerInterface
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/AdminToolbarContent',
      $namespaces,
      $module_handler,
      'Drupal\admin_toolbar_content\AdminToolbarContentPluginInterface',
      'Drupal\admin_toolbar_content\Annotation\AdminToolbarContentPlugin'
    );
    $this->cacheBackend = $cache_backend;
    $this->cacheKeyPrefix = 'admin_toolbar_content_plugins';
    $this->cacheKey = 'admin_toolbar_content_plugins';
    $this->alterInfo('admin_toolbar_content_plugins_info');
  }

  /**
   * {@inheritdoc}
   */
  public function menuLinkRebuild(EntityInterface $entity): void {

    $plugins = $this->getDefinitions();

    foreach ($plugins as $plugin_id => $definition) {
      try {
        $plugin = $this->createInstance($plugin_id, $definition);
        if ($plugin->isEnabled() && $plugin->needsMenuLinkRebuild($entity)) {
          \Drupal::service('plugin.manager.menu.link')->rebuild();
        }
      } catch (PluginException $e) {
        // Silently continue if plugin is not found.
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function menuLinksDiscoveredAlter(array &$links): void {

    $plugins = $this->getDefinitions();

    foreach ($plugins as $plugin_id => $definition) {
      try {
        $plugin = $this->createInstance($plugin_id, $definition);
        if ($plugin->isEnabled()) {
          $plugin->alterDiscoveredMenuLinks($links);
        }
      } catch (PluginException $e) {
        // Silently continue if plugin is not found.
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessMenu(array &$variables): void {

    $plugins = $this->getDefinitions();

    foreach ($plugins as $plugin_id => $definition) {
      try {
        $plugin = $this->createInstance($plugin_id, $definition);
        if ($plugin->isEnabled()) {
          $plugin->preprocessMenu($variables);
        }
      } catch (PluginException $e) {
        // Silently continue if plugin is not found.
      }
    }
  }
}
