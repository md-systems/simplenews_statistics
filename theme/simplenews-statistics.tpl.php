<?php

/**
 * @file
 * Template for simplenews statistics overview page.
 *
 * Available variables:
 *
 * $variables['statistics'] (array)
 *   ['sent_count'] (int)
 *     Copies of this newsletter sent to subscribers.
 *   ['subscriber_count'] (int)
 *     Number of subscibers.
 *   ['total_opens'] (int)
 *     Total number of times this newsletter has been opened.
 *   ['unique_opens'] (int)
 *     Unique number of times this newsletter has been opened.
 *   ['clicks'] (array of objects)
 *     ->urlid (int)
 *       The ID assigned to this record.
 *     ->url (string)
 *       The URL of this link.
 *     ->clicks (int)
 *       Number of times this link has been clicked.
 *   ['total_clicks'] (int)
 *     Total number of times all links have been clicked.
 *   ['open_rate'] (float)
 *     The percentage of subscribers who have opened the newsletter.
 *   ['ctr'] (float)
 *     The percentage of subscribers who have opened the newsletter
 *     and clicked a link.
 *
 */

$stats = $variables['statistics'];
?>
<h2>Newsletter Overview</h2>
<table>
  <tr>
    <th>Subscribers</th>
    <th>Emails Sent</th>
    <th>Emails Opened</th>
    <th>Links Clicked</th>
  </tr>
  <tr>
    <td><?php print $stats['subscriber_count']; ?></td>
    <td><?php print $stats['sent_count'] ?></td>
    <td>
      Total: <?php print $stats['total_opens']; ?><br/>
      Unique: <?php print $stats['unique_opens']; ?>
    </td>
    <td><?php print $stats['total_clicks']; ?></td>
  </tr>
</table>
<p>Note: Open and click counts can include test emails if they were sent to subscribers.</p>
<p>Open rate: <?php print $stats['open_rate']; ?>%</p>
<p>CTR: <?php print $stats['ctr']; ?>%</p>
