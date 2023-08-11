<?php
namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An AdminToolbarContentPlugin for altering the system media menu and
 * adding a new main media menu item.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "media",
 *   name = @Translation("Media"),
 *   description = @Translation("Adds a 'Media' item to the admin menu."),
 *   entity_type = "media_type",
 * )
 */
class AdminToolbarContentMediaPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigForm(array &$form, FormStateInterface $form_state): array {
    $elements = parent::buildConfigForm($form, $form_state);

    if ($this->isRouteAvailable('view.media_library.page')) {
      $elements['link_media_library'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Link to media library'),
        '#description' => $this->t('Links media items directly to the media library.'),
        '#default_value' => $this->config->get('media.link_media_library') ?? 0
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function alterDiscoveredMenuLinks(array &$links): void {
    unset($links["admin_toolbar_tools.extra_links:media_page"]);
    unset($links["admin_toolbar_tools.extra_links:media_library"]);
    unset($links["admin_toolbar_tools.extra_links:add_media"]);

    $mediaTypes = $this->getItems();
    foreach($mediaTypes as $id => $mediaType) {
      unset($links["admin_toolbar_tools.extra_links:media.add.$id"]);
    }

    if ($this->isRouteAvailable('view.files.page_1')) {
      unset($links['admin_toolbar_tools.extra_links:view.files']);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {

    $link_library = $this->config->get('media.link_media_library') ?? 1;

    // Use Media Library page if library is accessible.
    if ($link_library && $this->isRouteAvailable('view.media_library.page')) {
      $this->createRootLink($this->t("Media"), "view.media_library.page", [], -7);
    }
    else {
      $this->createRootLink($this->t("Media"), "view.media.media_page_list", [], -7);
    }

    $this->createFilesLink();
    $this->createCollectionLinks("entity.media.collection");
    $this->createItemLinks("entity.media.collection", 'media');
    $this->createItemAddLinks("entity.media.add_form");
  }

  protected function createFilesLink(): void {
    if ($this->isRouteAvailable('view.files.page_1')) {
      $this->links[$this->getPluginId() . '.files'] = [
        'title' => $this->t('Files'),
        'route_name' => 'view.files.page_1',
        'route_parameters' => [],
        'menu_name' => 'admin',
        'parent' => isset($this->links[$this->getPluginId()])
          ? $this->base_menu_link_plugin_definition['id'] . ':' . $this->getPluginId()
          : 'system.admin',
        'weight' => -7,
      ] + $this->base_menu_link_plugin_definition;
    }
  }

}
