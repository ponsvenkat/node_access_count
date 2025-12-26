<?php

/**
 * @file
 * Provides Views integration for Node Access Count module.
 */

/**
 * Implements hook_views_data().
 */
function node_access_count_views_data() {
  $data = [];

  // Define the base table (Node Summary Table).
  $data['node_access_summary'] = [
    'table' => [
      'group' => t('Node Access Count'),
      'provider' => 'node_access_count',

      // Define how this table joins to node_field_data.
      'join' => [
        // Joins this table to the 'node_field_data' base table on 'nid'.
        'node_field_data' => [
          'left_field' => 'nid',
          'field' => 'nid',
          'type' => 'LEFT',
        ],
      ],
    ],
  ];

  //Add total_views field.
  $data['node_access_summary']['total_views'] = [
    'title' => t('Total views'),
    'help' => t('The total number of times this node has been viewed.'),
    'field' => [
      'id' => 'numeric', // Display handler for numeric fields.
    ],
    'filter' => [
      'id' => 'numeric', // Allow filtering by number of views.
    ],
    'sort' => [
      'id' => 'standard', // Allow sorting.
    ],
  ];

  // Add last_updated field.
  $data['node_access_summary']['last_updated'] = [
    'title' => t('Last viewed time'),
    'help' => t('The last time this node was viewed.'),
    'field' => [
      'id' => 'date', // Display as a date/time.
    ],
    'filter' => [
      'id' => 'date', // Allow filtering by date range.
    ],
    'sort' => [
      'id' => 'standard', // Allow sorting by time.
    ],
  ];

  // Define the base table (User Tracking Table).
  $data['node_access_count'] = [
    'table' => [
      'group' => t('Node Access Count - User Logs'),
      'provider' => 'node_access_count',
      'base' => [
        'field' => 'id',
        'title' => t('Node Access Logs'),
        'help' => t('Logs of users who viewed nodes.'),
      ],
      // Define how this table joins to node_field_data.
      'join' => [
        // Joins this table to the 'node_field_data' base table on 'nid'.
        'node_field_data' => [
          'left_field' => 'nid',
          'field' => 'nid',
          'type' => 'LEFT',
        ],
        'users_field_data' => [
          'left_field' => 'uid',
          'field' => 'uid',
          'type' => 'LEFT',
        ],
      ],
    ],
  ];

  // Relationship to node
  $data['node_access_count']['nid'] = [
    'title' => t('Content ID'),
    'help' => t('The node ID that was accessed.'),
    'relationship' => [
      'title' => t('Content'),
      'help' => t('Relate access logs to content.'),
      'base' => 'node_field_data',
      'base field' => 'nid',
      'id' => 'standard',
    ],
    // Add this to make it available as contextual filter
    'argument' => [
      'title' => t('Node ID'),
      'help' => t('Filter logs by node ID (contextual filter).'),
      'id' => 'numeric',
    ],
  ];

    //Add total_views field.
  $data['node_access_count']['uid'] = [
    'title' => t('User ID'),
    'help' => t('The user ID of the person who viewed the node.'),
    'field' => [
      'id' => 'numeric', // Display handler for numeric fields.
    ],
    'filter' => [
      'id' => 'numeric', // Allow filtering by user ID.
    ],
    'sort' => [
      'id' => 'standard', // Allow sorting.
    ],
    'relationship' => [
        'title' => t('User'),
        'help' => t('Relate node access log to the User entity (based on uid).'),
        'base' => 'users_field_data',
        'base field' => 'uid',
        'id' => 'standard',
    ],
    // Add this to make it available as contextual filter
    'argument' => [
      'title' => t('User ID'),
      'help' => t('Filter logs by user ID (contextual filter).'),
      'id' => 'numeric',
    ],
  ];

  // Add viewed time field.
  $data['node_access_count']['timestamp'] = [
    'title' => t('viewed time'),
    'help' => t('The time this node was viewed by the user.'),
    'field' => [
      'id' => 'date', // Display as a date/time.
    ],
    'filter' => [
      'id' => 'date', // Allow filtering by date range.
    ],
    'sort' => [
      'id' => 'standard', // Allow sorting by time.
    ],
  ];

  return $data;
}
