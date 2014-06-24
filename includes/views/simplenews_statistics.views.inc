<?php

/**
 * @file
 * Simplenews Statistics views integration file.
 */

/**
 * Implements hook_views_data().
 */
function simplenews_statistics_views_data() {
  // Views definitions for the 'urls' table.
  $data['simplenews_statistics_url']['table'] = array(
    'base' => array(
      'field' => 'urlid',
      'title' => t('Simplenews Statistics URLs'),
    ),
    'group' => t('Simplenews Statistics'),
    'join' => array(
      'simplenews_newsletter' => array(
        'left_field' => 'nid',
        'field' => 'nid',
      ),
    ),
  );

  $data['simplenews_statistics_url']['urlid'] = array(
    'title' => t('URL ID'),
    'help' => t('The URL ID for a URL in a newsletter.'),
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

  $data['simplenews_statistics_url']['nid'] = array(
    'title' => t('Node ID'),
    'help' => t('The simplenews node ID in which the URL is stored.'),
    'relationship' => array(
      'title' => t('Newsletter'),
      'help' => t('The simplenews node in which the URL is stored.'),
      'base' => 'simplenews_newsletter',
      'base field' => 'nid',
      'handler' => 'views_handler_relationship',
      'label' => t('Newsletter'),
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );

  $data['simplenews_statistics_url']['url'] = array(
    'title' => t('URL'),
    'help' => t('The original URL.'),
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

  // Views definitions for the 'clicks' table.
  $data['simplenews_statistics_click']['table'] = array(
    'base' => array(
      'field' => 'clid',
      'title' => t('Simplenews Statistics clicks'),
    ),
    'group' => t('Simplenews Statistics'),
    'join' => array(
      'simplenews_newsletter' => array(
        'left_field' => 'nid',
        'field' => 'nid',
      ),
      'simplenews_subscriber' => array(
        'left_field' => 'snid',
        'field' => 'snid',
      ),
    ),
  );

  $data['simplenews_statistics_click']['clid'] = array(
    'title' => t('Click ID'),
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

  $data['simplenews_statistics_click']['urlid'] = array(
    'title' => t('URL ID'),
    'help' => t('The URL ID for this click.'),
    'relationship' => array(
      'title' => t('URL'),
      'help' => t('The URL for this click.'),
      'base' => 'simplenews_statistics_url',
      'base field' => 'urlid',
      'handler' => 'views_handler_relationship',
      'label' => t('URL'),
    ),
  );

  $data['simplenews_statistics_click']['snid'] = array(
    'title' => t('Simplenews subscriber'),
    'help' => t('The simplenews subscriber.'),
    'relationship' => array(
      'title' => t('Subscriber'),
      'help' => t('The subscriber tied to this URL record.'),
      'base' => 'simplenews_subscriber',
      'base field' => 'snid',
      'handler' => 'views_handler_relationship',
      'label' => t('Simplenews subscriber'),
    ),
  );

  $data['simplenews_statistics_click']['timestamp'] = array(
    'title' => t('Click timestamp'),
    'help' => t('The UNIX timestamp when the user clicked this link.'),
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

  // Views integration definitions for the 'opens' table.
  $data['simplenews_statistics_open']['table'] = array(
    'base' => array(
      'field' => 'opid',
      'title' => t('Simplenews Statistics opens'),
    ),
    'group' => t('Simplenews Statistics'),
    'join' => array(
      'simplenews_newsletter' => array(
        'left_field' => 'nid',
        'field' => 'nid',
      ),
      'simplenews_subscriber' => array(
        'left_field' => 'snid',
        'field' => 'snid',
      ),
    ),
  );

  $data['simplenews_statistics_open']['opid'] = array(
    'title' => t('Open ID'),
    'help' => t('The open ID for this record.'),
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

  $data['simplenews_statistics_open']['snid'] = array(
    'title' => t('Simplenews subscriber'),
    'help' => t('The simplenews subscriber.'),
    'relationship' => array(
      'title' => t('Subscriber'),
      'help' => t('The subscriber for which the email was opened.'),
      'base' => 'simplenews_subscriber',
      'base field' => 'snid',
      'handler' => 'views_handler_relationship',
      'label' => t('Simplenews subscriber'),
    ),
  );

  $data['simplenews_statistics_open']['nid'] = array(
    'title' => t('Node ID'),
    'help' => t('The simplenews node ID for which the email was opened.'),
    'relationship' => array(
      'title' => t('Newsletter'),
      'help' => t('The simplenews node for which the email was opened.'),
      'base' => 'simplenews_newsletter',
      'base field' => 'nid',
      'handler' => 'views_handler_relationship',
      'label' => t('Newsletter'),
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );

  $data['simplenews_statistics_open']['timestamp'] = array(
    'title' => t('Open timestamp'),
    'help' => t('The UNIX timestamp at which the user opened the email.'),
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
