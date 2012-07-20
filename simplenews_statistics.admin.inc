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
    '#type' => 'checkbox',
    '#title' => t('Enable Simplenews Statistics'),
    '#description' => t('Disabling will only disable the logging, not the links replacement.'),
    '#default_value' => variable_get('simplenews_statistics', 1),
  );

  $form['simplenews_statistics_track_emailaddress'] = array(
    '#type' => 'checkbox',
    '#title' => t('Track mailto links'),
    '#description' => t('In some cases tracking clicks on emailaddresses wil result in a blank browser window.
        Disabling this options prevents that.'),
    '#default_value' => variable_get('simplenews_statistics_track_emailaddress', 1),
  );

  // Settings for Google Analytics.
  $ga_account = variable_get('googleanalytics_account', 'UA-');
  $form['simplenews_statistics_ga_settings'] = array(
    '#type' => 'fieldset',
    '#title' => 'Google Analytics settings',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  if (module_exists('googleanalytics') && !empty($ga_account) && $ga_account != 'UA-') {
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Google Analytics tracking'),
      '#default_value' => variable_get('simplenews_statistics_ga', 0),
    );
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga_advanced'] = array(
      '#type' => 'fieldset',
      '#title' => 'Advanced settings',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga_advanced']['simplenews_statistics_ga_use_hash'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use hash (#) symbol for the Google Analytics part of the URL.'),
      '#default_value' => variable_get('simplenews_statistics_ga_use_hash', 0),
    );
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga_advanced']['simplenews_statistics_ga_utm_source'] = array(
      '#type' => 'textfield',
      '#title' => t('Campaign source'),
      '#required' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#default_value' => variable_get('simplenews_statistics_ga_utm_source', 'newsletter'),
    );
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga_advanced']['simplenews_statistics_ga_utm_medium'] = array(
      '#type' => 'textfield',
      '#title' => t('Campaign medium'),
      '#required' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#default_value' => variable_get('simplenews_statistics_ga_utm_medium', 'email'),
    );
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga_advanced']['simplenews_statistics_ga_utm_campaign'] = array(
      '#type' => 'textfield',
      '#title' => t('Campaign name'),
      '#required' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#default_value' => variable_get('simplenews_statistics_ga_utm_campaign', '!newsletter_title'),
      '#description' => t('Newsletter name, product, promo code, or slogan. Use <em>!newsletter_title</em> to use the title of the newsletter.'),
    );
  }
  else {
    $form['simplenews_statistics_ga_settings']['simplenews_statistics_ga'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Google Analytics tracking'),
      '#description' => t('You need to <a href="/admin/build/modules">install</a> and <a href="/admin/settings/googleanalytics">configure</a> the <a href="http://drupal.org/project/google_analytics/">Google Analytics</a> module to use this function'),
      '#default_value' => 0,
      '#disabled' => TRUE,
    );
  }

  return system_settings_form($form);
}
