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
    $this->additional_fields['sent_subscriber_count'] = array(
      'table' => 'simplenews_newsletter',
      'field' => 'sent_subscriber_count',
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

    $options['open_format'] = array('default' => 0);
    $options['open_rate_precision'] = array('default' => '0');

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['open_format'] = array(
      '#type' => 'select',
      '#title' => t('Display format'),
      '#options' => array(
        'total' => t('Total count'),
        'unique' => t('Unique count'),
        'open_rate' => t('Open rate'),
      ),
      '#default_value' => $this->options['open_format'],
      '#description' => t('Select the display format for the open counter.'),
    );

    $form['open_rate_precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Precision'),
      '#default_value' => $this->options['open_rate_precision'],
      '#description' => t('Number of decimal places to which the open rate should be calculated.'),
      '#states' => array(
        'visible' => array(
          ':input[name="options[open_format]"]' => array('value' => 'open_rate'),
        ),
      ),
    );
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    //dpm($values);
    //dpm($this->options);
    $format = $this->options['open_format'];
    $precision = intval($this->options['open_rate_precision']);
    $sent_count = $values->simplenews_newsletter_sent_subscriber_count;

    if ($format == 'total') {
      $open_count = simplenews_statistics_get_open_count($values->nid);
    }
    else {
      $open_count = simplenews_statistics_get_open_count($values->nid, TRUE);
    }

    if ($format == 'open_rate' && $sent_count > 0) {
      return round($open_count / $sent_count * 100, $precision) . '%';
    }

    return $open_count;
  }

}
