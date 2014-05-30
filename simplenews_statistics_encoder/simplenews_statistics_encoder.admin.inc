<?php
/**
 * @file
 * Simplenews statistics encoder file for admin settings.
 */

/**
 * Simplenews encoder settings.
 */
function simplenews_statistics_encoder_admin_settings_form($form, &$form_state) {
  // @todo: Implement some settings.
  $form = array();
  return system_settings_form($form);
}
