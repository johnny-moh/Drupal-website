<?php

namespace Drupal\admin_toolbar_content\Plugin\Derivative;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a basic menu links deriver.
 */
class AdminToolbarContentMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface
   */
  protected AdminToolbarContentPluginManagerInterface $pluginManager;

  /**
   * Create an AdminToolbarContentMenuLinks deriver.
   *
   * @param \Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface $plugin_manager
   */
  public function __construct(AdminToolbarContentPluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): ContainerDeriverInterface|AdminToolbarContentMenuLinks|static {
    return new static(
      $container->get('admin_toolbar_content.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(mixed $base_plugin_definition): array {
    $links = [];

    $plugins = $this->pluginManager->getDefinitions();
    foreach ($plugins as $plugin_id => $definition) {
      try {
        $plugin = $this->pluginManager->createInstance($plugin_id, $definition);
        if ($plugin->isEnabled()) {
          $plugin->initialize($links, $base_plugin_definition);
          $plugin->createMenuLinkItems();
        }
      } catch (PluginException $e) {
        // Silently continue if plugin is not found.
      }
    }

    return $links;
  }

}
