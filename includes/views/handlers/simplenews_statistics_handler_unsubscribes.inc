<?php

/**
 * @file
 * Definition of simplenews_statistics_handler_unsubscribes.
 */

/**
 * Description.
 */
class simplenews_statistics_handler_unsubscribes extends views_handler_field {
  /**
   * Add some required fields needed on render().
   */
  function construct() {
    parent::construct();
    $this->additional_fields['nid'] = array(
      'table' => 'node',
      'field' => 'nid',
    );
    $this->additional_fields['send_start_timestamp'] = array(
      'table' => 'simplenews_statistics',
      'field' => 'send_start_timestamp',
    );
    $this->additional_fields['tid'] = array(
      'table' => 'simplenews_newsletter',
      'field' => 'tid',
    );
  }

  /**
   * Loads additional fields.
   */
  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * Default options form.
   */
  function option_definition() {
    $options = parent::option_definition();

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    //dpm($values);

    $start = $values->simplenews_statistics_send_start_timestamp;
    $tid = $values->simplenews_newsletter_tid;

    if ($start == 0) {
      return 'N/A';
    }

    // If exists, get next newsletter since sent date.
    $query = db_select('simplenews_statistics', 'ss')
      ->fields('ss', array('send_start_timestamp'))
      ->condition('ss.send_start_timestamp', $start, '>');
    $end = $query->execute()->fetchField();

    if ($end == FALSE) {
      // @todo: Count unsubs in between those dates (or now).
      $unsubs = simplenews_statistics_count_unsubscribes($tid);
    }

    return '0';
  }

}
