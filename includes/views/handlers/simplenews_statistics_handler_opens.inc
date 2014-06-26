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

    $options['unique'] = array('default' => 0);
    $options['open_rate'] = array('default' => 0);

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['unique'] = array(
     '#type' => 'checkbox',
     '#title' => t('Unique opens'),
     '#default_value' => $this->options['unique'],
     '#description' => t('Only count unique opens. If a subscriber has open the newsletter multiple times only one will be counted.'),
    );
    $form['open_rate'] = array(
     '#type' => 'checkbox',
     '#title' => t('Open rate'),
     '#default_value' => $this->options['open_rate'],
     '#description' => t('Calculate the open rate based on the number of emails sent and the unique open count.'),
    );
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    //dpm($values);
    //dpm($options);
    $unique = $this->options['unique'];
    $open_rate = $this->options['open_rate'];
    $nid = $values->nid;
    $sent_count = $values->simplenews_newsletter_sent_subscriber_count;

    if ($unique) {
      $open_count = simplenews_statistics_get_open_count($nid, TRUE);
    }
    else {
      $open_count = simplenews_statistics_get_open_count($nid);
    }

    // @todo: Require unique for this. Make number of decimals configurable.
    if ($open_rate && $sent_count > 0) {
      return round($open_count / $sent_count * 100, 0) . '%';
    }

    return $open_count;
  }

}
