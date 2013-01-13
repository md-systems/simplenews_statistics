<?php

/**
 * Implements hook_views_default_views().
 */
function simplenews_statistics_views_default_views() {
  $view = new view();
  $view->name = 'simplenews_statistics_clicks';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'simplenews_statistics_clicks';
  $view->human_name = 'Simplenews Statistics Clicks';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'cid' => 'cid',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'cid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Relationship: Simplenews Statistics Clicks: URL */
  $handler->display->display_options['relationships']['urlid']['id'] = 'urlid';
  $handler->display->display_options['relationships']['urlid']['table'] = 'simplenews_statistics_clicks';
  $handler->display->display_options['relationships']['urlid']['field'] = 'urlid';
  $handler->display->display_options['relationships']['urlid']['required'] = TRUE;
  /* Field: Simplenews Statistics Clicks: Timestamp */
  $handler->display->display_options['fields']['timestamp']['id'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['table'] = 'simplenews_statistics_clicks';
  $handler->display->display_options['fields']['timestamp']['field'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['date_format'] = 'long';
  /* Field: Simplenews Statistics Clicks: E-mail */
  $handler->display->display_options['fields']['email']['id'] = 'email';
  $handler->display->display_options['fields']['email']['table'] = 'simplenews_statistics_clicks';
  $handler->display->display_options['fields']['email']['field'] = 'email';
  /* Field: Simplenews Statistics URL's: URL */
  $handler->display->display_options['fields']['url']['id'] = 'url';
  $handler->display->display_options['fields']['url']['table'] = 'simplenews_statistics_urls';
  $handler->display->display_options['fields']['url']['field'] = 'url';
  $handler->display->display_options['fields']['url']['relationship'] = 'urlid';
  /* Sort criterion: Simplenews Statistics Clicks: Timestamp */
  $handler->display->display_options['sorts']['timestamp']['id'] = 'timestamp';
  $handler->display->display_options['sorts']['timestamp']['table'] = 'simplenews_statistics_clicks';
  $handler->display->display_options['sorts']['timestamp']['field'] = 'timestamp';
  $handler->display->display_options['sorts']['timestamp']['order'] = 'DESC';
  /* Contextual filter: Simplenews Statistics Clicks: Nid */
  $handler->display->display_options['arguments']['nid']['id'] = 'nid';
  $handler->display->display_options['arguments']['nid']['table'] = 'simplenews_statistics_clicks';
  $handler->display->display_options['arguments']['nid']['field'] = 'nid';
  $handler->display->display_options['arguments']['nid']['default_action'] = 'default';
  $handler->display->display_options['arguments']['nid']['default_argument_type'] = 'node';
  $handler->display->display_options['arguments']['nid']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['nid']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['nid']['summary_options']['items_per_page'] = '25';

  /* Display: Simplenews Statistics Clicks */
  $handler = $view->new_display('page', 'Simplenews Statistics Clicks', 'page_1');
  $handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
  $handler->display->display_options['path'] = 'node/%/simplenews_statistics/clicks';
  $handler->display->display_options['menu']['type'] = 'default tab';
  $handler->display->display_options['menu']['title'] = 'Clicks';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['tab_options']['type'] = 'tab';
  $handler->display->display_options['tab_options']['title'] = 'Simplenews Statistics';
  $handler->display->display_options['tab_options']['weight'] = '0';

  $views[$view->name] = $view;

  // return views
  return $views;
}