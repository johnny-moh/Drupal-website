<?php
namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An AdminToolbarContentDrupalMenuPlugin for altering the system drupal menu.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "drupal",
 *   name = @Translation("Drupal menu"),
 *   description = @Translation("Alters the main Drupal menu item.")
 * )
 */
class AdminToolbarContentDrupalMenuPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigForm(array &$form, FormStateInterface $form_state): array {
    $elements = parent::buildConfigForm($form, $form_state);

    $elements['account_links'] = [
      '#type' => 'radios',
      '#title' => $this->t('User account link'),
      '#description' => $this->t('Links to user account pages.'),
      '#options' => [
        '' => $this->t('Show no link'),
        'user' => $this->t('Link to user page'),
        'edit' => $this->t('Link to account edit form'),
        'both' => $this->t('Link to both'),
      ],
      '#default_value' => $this->config->get('drupal.account_links') ?? ''
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {

    $setting = $this->config->get('drupal.account_links') ?? '';

    if ($setting == 'user' || $setting == 'both') {
      $this->links['user.entity'] = [
        'title' => $this->t('My account'),
        'route_name' => 'user.page',
        'route_parameters' => [],
        'menu_name' => 'admin',
        'parent' => 'admin_toolbar_tools.help',
        'weight' => 9,
      ] + $this->base_menu_link_plugin_definition;
    }

    if ($setting == 'edit' || $setting == 'both') {
      $this->links['user.entity.' . $this->currentUser->id()] = [
        'title' => $setting == 'both' ? $this->t('Edit my account') : $this->t('My account'),
        'route_name' => "entity.user.edit_form",
        'route_parameters' => [
          'user' => $this->currentUser->id()
        ],
        'menu_name' => 'admin',
        'weight' => 10,
        'parent' => 'admin_toolbar_tools.help'
      ] + $this->base_menu_link_plugin_definition;
    }

  }

}
