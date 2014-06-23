<?php

/**
 * @file
 * Contains page function for simplenews statistics.
 */

/**
 * Check and log newsletter opens.
 */
function simplenews_statistics_open_page($nid, $snid, $terminate = TRUE) {
  // Call possible decoders for the snid in modules implementing the hook.
  // Modules implementing this hook should validate this input themself because
  // we can not know what kind of string they will generate.
  $hook = 'simplenews_statistics_decode_snid';
  foreach (module_implements($hook) as $module) {
    $function = $module . '_' . $hook;
    if (function_exists($function)) {
      $snid = $function($snid);
    }
  }

  // Once decoded properly we can know for sure that the $snid is numeric.
  if (!is_numeric($snid)) {
    watchdog('simplenews_statistics', 'Simplenews statistics open called with illegal parameter.');
  }
  else {
    $subscriber = simplenews_subscriber_load($snid);
    if (!empty($subscriber) && $subscriber->snid === $snid) {
      $record = new stdClass();
      $record->snid = $subscriber->snid;
      $record->nid = $nid;
      $record->timestamp = time();
      drupal_write_record('simplenews_statistics_open', $record);
    }
  }

  if ($terminate == FALSE) {
    return; // Allow PHP execution to continue.
  }

  // Render a transparent image and stop PHP execution.
  $type = 'image/png; utf-8';
  $file = drupal_get_path('module', 'simplenews_statistics') . '/images/count.png';

  drupal_add_http_header('Content-Type', $type);
  drupal_add_http_header('Content-Length', filesize($file));
  // @todo: Set some more headers like cache.

  readfile($file);
  drupal_exit();
}

/**
 * Check and log newsletter URL clicks.
 */
function simplenews_statistics_click_page($urlid) {
  // Call possible decoders for the urlid in modules implementing the hook.
  $hook = 'simplenews_statistics_decode_urlid';
  foreach (module_implements($hook) as $module) {
    $function = $module . '_' . $hook;
    if (function_exists($function)) {
      $urlid = $function($urlid);
    }
  }

  // Once decoded properly we can know for sure that the $snid and $urlid are
  // numeric. Fallback on any error is to go to the homepage.
  if (!is_numeric($urlid)) {
    watchdog('simplenews_statistics', 'Simplenews statistics click called with illegal parameter.');
    drupal_goto();
  }

  // Track click if there is an url and a valid subscriber. For test mails the
  // snid can be 0, so no valid subscriber will be loaded and the click won't
  // be counted. But the clicked link will still redirect properly.
  $query = db_select('simplenews_statistics_url', 'ssu')
    ->fields('ssu')
    ->condition('urlid', $urlid);
  $url_record = $query->execute()->fetchObject();

  $snid = $url_record->snid;
  $nid = $url_record->nid;

  $subscriber = simplenews_subscriber_load($snid);
  if ($url_record) {
    $click_record = new stdClass();
    $click_record->urlid = $urlid;
    $click_record->timestamp = time();
    drupal_write_record('simplenews_statistics_click', $click_record);

    // Check if the open action was registered for this subscriber. If not we
    // can track the open here to improve statistics accuracy.
    $query = db_select('simplenews_statistics_open', 'sso');
    $query->condition('snid', $snid);
    $num_rows = $query->countQuery()->execute()->fetchField();
    if ($num_rows == 0) {
      simplenews_statistics_open_page($nid, $snid, FALSE);
    }
  }

  // Redirect to the right URL.
  if (!empty($url_record) && !empty($url_record->url)) {
    // Split the URL into it's parts for easier handling.
    $path = $url_record->url;
    $options = array(
      'fragment' => '',
      'query' => array(),
    );

    // The fragment should always be after the query, so we get that first.
    // We format it for the options array for drupal_goto().
    $fragment_position = strpos($path, '#');
    if (FALSE !== $fragment_position) {
      $fragment = substr($path, $fragment_position + 1);
      $path = str_replace('#' . $fragment, '', $path);
      $options['fragment'] = $fragment;
    }
    // Determine the position of the query string, get it, delete it from the
    // original path and then we explode the parts and the key-value pairs to
    // get a clean output that we can use in drupal_goto().
    $query_position = strpos($path, '?');
    if (FALSE !== $query_position) {
      $query = substr($path, $query_position + 1);
      $path = str_replace('?' . $query, '', $path);
      $element = explode('&', $query);
      foreach ($element as $pair) {
        $pair = explode('=', $pair);
        if (!isset($pair[1])) {
          $pair[1] = '';
        }
        $options['query'][$pair[0]] = $pair[1];
      }
    }

    // Call possible rewriters for the url.
    $hook = 'simplenews_statistics_rewrite_goto_url';
    foreach (module_implements($hook) as $module) {
      $function = $module . '_' . $hook;
      if (function_exists($function)) {
        $function($path, $options, $snid, $nid);
      }
    }
    // Fragment behaviour can get out of spec here.
    drupal_goto($path, $options);
  }

  // Fallback on any error is to go to the homepage.
  drupal_goto();
}

/**
 * Page callback for simplenews statistics overview.
 *
 * WIP.
 * @todo: How to tie this in to views?
 */
function simplenews_statistics_page($nid) {
  $newsletter = simplenews_newsletter_load($nid);

  $subscriber_count = simplenews_statistics_get_subscriber_count($newsletter);
  $sent_count = $newsletter->sent_subscriber_count;
  $total_opens = simplenews_statistics_get_open_count($nid);
  $unique_opens = simplenews_statistics_get_open_count($nid, TRUE);
  $clicks = simplenews_statistics_get_clicks($nid);

  $total_clicks = 0;
  foreach ($clicks as $click) {
    $total_clicks += $click->clicks;
  }

  $open_rate = 0;
  if ($subscriber_count != 0) {
    $open_rate = round($unique_opens / $sent_count * 100, 0);
  }

  $unique_clicks = simplenews_statistics_unique_opened_and_clicked_count($nid);
  $ctr = 0;
  if ($unique_opens != 0) {
    $ctr = round($unique_clicks / $unique_opens * 100, 0);
  }

  $statistics = array(
    'sent_count' => $sent_count,
    'subscriber_count' => $subscriber_count,
    'total_opens' => $total_opens,
    'unique_opens' => $unique_opens,
    'clicks' => $clicks,
    'total_clicks' => $total_clicks,
    'open_rate' => $open_rate,
    'ctr' => $ctr,
  );

  return theme('simplenews_statistics', array('statistics' => $statistics));
}
