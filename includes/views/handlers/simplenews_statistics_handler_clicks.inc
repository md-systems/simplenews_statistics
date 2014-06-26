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

    $options['click_format'] = array('default' => 0);
    $options['ctr_precision'] = array('default' => '0');

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['click_format'] = array(
      '#type' => 'select',
      '#title' => t('Display format'),
      '#options' => array(
        'total' => t('Total count'),
        'unique' => t('Unique count'),
        'ctr' => t('Click-through rate'),
      ),
      '#default_value' => $this->options['click_format'],
      '#description' => t('Select the display format for the click counter.'),
    );

    $form['ctr_precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Precision'),
      '#default_value' => $this->options['ctr_precision'],
      '#description' => t('Number of decimal places to which the click-through rate should be calculated.'),
      '#states' => array(
        'visible' => array(
          ':input[name="options[click_format]"]' => array('value' => 'ctr'),
        ),
      ),
    );
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    $format = $this->options['click_format'];
    $precision = intval($this->options['ctr_precision']);
    $unique_opens = simplenews_statistics_count_opens($values->nid, TRUE);

    if ($format == 'total') {
      $click_count =  simplenews_statistics_count_clicks($values->nid);
    }
    else {
      $click_count = simplenews_statistics_count_clicks($values->nid, TRUE);
    }

    if ($format == 'ctr' && $unique_opens > 0) {
      return round($click_count / $unique_opens * 100, $precision) . '%';
    }
    elseif ($format == 'ctr' && $unique_opens == 0) {
      return t('N/A');
    }

    return $click_count;
  }

}
