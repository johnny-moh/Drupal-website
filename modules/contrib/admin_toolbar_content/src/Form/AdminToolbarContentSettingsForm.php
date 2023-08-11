<?php

namespace Drupal\admin_toolbar_content\Form;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminToolbarContentSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface
   */
  protected AdminToolbarContentPluginManagerInterface $pluginManager;

  /**
   * Constructs a AdminToolbarContentSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\admin_toolbar_content\AdminToolbarContentPluginManagerInterface $pluginManager
   */
  public function __construct(ConfigFactoryInterface $configFactory, ModuleHandlerInterface $module_handler, AdminToolbarContentPluginManagerInterface $pluginManager) {
    parent::__construct($configFactory);
    $this->moduleHandler = $module_handler;
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('admin_toolbar_content.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_toolbar_content';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'admin_toolbar_content.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('admin_toolbar_content.settings');

    $form['#title'] = $this->t('Admin Toolbar Content');

    $form['#attached']['library'][] = 'field_group/element.horizontal_tabs';

    $form['tabs'] = array(
      '#type' => 'horizontal_tabs',
      '#prefix' => '<div id="police-site-configuration-form-horizontal-tabs-wrapper">',
      '#suffix' => '</div>',
    );

    $form['tabs']['common'] = array(
      '#type' => 'details',
      '#title' => $this->t('Common'),
      '#tree' => TRUE,
      '#weight' => -99,
    );

    $form['tabs']['common']['group_collections'] = [
      '#type' => 'radios',
      '#title' => $this->t("Group collections"),
      '#description' => $this->t("Group collections to a specific place."),
      '#options' => [
        '' => $this->t("Don't Group"),
        'bottom' => $this->t("At bottom"),
        'top' => $this->t("At top"),
      ],
      '#default_value' => $config->get('common.group_collections') ?? '',
    ];

    $plugins = $this->pluginManager->getDefinitions();
    foreach ($plugins as $plugin_id => $definition) {
      try {
        /** @var \Drupal\admin_toolbar_content\AdminToolbarContentPluginInterface $plugin */
        $plugin = $this->pluginManager->createInstance($plugin_id, $definition);

        // Plugin tab.
        $form['tabs'][$plugin_id] = [
          '#type' => 'details',
          '#title' => $definition['name'],
          '#tree' => TRUE,
        ];

        // Plugin specific settings.
        $form['tabs'][$plugin_id] = $plugin->buildConfigForm($form,  $form_state) + (array) $form['tabs'][$plugin_id];

      } catch (\Drupal\Component\Plugin\Exception\PluginException $e) {
        // Silently continue if plugin is not found.
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Prepare values.
    $form_state->cleanValues();
    $values = $form_state->getValues();

    // Save the config.
    $config = $this->config('admin_toolbar_content.settings');
    $config->setData($values);
    $config->save();

    // Clear cache so admin menu can rebuild.
    \Drupal::service('plugin.manager.menu.link')->rebuild();

    parent::submitForm($form, $form_state);
  }

}
