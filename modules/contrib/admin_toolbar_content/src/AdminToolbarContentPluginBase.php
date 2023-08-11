<?php

namespace Drupal\admin_toolbar_content;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a basic deriver with caching capabilities.
 */
abstract class AdminToolbarContentPluginBase extends PluginBase implements AdminToolbarContentPluginInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entire list of links this deriver is providing.
   *
   * @var array
   */
  protected array $links;

  /**
   * The collections for this plugin.
   *
   * @var array
   */
  protected array $collections;

  /**
   * The items this plugin is handling.
   *
   * @var array
   */
  protected array $items;

  /**
   * The base menu link plugin definition.
   *
   * @var mixed
   */
  protected mixed $base_menu_link_plugin_definition;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected AccessManagerInterface $accessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected RouteProviderInterface $routeProvider;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The admin toolbar config settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   */
  public function __construct(array                      $configuration,
                              string                     $plugin_id,
                              mixed                      $plugin_definition,
                              LanguageManagerInterface   $languageManager,
                              AccessManagerInterface     $access_manager,
                              AccountProxyInterface      $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              RouteProviderInterface     $route_provider,
                              ModuleHandlerInterface     $module_handler,
                              ConfigFactoryInterface     $config_factory,
                              EntityRepositoryInterface  $entity_repository
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->languageManager = $languageManager;
    $this->accessManager = $access_manager;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeProvider = $route_provider;
    $this->moduleHandler = $module_handler;
    $this->config = $config_factory->get('admin_toolbar_content.settings');
    $this->entityRepository = $entity_repository;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('access_manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('router.route_provider'),
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('entity.repository'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigForm(array &$form, FormStateInterface $form_state): array {
    $elements = [];

    $elements['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Enabled the %plugin plugin.', ['%plugin' => $this->getPluginDefinition()['name']]),
      '#default_value' => $this->config->get($this->getPluginId() . ".enabled") ?? 0
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array &$links, mixed $base_plugin_link_definition): void {
    $this->links = &$links;
    $this->base_menu_link_plugin_definition = $base_plugin_link_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function alterDiscoveredMenuLinks(array &$links): void {
    // Base class does nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function needsMenuLinkRebuild(EntityInterface $entity): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessMenu(array &$variables): void {
    // Base class does nothing.
  }

  /**
   * {@inheritdoc}
   */
  abstract public function createMenuLinkItems(): void;

  /**
   * Determine if a route is accessible for the current user.
   *
   * @param string $route_name
   *   The name of the route to check.
   *
   * @return bool
   *   True if the route is accessible for the current user.
   */
  protected function isRouteAvailable(string $route_name): bool {
    return (count($this->routeProvider->getRoutesByNames([$route_name])) === 1);
  }

  /**
   * Check if this plugin is enabled or not.
   *
   * @return bool
   */
  public function isEnabled(): bool {

    // Check if plugin is enabled in config.
    $enabled = $this->config->get($this->getPluginId() . '.enabled') ?? FALSE;

    if ($enabled) {
      // If the plugin deals with entities, make sure the type it handles exists.
      $definition = $this->getPluginDefinition();
      $entity_type = $definition['entity_type'] ?? FALSE;
      if ($entity_type) {
        try {
          // Try to fetch the storage for the entity type.
          $this->entityTypeManager->getStorage($entity_type);
        } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
          return FALSE;
        }
      }
    }

    return $enabled;
  }

  /**
   * Get the collection an item belongs to.
   *
   * @param mixed $item
   * @param array|null $collections
   *
   * @return array
   */
  protected function getItemCollection(mixed $item, array $collections = NULL): array {

    $found = [];
    $default = [];

    if (!isset($collections)) {
      $collections = $this->getCollections();
      // On the initial call when $collection is null, we set a default
      // collection, that way the recursive call only returns a valid (nonempty)
      // result if it is found in 'items' of the recursive collections. On the
      // recursive call, default remains empty.
      $default = [
        'id' => $this->getPluginId()
      ];
    }

    foreach ($collections as $collection) {
      if (in_array($item->id(), $collection['items'] ?? [])) {
        $found = $collection;
      }
      else {
        $found = $this->getItemCollection($item, $collection['collections'] ?? []);
      }

      if (!empty($found)) {
        break;
      }
    }

    return $found + $default;
  }

  /**
   * Get the collection structure and stores it into the class
   *
   * @return array
   *   A flattened array of collections to process for this plugin.
   */
  protected function getCollections(): array {

    if (empty($this->collections)) {
      $this->collections = $this->moduleHandler->invokeAll('admin_toolbar_content_collections');
      foreach ($this->collections as $plugin_id => &$collection) {
        // Sort collections so grouping will use alphabetic order.
        asort($collection);

        // The initial parent of a collection is the plugin id.
        $this->prepCollection($collection, $plugin_id);
      }
    }

    return $this->collections[$this->getPluginId()] ?? [];
  }

  /**
   * Preps the collections array, so it can be used for creating items.
   *
   * @param array $collections
   * @param string|null $parent
   *
   * @return void
   */
  protected function prepCollection(array &$collections, string $parent = NULL): void {
    // Get the starting weight, or 0 if no grouping for collections is set.
    $group_collections_weight = [
      'bottom' => 100,
      'top' => -100,
    ][$this->config->get('common.group_collections')] ?? 0;

    foreach ($collections as $id => &$collection) {

      // The id represents the hierarchy of the collections, so add the parent.
      $collection['id'] = $parent ? "{$parent}.{$id}" : $id;

      // If the parent is an id from a link we have generated from within this
      // plugin add the base definition of this plugin.
      if (isset($this->links[$parent])) {
        $collection['parent'] = $this->base_menu_link_plugin_definition['id'] . ':' . $parent;
      }
      else {
        $collection['parent'] = $parent;
      }

      // Use group collections weight if group collections is enabled.
      if (!empty($group_collections_weight) && empty($collection['weight'])) {
        $collection['weight'] = $group_collections_weight;
        $group_collections_weight++;
      }

      if (isset($collection['collections'])) {
        asort($collection['collections']);
        $this->prepCollection($collection['collections'], $collection['id']);
      }
    }
  }

  /**
   * Get an array of items to create item links for.
   *
   * By default, this will get entities of the type given in the 'entity_type'
   * annotation attribute of the plugin definition.
   *
   * @return array
   */
  protected function getItems(): array {

    if (empty($this->items)) {
      try {
        $definition = $this->getPluginDefinition();
        $this->items = $this->entityTypeManager
          ->getStorage($definition['entity_type'])
          ->loadMultiple();
      } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
        return [];
      }
    }

    return $this->items;
  }

  /**
   * Creates a new menu link item.
   *
   * The new menu item is created in the 'admin' menu under 'system.admin'.
   *
   * The id of the menu link is the plugin id.
   *
   * Example:
   * $this->links['content']
   *
   * @param mixed $title
   *   Either a string or a TranslatableMarkup object.
   * @param string $route_name
   *   The route this menu item should point to.
   * @param array $route_parameters
   *   Optional parameters for the given route.
   * @param int $weight
   *   Optional weight for the menu item.
   *
   * @return void
   */
  protected function createRootLink(mixed $title, string $route_name, array $route_parameters = [], int $weight = 0): void {
    if ($this->isRouteAvailable($route_name, $route_parameters)) {
      $this->links[$this->getPluginId()] = [
        'title' => $title,
        'route_name' => $route_name,
        'route_parameters' => $route_parameters,
        'menu_name' => 'admin',
        'parent' => 'system.admin',
        'weight' => $weight,
      ] + $this->base_menu_link_plugin_definition;
    }
  }

  /**
   * Creates the collection links menu items tree.
   *
   * Example:
   *
   * $links['content.collection_a']
   * $links['content.collection_b']
   *
   * @param string $route
   * @param array $route_parameters
   * @param array|null $collections
   *
   * @return void
   *
   * @see \Drupal\admin_toolbar_content\AdminToolbarContentPluginBase::getCollections()
   */
  protected function createCollectionLinks(string $route, array $route_parameters = [], array $collections = NULL): void {

    if (!isset($collections)) {
      $collections = $this->getCollections();
    }

    foreach ($collections as $collection) {

      $this->createCollectionLink($collection, $route, $route_parameters);

      if (!empty($collection['collections'])) {
        $this->createCollectionLinks($route, $route_parameters, $collection['collections']);
      }

    }
  }

  /**
   * Creates a single collection link.
   *
   * It's up to the extending class to add:
   * - route_name
   * - route_parameters
   * - parent
   * - additional classes
   *
   * @param array $collection
   *  The collection to create a link for.
   *
   * @return void
   */
  protected function createCollectionLink(array $collection, $route_name, $route_parameters = []): void {

    $route_parameters = [
        'collection' => $collection['id'],
      ] + $route_parameters;

    if ($this->isRouteAvailable($route_name, $route_parameters)) {
      $this->links[$collection['id']] = [
        'title' => $collection['label'],
        'route_name' => $route_name,
        'route_parameters' => $route_parameters,
        'menu_name' => 'admin',
        'parent' => $collection['parent'],
        'weight' => $collection['weight'] ?? 0,
        'options' => [
          'attributes' => [
            'class' => [
              'admin-toolbar-content--collection',
              'admin-toolbar-content--collection--' . $this->getPluginId(),
              'admin-toolbar-content--collection--' . $this->getPluginId() . '--' . str_replace('.', '--', $collection['id']),
            ],
          ],
        ],
      ] + $this->base_menu_link_plugin_definition;
    }

  }

  /**
   * Helper to get the translation of an item in a collection.
   *
   * @param $item
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected function getItemTitle($item) {
    return $item->label();
  }

  /**
   * Creates the item links for a collection.
   *
   * @param string $route_name
   * @param string $route_item_parameter
   *
   * @return void
   */
  protected function createItemLinks(string $route_name, string $route_item_parameter): void {

    $items = $this->getItems();

    foreach ($items as $item) {
      $this->createItemLink($item, $route_name, $route_item_parameter);
    }

  }

  /**
   * Creates a single item link for a collection.
   *
   * @param mixed $item
   *  The item to create the link for.
   * @param string $route_name
   * @param string $route_item_parameter
   *
   * @return void
   */
  protected function createItemLink(mixed $item, string $route_name, string $route_item_parameter): void {

    if ($this->isRouteAvailable($route_name)) {

      // Find the collection for the item and default to the plugin id
      // if not found.
      $collection = $this->getItemCollection($item) ;

      $definition =  $this->getPluginDefinition();

      $route_parameters = [
        $route_item_parameter => $item->id(),
      ];

      if ($collection['id'] !== $this->getPluginId()) {
        $route_parameters['collection'] = $collection['id'];
      }

      $this->links[$collection['id'] . '.' . $item->id()] = [
        'class' => 'Drupal\admin_toolbar_tools\Plugin\Menu\MenuLinkEntity',
        //'title' => $this->getItemTitle($item),
        'route_name' => $route_name,
        'route_parameters' => $route_parameters,
        'menu_name' => 'admin',
        'parent' => $this->base_menu_link_plugin_definition['id'] . ':' . $collection['id'],
        'options' => [
          'attributes' => [
            'class' => [
              'admin-toolbar-content--edit-item',
              'admin-toolbar-content--edit-item--' . $item->id()
            ]
          ]
        ],
        'metadata' => [
          'entity_type' => $definition['entity_type'],
          'entity_id' => $item->id(),
        ],
      ] + $this->base_menu_link_plugin_definition;
    }
  }

  /**
   * Creates the add new item links.
   *
   * @param string $route_name
   * @param array $route_parameters
   *
   * @return void
   */
  protected function createItemAddLinks(string $route_name, array $route_parameters = []): void {

    $items = $this->getItems();

    foreach ($items as $item) {
      $this->createItemAddLink($item, $route_name, $route_parameters);
    }

  }

  /**
   * Creates a single add new item link for an existing item link.
   *
   * createItemLinks needs to run first.
   *
   * @param mixed $item
   *  The item to create the link for.
   * @param string $route_name
   * @param array $route_parameters
   *
   * @return void
   */
  protected function createItemAddLink(mixed $item, string $route_name, array $route_parameters = []): void {

    // Find the collection for the item and default to the plugin id
    // if not found.
    $collection = $this->getItemCollection($item);

    if (isset($this->links[$collection['id'] . '.' . $item->id()])) {

      if ($this->isRouteAvailable($route_name)) {

        $definition = $this->getPluginDefinition();

        $route_parameters = [
            $definition['entity_type'] => $item->id()
          ] + $route_parameters;

        $this->links[$collection['id'] . '.' . $item->id() . '.add'] = [
          'title' => $this->t('Add new'),
          'route_name' => $route_name,
          'route_parameters' => $route_parameters,
          'menu_name' => 'admin',
          'parent' => $this->base_menu_link_plugin_definition['id'] . ':' . $collection['id'] . '.' . $item->id(),
          'options' => [
            'attributes' => [
              'class' => [
                'admin-toolbar-content--add-new-item',
                'admin-toolbar-content--add-new-item--' . $item->id()
              ]
            ]
          ],
          'metadata' => [
            'entity_type' => $definition['entity_type'],
            'entity_id' => $item->id(),
          ],
        ] + $this->base_menu_link_plugin_definition;
      }
    }
  }

  /**
   * Creates the recent content links for items.
   *
   * @param string $type
   * @param array $route_parameters
   *
   * @return void
   */
  protected function createItemRecentContentLinks(string $type, string $route_name, array $route_parameters = []): void {

    $count = $this->config->get($this->getPluginId() . '.recent_items') ?? 0;

    if (empty($count)) {
      return;
    }

    $items = $this->getItems();

    foreach ($items as $item) {
      $this->createItemRecentContentEditLinks($item, $type, $count, $route_name, $route_parameters);
    }

  }

  /**
   * @param mixed $item
   * @param string $entity_type
   * @param int $count
   * @param string $route_name
   * @param array $route_parameters
   *
   * @return void
   */
  protected function createItemRecentContentEditLinks(mixed $item, string $entity_type, int $count, string $route_name, array $route_parameters = []): void {

    $collection = $this->getItemCollection($item);

    // If the item link is not there, we can't add recent items.
    if (!isset($this->links[$collection['id'] . '.' . $item->id()])) {
      return;
    }

    // Get the recent content items
    /** @var \Drupal\node\NodeStorageInterface $entity_storage */
    $entity_storage = \Drupal::service('entity_type.manager')->getStorage($entity_type);
    $ids = $entity_storage->getQuery()
      // We don't do the access check here, we let menu handle that.
      // Otherwise, the check is performed on the user doing the rebuild.
      ->accessCheck(FALSE)
      ->condition('type', $item->id())
      // Get One more than we need, so we can check if we need a 'More' item.
      ->pager($count + 1)
      ->sort('changed', 'DESC')
      ->execute();

    if (empty($ids)) {
      return;
    }

    $this->links[$collection['id'] . '.' . $item->id() . '.recent'] = [
      'title' => $this->t('Recent items'),
      'route_name' => '<none>',
      'parent' => $this->base_menu_link_plugin_definition['id'] . ':' . $collection['id'] . '.' . $item->id(),
      'options' => [
        'attributes' => [
          'class' => [
            'admin-toolbar-content--recent-items',
            'admin-toolbar-content--recent-items--' . $item->id()
          ]
        ]
      ],
      // We need to override admin_toolbar_tools MenuLinkEntity::getTitle().
      'class' => "Drupal\Core\Menu\MenuLinkDefault",
    ] + $this->links[$collection['id'] . '.' . $item->id()];

    $counter = 0;
    foreach ($ids as $id) {
      // Skip the last one, which is used to determine if we need a 'More' item.
      if ($counter++ == $count) {
        break;
      }

      /** @var \Drupal\node\NodeInterface $entity */
      $entity = $entity_storage->load($id);
      if ($entity === NULL) {
        continue;
      } // HACK fix, getQuery can return results which load can't load???

      // Default to the entity node edit form.
      $route_parameters = [
          $entity_type => $entity->id(),
        ] + $route_parameters;

      if ($this->isRouteAvailable($route_name)) {
        $this->createItemRecentContentEditLink($collection, $item, $entity, $route_name, $route_parameters, $counter + 1);
      }
    }

    if (count($ids) > $count) {
      $this->links[$collection['id'] . '.' . $item->id() . '.more'] = [
        'title' => $this->t('More'),
        'weight' => $count + 2,
        'parent' => $this->base_menu_link_plugin_definition['id'] . ':' . $collection['id'] . '.' . $item->id(),
        'options' => [
          'attributes' => [
            'class' => [
              'admin-toolbar-content--more-recent-items',
              'admin-toolbar-content--more-recent-items--' . $item->id()
            ],
          ],
        ],
        // We need to override admin_toolbar_tools MenuLinkEntity::getTitle().
        'class' => "Drupal\Core\Menu\MenuLinkDefault",
      ] + $this->links[$collection['id'] . '.' . $item->id()];
    }
  }

  /**
   * @param array $collection
   * @param mixed $item
   * @param mixed $entity
   * @param string $route_name
   * @param array $route_parameters
   * @param int $weight
   *
   * @return void
   */
  protected function createItemRecentContentEditLink(array $collection, mixed $item, mixed $entity, string $route_name, array $route_parameters, int $weight = 0): void {

    /** @var ContentEntityInterface $entity */

    $this->links[$collection['id'] . '.' . $item->id() . '.entity.' . $entity->id()] = [
      'class' => 'Drupal\admin_toolbar_tools\Plugin\Menu\MenuLinkEntity',
      'route_name' => $route_name,
      'route_parameters' => $route_parameters,
      'menu_name' => 'admin',
      'weight' => $weight,
      'parent' => $this->base_menu_link_plugin_definition['id'] . ':' . $collection['id'] . '.' . $item->id(),
      'options' => [
        'attributes' => [
          'class' => [
            'admin-toolbar-content--recent-item',
            'admin-toolbar-content--recent-item--' . $item->id()
          ]
        ]
      ],
      'metadata' => [
        'entity_type' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
      ],
    ] + $this->base_menu_link_plugin_definition;
  }

}
