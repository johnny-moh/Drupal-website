<?php
namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * An AdminToolbarContentPlugin for altering the system content menu.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "content",
 *   name = @Translation("Content"),
 *   description = @Translation("Alters the system content menu to provide links for each content type."),
 *   entity_type = "node_type"
 * )
 */
class AdminToolbarContentContentPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigForm(array &$form, FormStateInterface $form_state): array {
    $elements = parent::buildConfigForm($form, $form_state);

    $elements['recent_items'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Show recent content'),
      '#description' => $this->t('Show recent content items. Leave empty or 0 to show none.'),
      '#default_value' => $this->config->get('content.recent_items') ?? 10
    ];

    $recent_items_link_options = ['default' => $this->t('Edit form')];

    // Add Layout Builder option.
    if ($this->moduleHandler->moduleExists('layout_builder')) {
      $recent_items_link_options = $recent_items_link_options + ['layout_builder' => $this->t('Layout Builder')];
    }

    if (count($recent_items_link_options) > 1) {
      $elements['recent_items_link'] = [
        '#type' => 'radios',
        '#title' => $this->t('Recent items link'),
        '#description' => $this->t('Choose the destination the recent items link should go to.'),
        '#options' => $recent_items_link_options,
        '#default_value' => $this->config->get('content.recent_items_link') ?? 'default',
      ];
    }

    $elements['hide_non_content_items'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide non content items'),
      '#description' => $this->t('Hides items under "content" not directly related to content types.'),
      '#default_value' => $this->config->get('content.hide_non_content_items') ?? 0
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function alterDiscoveredMenuLinks(array &$links): void {

    $hide_non_content_items = $this->config->get('content.hide_non_content_items') ?? 0;
    if ($hide_non_content_items) {
      $parents = ['system.admin_content'];
      while (!empty($parents)) {
        $removed = [];
        foreach ($links as $name => $link) {
          if (isset($link['parent']) && in_array($link['parent'], $parents)) {
            if (!str_starts_with($name, 'admin_toolbar_content')) {
              unset($links[$name]);
              $removed[] = $name;
            }
          }
        }
        $parents = $removed;
      }
    }

    // Unset the original "add content" menu item and it's children.
    // These are replaced with the links from createMenuLinkItems.
    unset($links['admin_toolbar_tools.extra_links:node.add']);
    $contentTypes = $this->getItems();
    foreach ($contentTypes as $contentType) {
      unset($links['admin_toolbar_tools.extra_links:node.add.' . $contentType->id()]);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function needsMenuLinkRebuild(EntityInterface $entity): bool {
    $needsMenuLinkRebuild = FALSE;

    if ($entity instanceof Node) {
      $needsMenuLinkRebuild = (bool) ($this->config->get('content.recent_items') ?? 0);
    }

    return $needsMenuLinkRebuild;
  }

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {
    $this->createCollectionLinks('system.admin_content');
    $this->createItemLinks('system.admin_content', 'type');
    $this->createItemAddLinks('node.add');
    $this->createItemRecentContentLinks('node', 'entity.node.edit_form', []);
  }

  /**
   * {@inheritdoc}
   */
  protected function createCollectionLink(array $collection, $route_name, $route_parameters = []): void {
    parent::createCollectionLink($collection, $route_name, $route_parameters);
    if (isset($this->links[$collection['id']])) {
      // Because we don't have a custom root item, we add collections to the
      // existing system content menu item.
      if ($collection['parent'] == $this->getPluginId()) {
        $this->links[$collection['id']]['parent'] = 'system.admin_content';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function createItemLink(mixed $item, string $route_name, string $route_item_parameter): void {
    parent::createItemLink($item, $route_name, $route_item_parameter);
    $collection = $this->getItemCollection($item);
    if (isset($this->links[$collection['id'] . '.' . $item->id()])) {
      // Because we don't have a custom root item, we add items without a
      // collection to the existing system content menu item.
      if ($collection['id'] == $this->getPluginId()) {
        $this->links[$collection['id'] . '.' . $item->id()]['parent'] = 'system.admin_content';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function createItemRecentContentEditLink(array $collection, mixed $item, mixed $entity, string $route_name, array $route_parameters, int $weight = 0): void {
    parent::createItemRecentContentEditLink($collection, $item, $entity, $route_name, $route_parameters, $weight);

    if (isset($this->links[$collection['id'] . '.' . $item->id() . '.entity.' . $entity->id()])) {
      $recent_items_link = $this->config->get('content.recent_items_link') ?? 'default';
      if ($recent_items_link === 'layout_builder') {
        if ($this->isRouteAvailable('layout_builder.overrides.node.view')) {
          $this->links[$collection['id'] . '.' . $item->id() . '.entity.' . $entity->id()]['route_name'] = 'layout_builder.overrides.node.view';
        }
      }
    }
  }

}
