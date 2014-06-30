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

  $form['simplenews_statistics']['simplenews_statistics_track_test'] = array(
    '#type' => 'checkbox',
    '#title' => t('Track newsletter test sends'),
    '#description' => t('Disabling this will stop the tracking of opens and clicks for test emails. Links replacements will still be done for those test sends, but no statistics will be recorded.'),
    '#default_value' => variable_get('simplenews_statistics_track_test', 0),
  );

  $form['simplenews_statistics']['simplenews_statistics_track_mailto'] = array(
    '#type' => 'checkbox',
    '#title' => t('Track mailto links'),
    '#description' => t('In some cases tracking clicks on email addresses wil result in a blank browser window. Disabling this options prevents that.'),
    '#default_value' => variable_get('simplenews_statistics_track_mailto', 1),
  );

  return system_settings_form($form);
}
