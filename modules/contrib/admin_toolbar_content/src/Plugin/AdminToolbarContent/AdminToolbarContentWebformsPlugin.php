<?php
namespace Drupal\admin_toolbar_content\Plugin\AdminToolbarContent;

use Drupal\admin_toolbar_content\AdminToolbarContentPluginBase;

/**
 * An AdminToolbarContentPlugin adding a new main 'Webform submissions' menu item.
 *
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks.
 *
 * @AdminToolbarContentPlugin(
 *   id = "webform",
 *   name = @Translation("Webforms"),
 *   description = @Translation("Adds a 'Webforms' item to the admin menu."),
 *   entity_type = "webform"
 * )
 */
class AdminToolbarContentWebformsPlugin extends AdminToolbarContentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function createMenuLinkItems(): void {
    $this->createRootLink($this->t("Webform submissions"), "entity.webform_submission.collection", [], -5);
    $this->createCollectionLinks("entity.webform_submission.collection");
    $this->createItemLinks("entity.webform.results_submissions", "webform");
  }

}
