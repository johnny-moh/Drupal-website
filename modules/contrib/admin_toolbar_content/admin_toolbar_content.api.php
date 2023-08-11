<?php

/**
 * @file
 * Documentation for admin_toolbar_content API.
 */

/**
 * Provide an array describing collections for each plugin.
 *
 * @see \Drupal\admin_toolbar_content\AdminToolbarContentPluginBase
 * @see \Drupal\admin_toolbar_content\Plugin\Derivative\AdminToolbarContentMenuLinks
 *
 * Collections are used to group items together in the administration menu.
 * They can define a hierarchical collections structure:
 *
 * 'plugin_a' => [
 *   'collection_a' => [
 *     'label' => 'COLLECTION A',
 *     'items' => [
 *       'item a',
 *       'item b'
 *     ],
 *     'collections' => [
 *       'collection_a_1' => [
 *         'label' => 'COLLECTION A.1',
 *         'items' => [
 *           'item c'
 *         ]
 *       ]
 *     ]
 *   ]
 * ],
 * 'plugin_b' => [
 *   ...
 * ]
 *
 * This will generate the following links structure
 *
 * Root link:
 * $links['plugin_a']
 *
 * Collection links:
 * $links['plugin_a.collection_a']
 * $links['plugin_a.collection_a.collection_a_1']
 *
 * Item links:
 * $links['plugin_a.collection_a.item_a']
 * $links['plugin_a.collection_a.item_b']
 * $links['plugin_a.collection_a.collection_a_1.item_c']
 *
 * Item add links:
 * $links['plugin_a.collection_a.item_a.add']
 * $links['plugin_a.collection_a.item_b.add']
 * $links['plugin_a.collection_a.collection_a_1.item_c.add']
 *
 * @return array
 *   A hierarchical collection definition, keyed by plugin id.
 */
function hook_admin_toolbar_content_collections(): array {
  return [
    'plugin_a' => [
      'collection_a' => [
        'label' => 'COLLECTION A',
        'items' => [
          'item a',
          'item b'
         ],
        'collections' => [
          'collection_a_1' => [
            'label' => 'COLLECTION A.1',
            'items' => [
              'item c'
            ]
          ]
        ]
      ]
    ]
  ];
}
