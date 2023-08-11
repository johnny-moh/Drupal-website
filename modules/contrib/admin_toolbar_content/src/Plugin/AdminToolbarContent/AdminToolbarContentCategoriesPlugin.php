<?php
namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;

/**
 * An AdminToolbarContentPlugin for altering the system taxonomies menu and
 * adding a new main categories menu item.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "categories",
 *   name = @Translation("Categories"),
 *   description = @Translation("Adds a 'Categories' item to the admin menu."),
 *   entity_type = "taxonomy_vocabulary",
 * )
 */
class AdminToolbarContentCategoriesPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function alterDiscoveredMenuLinks(array &$links): void {

    $vocabularies = $this->getItems();

    foreach ($vocabularies as $id => $vocabulary) {
      if (isset($links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id])) {
        // Transform the overview form link into the edit link, but keep the original title and parent.
        $links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id] = [
            'class' => $links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id]['class'],
            'parent' => $links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id]['parent'],
            'metadata' => $links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id]['metadata'],
          ] + $links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.edit_form.' . $id];

        // Let the title be handled by the MenuLinkEntity::getTitle().
        unset($links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.overview_form.' . $id]['title']);

        // Remove the edit link.
        unset($links['admin_toolbar_tools.extra_links:entity.taxonomy_vocabulary.edit_form.' . $id]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {
    $this->createRootLink($this->t('Categories'), 'entity.taxonomy_vocabulary.collection', [], -8);
    $this->createCollectionLinks('entity.taxonomy_vocabulary.collection');
    $this->createItemLinks('entity.taxonomy_vocabulary.overview_form', 'taxonomy_vocabulary');
    $this->createItemAddLinks("entity.taxonomy_term.add_form");
  }

}
