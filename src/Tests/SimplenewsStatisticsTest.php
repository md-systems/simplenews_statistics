<?php

/**
 * @file
 * Contains \Drupal\simplenews_statistics\Tests\SimplenewsStatisticsTest.
 */

namespace Drupal\simplenews_statistics\Tests;

use Drupal\simplenews\Tests\SimplenewsTestBase;

/**
 * Tests newsletter statistic procedures for the simplenews module.
 *
 * @group simplenews_statistics
 */
class SimplenewsStatisticsTest extends SimplenewsTestBase {

  private $newsletter_nid = NULL;

  public static $modules = ['simplenews_statistics'];

  /**
   * A user with all simplenews, simplenews statistics and core permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->user = $this->drupalCreateUser(array(
      'administer newsletter statistics',
      'view newsletter statistics',
      'administer newsletters',
      'send newsletter',
      'administer nodes',
      'administer simplenews subscriptions',
      'create simplenews_issue content',
      'edit any simplenews_issue content',
      'view own unpublished content',
      'delete any simplenews_issue content',
    ));
    $this->drupalLogin($this->user);

    // Subscribe a few users. Use a higher amount because we want to test statistics
    $this->setUpSubscribers(37);
  }

  /**
   * Function that will create a newsletter in the default category
   *
   * @see SimplenewsSourceTestCase::testSendHTML
   */
  private function createNewsletter() {
    // Use custom testing mail system to support HTML mails.
    $mail_config = $this->config('system.mail');
    $mail_config->set('interface.default', 'test_simplenews_html_mail');
    $mail_config->save();

    // Set the format to HTML.
    $this->drupalGet('admin/config/services/simplenews');
    $this->clickLink(t('Edit'));
    $edit_category = array(
      'format' => 'html',
      // Use umlaut to provoke mime encoding.
      'from_name' => 'Drupäl Simplenews Statistic Testing',
      // @todo: load the website default email
      'from_address' => $this->randomEmail(),
      // Request a confirmation receipt.
      'receipt' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit_category, t('Save'));

    $body_text = <<<TEXT
Mail token: <strong>[simplenews-subscriber:mail]</strong><br />
add a link in the mail to drupal.org so we can test later <br />
<a title="Simplenews Statistics Link" href="http://drupal.org/project/simplenews_statistics ">Simplenews Statistics Module Page</a>
TEXT;

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => $body_text,
    );
    $this->drupalPostForm('node/add/simplenews_issue', $edit, ('Save and publish'));
    $this->assertTrue(preg_match('|node/(\d+)$|', $this->getUrl(), $matches), 'Node created');
    $node = \Drupal::entityManager()->getStorage('node')->load($matches[1]);
    $this->newsletter_nid = $node->id();
  }

  /**
   * Function that will create a newsletter in the default category and publish
   * it
   */
  private function createAndPublishNewsletter() {
    $this->createNewsletter();
  }

  /**
   * Function that will create a newsletter in the default category and send it
   */
  private function createAndSendNewsletter() {
    $this->createAndPublishNewsletter();

    $node = \Drupal::entityManager()
      ->getStorage('node')
      ->load($this->newsletter_nid);

    // Send the node.
    \Drupal::service('simplenews.spool_storage')->addFromEntity($node);

    // Send mails.
    \Drupal::service('simplenews.mailer')->sendSpool();
  }

  /**
   * Test Statistic Logic: Open Rate
   *
   * test that calling the URL /simplenews/statistics/view for the node
   * properly updates the statistics
   */
  function testCallOpenStatisticURLDirectlyAndCheckDatabaseOpenRateUpdate() {
    $this->createAndSendNewsletter();

    //get the last email sent
    $mails = $this->drupalGetMails();
    $mail = end($mails);
    $source = $mail['params']['simplenews_mail'];
    $source_node = $source->getEntity();

    // Obtain the full URL link.
    $pattern = '@http://.+/track/open/\d+/\d+@';
    $found = preg_match($pattern, $mail['body'], $match);
    if (!$found) {
      $this->fail('Track URL found.');
      debug($mail['body']);
      return;
    }
    $link = $match[0];
    $this->pass(t('Track URL found: @url', array('@url' => $link)));

    //Before "viewing", verify the tables are properly initialized
    {
      //the simplenews_statistics_open table should have no entry.
      $subscriber = simplenews_subscriber_load_by_mail($mail['to']);
      $query = db_select('simplenews_statistics_open', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(0, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');
        }
      }

      simplenews_statistics_cron();

      //Query that 0 views is recorded in simplenews_statistics; expected total opens = 0 and unique opens = 0
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $resultset = $query->execute();
      if ($result = $resultset->fetchObject()) {
        debug($result);
        $this->assertEqual(0, $result->total_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: total_opens.');
        $this->assertEqual(0, $result->unique_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: unique_opens.');
      }
      else {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics.');
      }
    }

    //load the image
    $this->drupalGet($link);

    {
      //the simplenews_statistics_open table should have 1 entry.
      $query = db_select('simplenews_statistics_open', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $result = $query->execute()->fetchField();
      $this->assertEqual(1, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');


      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 1 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(1, $result->total_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: total_opens.');
          $this->assertEqual(1, $result->unique_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: unique opens.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');
      }
    }

    // load the image a second time.
    $this->drupalGet($link);

    {
      //the simplenews_statistics_open table should have 2 entries.
      $query = db_select('simplenews_statistics_open', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(2, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 2 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(2, $result->total_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: total_opens.');
          $this->assertEqual(1, $result->unique_opens, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics in field: unique_opens.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_open.');
      }
    }
  }

  /**
   * Test Statistic Logic: Click Rate
   *
   * test that calling the URL /simplenews/statistics/click for the node
   * properly updates the statistics. Modelled after the Opens test (previous
   * test).
   *
   * @see testCallOpenStatisticURLDirectlyAndCheckDatabaseOpenRateUpdate
   */
  function testCallClickStatisticURLDirectlyAndCheckDatabaseClickRateUpdate() {
    $this->createAndSendNewsletter();

    //get the last email sent
    $mails = $this->drupalGetMails();
    $mail = end($mails);
    $source = $mail['params']['simplenews_mail'];
    $source_node = $source->getEntity();

    // Obtain the full URL link.
    $pattern = '@http://.+/track/click/\d+/\d+@';
    $found = preg_match($pattern, $mail['body'], $match);
    if (!$found) {
      $this->fail('Track URL found.');
      debug($mail['body']);
      return;
    }
    $link = $match[0];
    $this->pass(t('Track URL found: @url', array('@url' => $link)));

    //Before "clicking", verify the tables are properly initialized
    {
      //the simplenews_statistics_open table should have no entry.
      $subscriber = simplenews_subscriber_load_by_mail($mail['to']);
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->join('simplenews_statistics_url', 'ssu', 'ssu.urlid = ssc.urlid');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(0, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
        }
      }

      //Query that 0 views is recorded in simplenews_statistics; expected total opens = 0 and unique opens = 0
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'unique_clicks'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(0, $result->total_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: total_clicks.');
          $this->assertEqual(0, $result->unique_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: unique_clicks.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
      }
    }

    //load the image
    $this->drupalGet($link);

    {
      //the simplenews_statistics_click table should have 1 entry.
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->join('simplenews_statistics_url', 'ssu', 'ssu.urlid = ssc.urlid');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(1, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 1 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'unique_clicks'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(1, $result->total_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: total_clicks.');
          $this->assertEqual(1, $result->unique_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: unique_clicks.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
      }
    }

//load the image a second time
    $this->drupalGet($link);

    {
      //the simplenews_statistics_click table should have 2 entries.
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->join('simplenews_statistics_url', 'ssu', 'ssu.urlid = ssc.urlid');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(2, $result, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click.');
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 2 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'unique_clicks'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(2, $result->total_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: total_clicks.');
          $this->assertEqual(1, $result->unique_clicks, 'Simplenews newsletter open statistic was recorded properly in simplenews_statistics_click in field: unique_clicks.');
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail('Simplenews newsletter open statistic was recorded properly in simplenews_statistics.');
      }
    }
  }

  /**
   * Test Workflow: Redirected to correct page
   *
   * send a newsletter, click a link, test that the user is forwarded to the
   * correct page
   */
  function testClickStatisticLinkRedirect() {
    $this->createAndSendNewsletter();

    //get the last email sent
    $mails = $this->drupalGetMails();
    $mail = end($mails);
    $source = $mail['params']['simplenews_mail'];
    $source_node = $source->getEntity();

    // Obtain the full URL link.
    $pattern = '@http://.+/track/click/\d+/\d+@';
    $found = preg_match($pattern, $mail['body'], $match);
    if (!$found) {
      $this->fail('Track URL found.');
      debug($mail['body']);
      return;
    }
    $link = $match[0];
    $this->pass(t('Track URL found: @url', array('@url' => $link)));

    $intended_url = "https://www.drupal.org/project/simplenews_statistics"; //defined in the test mail we send above

    //click the link - see if we are re-directed to the target page
    $this->drupalGet($link);

    //test
    $this->assertEqual($intended_url, $this->getUrl());

  }

  /**
   * Test Workflow: Open Rate for unpublished Newsletter is not updated
   *
   * send a newsletter, click a link, test that the user is forwarded to the
   * correct page
   */
  /* function testUnpublishedNewsletterOpenStatistic(){
     $this->assertEqual(TRUE, FALSE, 'Test not written.');
     return false;
     } */

  /**
   * Test Workflow: Click Rate for unpublished Newsletter is not updated
   *
   * send a newsletter, click a link, test that the user is forwarded to the
   * correct page
   */
  /* function testUnpublishedNewsletterClickStatistic(){
     $this->assertEqual(TRUE, FALSE, 'Test not written.');
     return false;
     }*/

  /**
   * Test Workflow: Open Rate for non-Newsletter entity is not counted
   *
   * test that if the URL is called with an improper node id, that it is not
   * updated
   */
  /* function testNonNewsletterOpenStatistic(){
     $this->assertEqual(TRUE, FALSE, 'Test not written.');
     return false;
     }*/

  /**
   * Test Workflow: Open Rate for non-Newsletter entity is not counted
   *
   * test that if the URL is called with an improper node id, that it is not
   * updated
   */
  /* function testNonNewsletterClickStatistic(){
     $this->assertEqual(TRUE, FALSE, 'Test not written.');
     return false;
     }*/

}
