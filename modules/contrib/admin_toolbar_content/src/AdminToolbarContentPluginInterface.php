<?php
namespace Drupal\admin_toolbar_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
* Provides an interface defining a MenuLinksPlugin.
*/
interface AdminToolbarContentPluginInterface {

  /**
   * Initialises the plugin for creating new menu links based on the base
   * definition of the deriver.
   *
   * @param array $links
   * @param mixed $base_plugin_link_definition
   *
   * @return void
   */
  public function initialize(array &$links, mixed $base_plugin_link_definition): void;

  /**
   * Create the links this plugin provides.
   *
   * @return void
   */
  public function createMenuLinkItems(): void;

  /**
   * Allows a plugin to hook into the alteration of existing discovered menu
   * items.
   *
   * @param array $links
   *
   * @return void
   */
  public function alterDiscoveredMenuLinks(array &$links): void;

  /**
   * Given an entity, determine if a menu rebuild needs to be triggered.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   */
  public function needsMenuLinkRebuild(EntityInterface $entity): bool;


  /**
   * Allows a plugin to hook into the menu preprocess.
   *
   * @param array $variables
   *
   * @return void
   */
  public function preprocessMenu(array &$variables): void;

  /**
   * Allows a plugin to add extra settings to the admin toolbar content
   * configuration settings form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildConfigForm(array &$form, FormStateInterface $form_state): array;

}
