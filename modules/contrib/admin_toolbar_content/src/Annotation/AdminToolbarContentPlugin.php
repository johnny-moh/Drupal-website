<?php

namespace Drupal\admin_toolbar_content\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an admin toolbar content plugin.
 *
 * Plugin Namespace: Plugin\AdminToolbarContent
 *
 * @see \Drupal\admin_toolbar_content\AdminToolbarContentPluginInterface
 * @see \Drupal\admin_toolbar_content\AdminToolbarContentPluginManager
 *
 * @Annotation
 */
class AdminToolbarContentPlugin extends Plugin {

  /**
   * The admin toolbar content plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the admin toolbar content plugin.
   *
   * @insite plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $name;

  /**
   * The description of the admin toolbar content plugin.
   *
   * @insite plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;


}
