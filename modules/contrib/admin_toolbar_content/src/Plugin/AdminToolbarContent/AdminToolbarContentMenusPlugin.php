<?php

namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * An AdminToolbarContentPlugin for altering the system 'menus' menu and
 * adding a new main 'Menus' menu item.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "menus",
 *   name = @Translation("Menus"),
 *   description = @Translation("Adds a 'Menus' item to the admin menu."),
 *   entity_type = "menu"
 * )
 */
class AdminToolbarContentMenusPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {
    $this->createRootLink($this->t("Menus"), "entity.menu.collection", [], -6);
    $this->createCollectionLinks("entity.menu.edit_form");
    $this->createItemLinks("entity.menu.edit_form", "menu");
    $this->createItemAddLinks("entity.menu.add_form");
  }

  /**
   * {@inheritdoc}
   */
  public function needsMenuLinkRebuild(EntityInterface $entity): bool {
    return ($entity->getEntityTypeId() == 'menu');
  }

}
