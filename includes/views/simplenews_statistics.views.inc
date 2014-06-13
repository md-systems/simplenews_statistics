<?php

/**
 * @file
 * Main simplenews statistics views integration file.
 */

/**
 * Implements hook_views_data().
 */
function simplenews_statistics_views_data() {
  // Views integration for the clicks table.
  $data['simplenews_statistics_clicks']['table']['group'] = t('Simplenews Statistics Clicks');
  $data['simplenews_statistics_clicks']['table']['base'] = array(
    'field' => 'cid',
    'title' => t('Simplenews Statistics clicks'),
  );
  $data['simplenews_statistics_clicks']['cid'] = array(
    'title' => t('ID'),
    'help' => t('The click ID for this record.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );
  $data['simplenews_statistics_clicks']['email'] = array(
    'title' => t('E-mail'),
    'help' => t('The email address.'),
    'field' => array(
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_string',
      'allow empty' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  $data['simplenews_statistics_clicks']['nid'] = array(
    'title' => t('Nid'),
    'help' => t('The simplenews node ID for which this click was registered.'),
    'relationship' => array(
      'title' => t('Content'),
      'help' => t('The simplenews node for which this click was registered.'),
      'base' => 'node',
      'base field' => 'nid',
      'handler' => 'views_handler_relationship',
      'label' => t('Content'),
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
    ),
  );
  $data['simplenews_statistics_clicks']['urlid'] = array(
    'title' => t('URLid'),
    'help' => t('The URL ID for this click.'),
    'relationship' => array(
      'title' => t('URL'),
      'help' => t('The URL for this click.'),
      'base' => 'simplenews_statistics_urls',
      'base field' => 'urlid',
      'handler' => 'views_handler_relationship',
      'label' => t('URL'),
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
    ),
  );
  $data['simplenews_statistics_clicks']['timestamp'] = array(
    'title' => t('Timestamp'),
    'help' => t('The time the user clicked this link.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
      'allow empty' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_date',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
  );

  // Views integration for the urls table.
  $data['simplenews_statistics_urls']['table']['group'] = t('Simplenews Statistics URLs');
  $data['simplenews_statistics_urls']['table']['base'] = array(
    'field' => 'urlid',
    'title' => t('Simplenews Statistics URLs'),
  );
  $data['simplenews_statistics_urls']['urlid'] = array(
    'title' => t('ID'),
    'help' => t('The URL ID for this record.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );
  $data['simplenews_statistics_clicks']['nid'] = array(
    'title' => t('Nid'),
    'help' => t('The simplenews node ID for which this click was registered.'),
    'relationship' => array(
      'title' => t('Content'),
      'help' => t('The simplenews node for which this click was registered.'),
      'base' => 'node',
      'base field' => 'nid',
      'handler' => 'views_handler_relationship',
      'label' => t('Content'),
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
    ),
  );
  $data['simplenews_statistics_urls']['url'] = array(
    'title' => t('URL'),
    'help' => t('The clicked URL.'),
    'field' => array(
      'handler' => 'views_handler_field_url',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_string',
      'allow empty' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  // Views integration for the opens table.
  $data['simplenews_statistics_opens']['table']['group'] = t('Simplenews Statistics Opens');
  $data['simplenews_statistics_opens']['table']['base'] = array(
    'field' => 'oid',
    'title' => t('Simplenews Statistics Opens'),
  );
  $data['simplenews_statistics_opens']['email'] = array(
    'title' => t('E-mail'),
    'help' => t('The email address.'),
    'field' => array(
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_string',
      'allow empty' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  $data['simplenews_statistics_opens']['nid'] = array(
    'title' => t('Nid'),
    'help' => t('The simplenews node ID for which the email was opened.'),
    'relationship' => array(
      'title' => t('Content'),
      'help' => t('The simplenews node for which the email was opened.'),
      'base' => 'node',
      'base field' => 'nid',
      'handler' => 'views_handler_relationship',
      'label' => t('Content'),
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
    ),
  );
  $data['simplenews_statistics_opens']['timestamp'] = array(
    'title' => t('Timestamp'),
    'help' => t('The time at which the user opened the email.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
      'allow empty' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_date',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
  );

  return $data;
}
