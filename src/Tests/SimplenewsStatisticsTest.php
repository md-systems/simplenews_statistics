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
    // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// variable_set('mail_system', array('default-system' => 'SimplenewsHTMLTestingMailSystem'));


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

    //obtain the full URL link by stripping off the characters
    $link = $mail['body'];
    $link = substr($link, strpos($link, \Drupal\Component\Utility\SafeMarkup::checkPlain(url('simplenews/statistics/view', array('absolute' => TRUE,)))));

    $link = substr($link, 0, strpos($link, '"'));

    $this->verbose('Link thus far is: ' . $link);

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
          $this->assertEqual(0, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result
            )));
        }
      }

      //Query that 0 views is recorded in simplenews_statistics; expected total opens = 0 and unique opens = 0
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(0, $result->total_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'total_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result->total_opens
            )));
          $this->assertEqual(0, $result->unique_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'unique_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result->unique_opens
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
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
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(1, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics_open'
        )));
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 1 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(1, $result->total_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'total_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->total_opens
            )));
          $this->assertEqual(1, $result->unique_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'unique_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->unique_opens
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
      }
    }

//load the image a second time
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
          $this->assertEqual(2, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 2,
              '@received' => $result
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics_open'
        )));
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 2 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_opens', 'unique_opens'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(2, $result->total_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'total_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 2,
              '@received' => $result->total_opens
            )));
          $this->assertEqual(1, $result->unique_opens, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_open',
              '@field' => 'unique_opens'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->unique_opens
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
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

    //obtain the full URL link by stripping off the characters
    $link = $mail['body'];
    $link = substr($link, strpos($link, \Drupal\Component\Utility\SafeMarkup::checkPlain(url('simplenews/statistics/click', array('absolute' => TRUE,)))));
    //we only obtain the first of probobly several urls
    $link = substr($link, 0, strpos($link, '"'));

    $this->verbose('Link thus far is: ' . $link);

    //Before "clicking", verify the tables are properly initialized
    {
      //the simplenews_statistics_open table should have no entry.
      $subscriber = simplenews_subscriber_load_by_mail($mail['to']);
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(0, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result
            )));
        }
      }

      //Query that 0 views is recorded in simplenews_statistics; expected total opens = 0 and unique opens = 0
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'user_unique_click_through'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(0, $result->total_clicks, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'total_clicks'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result->total_clicks
            )));
          $this->assertEqual(0, $result->user_unique_click_through, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'user_unique_click_through'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 0,
              '@received' => $result->user_unique_click_through
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
      }
    }

    //load the image
    $this->drupalGet($link);

    {
      //the simplenews_statistics_click table should have 1 entry.
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(1, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics_click'
        )));
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 1 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'user_unique_click_through'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(1, $result->total_clicks, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'total_clicks'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->total_clicks
            )));
          $this->assertEqual(1, $result->user_unique_click_through, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'user_unique_click_through'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->user_unique_click_through
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
      }
    }

//load the image a second time
    $this->drupalGet($link);

    {
      //the simplenews_statistics_click table should have 2 entries.
      $query = db_select('simplenews_statistics_click', 'ssc');
      $query->addExpression('COUNT(*)', 'ct');
      $query->condition('nid', $source_node->id());
      $query->condition('snid', $subscriber->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchField()) {
          $this->assertEqual(2, $result, t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 2,
              '@received' => $result
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics_click'
        )));
      }

      //Query that 1 views is recorded in simplenews_statistics; expected total opens = 2 and unique opens = 1
      $query = db_select('simplenews_statistics', 'ssc');
      $query->fields('ssc', array('total_clicks', 'user_unique_click_through'));
      $query->condition('nid', $source_node->id());
      $found = FALSE;
      if ($resultset = $query->execute()) {
        if ($result = $resultset->fetchObject()) {
          $this->assertEqual(2, $result->total_clicks, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'total_clicks'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 2,
              '@received' => $result->total_clicks
            )));
          $this->assertEqual(1, $result->user_unique_click_through, t('Simplenews newsletter @statistic statistic was recorded properly in @table in field: @field.', array(
              '@statistic' => 'open',
              '@table' => 'simplenews_statistics_click',
              '@field' => 'user_unique_click_through'
            )) . ' ' . t('Expected @expected, received @received', array(
              '@expected' => 1,
              '@received' => $result->user_unique_click_through
            )));
          $found = TRUE;
        }
      }

      if (!$found) {
        $this->fail(t('Simplenews newsletter @statistic statistic was recorded properly in @table.', array(
          '@statistic' => 'open',
          '@table' => 'simplenews_statistics'
        )));
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

    //obtain the full URL link by stripping off the characters
    $link = $mail['body'];
    $link = substr($link, strpos($link, 'title="Simplenews Statistics Link" href="')); //make certain we get the link that we need to test
    $link = substr($link, strpos($link, \Drupal\Component\Utility\SafeMarkup::checkPlain(url('simplenews/statistics/click', array('absolute' => TRUE,)))));
    //we only obtain the first of probobly several urls
    $link = substr($link, 0, strpos($link, '"'));

    $this->verbose('Link thus far is: ' . $link);

    $intended_url = "http://drupal.org/project/simplenews_statistics"; //defined in the test mail we send above

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
