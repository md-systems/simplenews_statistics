<?php
/**
 * @file
 * Simplenews statistics file containing all admin settings and functions.
 */

/**
 * Simplenews Google Analytics settings.
 */
function simplenews_statistics_admin_settings_form($form, &$form_state) {
  // General settings for Simplenews Statistics.
  $form['simplenews_statistics'] = array(
    '#type' => 'fieldset',
    '#title' => t('General Settings'),
  );

  $form['simplenews_statistics']['logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable logging'),
    '#description' => t('Disabling will only disable the logging, not the links replacement.'),
    '#default_value' => variable_get('simplenews_statistics_logging', 1),
  );

  $form['simplenews_statistics']['track_email_address'] = array(
    '#type' => 'checkbox',
    '#title' => t('Track mailto links'),
    '#description' => t('In some cases tracking clicks on email addresses wil result in a blank browser window. Disabling this options prevents that.'),
    '#default_value' => variable_get('simplenews_statistics_track_email_address', 1),
  );

  return system_settings_form($form);
}
