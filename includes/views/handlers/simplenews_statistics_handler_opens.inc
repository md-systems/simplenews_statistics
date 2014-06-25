<?php

/**
 * @file
 * Definition of simplenews_statistics_handler_opens.
 */

/**
 * Description.
 */
class simplenews_statistics_handler_opens extends views_handler_field {
  /**
   * Add some required fields needed on render().
   */
  function construct() {
    parent::construct();
    $this->additional_fields['nid'] = array(
      'table' => 'node',
      'field' => 'nid',
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

    //$options['unique'] = array('default' => '');
    //$options['open_rate'] = array('default' => '');

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    //$form['unique'] = array(
    //  '#type' => 'checkbox',
    //  '#title' => t('Unique'),
    //  '#default_value' => $this->options['unique'],
    //  '#description' => t('Some description.'),
    //  '#weight' => -10,
    //);
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    // $this->options.

    $nid = $values->nid;
    // @todo: Require this as a field.
    $newsletter = simplenews_newsletter_load($nid);

    $sent_count = $newsletter->sent_subscriber_count;
    $total_opens = simplenews_statistics_get_open_count($nid);
    // @todo: Make it possible to return this.
    $unique_opens = simplenews_statistics_get_open_count($nid, TRUE);

    // @todo: Make it possible to return this.
    $open_rate = 0;
    if ($sent_count != 0) {
      $open_rate = round($unique_opens / $sent_count * 100, 0);
    }

    return $total_opens;
  }

}
