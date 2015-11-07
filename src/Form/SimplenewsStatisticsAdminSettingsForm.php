<?php

/**
 * @file
 * Contains \Drupal\simplenews_statistics\Form\SimplenewsStatisticsAdminSettingsForm.
 */

namespace Drupal\simplenews_statistics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\simplenews\Entity\Newsletter;

class SimplenewsStatisticsAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplenews_statistics_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('simplenews_statistics.settings')
      ->set('track_test', $form_state->getValue('track_test'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['simplenews_statistics.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // General settings for Simplenews Statistics.
    $form['simplenews_statistics'] = [
      '#type' => 'fieldset',
      '#title' => t('General Settings'),
    ];

    $form['simplenews_statistics']['track_test'] = [
      '#type' => 'checkbox',
      '#title' => t('Track newsletter test sends'),
      '#description' => t('Disabling this will stop the tracking of opens and clicks for test emails. Links replacements will still be done for those test sends, but no statistics will be recorded.'),
      '#default_value' => \Drupal::config('simplenews_statistics.settings')->get('track_test'),
    ];

    $form['simplenews_statistics']['track_mailto'] = [
      '#type' => 'checkbox',
      '#title' => t('Track mailto links'),
      '#description' => t('In some cases tracking clicks on email addresses wil result in a blank browser window. Disabling this options prevents that.'),
      '#default_value' => \Drupal::config('simplenews_statistics.settings')->get('track_mailto'),
    ];

    $form['simplenews_statistics']['archive_days'] = [
      '#type' => 'textfield',
      '#title' => t('Days to keep open and click records'),
      '#description' => t('Specify a number of days beyond which the open and click records for newsletters will be deleted. This can help control the growth of the open and click database tables over time. The site cron must be correctly configured. A value of 0 disables this setting.'),
      '#default_value' => \Drupal::config('simplenews_statistics.settings')->get('archive_days'),
      '#size' => 4,
      '#maxlength' => 4,
    ];

    $form['simplenews_statistics']['exclude'] = [
      '#type' => 'textarea',
      '#title' => t('Exclude links from tracking'),
      '#description' => t('Enter a list paths or URLs that should be excluded by the links replacement process. Wildcards are allowed. Each URL or path should be on a newline. You may need to include initial and/or trailing slashes for paths, but this will depend on how the href attribute is structured.'),
      '#default_value' => \Drupal::config('simplenews_statistics.settings')->get('exclude'),
    ];

    // Check for HTML formats.
    $newsletters = Newsletter::loadMultiple();
    foreach ($newsletters as $newsletter) {
      if ($newsletter->format !== 'html') {
        drupal_set_message(t('Newsletter %name format has not been set to HTML. There will be no statistics recorded for this newsletter.', [
          '%name' => $newsletter->name
          ]), 'warning');
      }
    }

    return parent::buildForm($form, $form_state);
  }

}
