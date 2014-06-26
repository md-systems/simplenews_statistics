<?php

/**
 * @file
 * Definition of simplenews_statistics_handler_clicks.
 */

/**
 * Description.
 */
class simplenews_statistics_handler_clicks extends views_handler_field {
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

    $options['ctr'] = array('default' => 0);

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['ctr'] = array(
     '#type' => 'checkbox',
     '#title' => t('Click-through rate'),
     '#default_value' => $this->options['ctr'],
     '#description' => t('Display the click-through rate instead of just a sum of the number of url clicks.'),
     '#weight' => -10,
    );
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    dpm($this->options['ctr']);

    $nid = $values->nid;
    $unique_opens = simplenews_statistics_count_opens($nid, TRUE);
    $clicks = simplenews_statistics_get_clicks($nid);
    $total_clicks = 0;

    foreach ($clicks as $click) {
      $total_clicks += $click->clicks;
    }
    dpm($unique_opens);
    if ($this->options['ctr'] && $unique_opens > 0) {
      $unique_clicks = simplenews_statistics_count_unique_clicks($nid);
      return round($unique_clicks / $unique_opens * 100, 0) . '%';
    }
    elseif ($this->options['ctr'] && $unique_opens == 0) {
      return t('N/A');
    }

    return $total_clicks;
  }

}
