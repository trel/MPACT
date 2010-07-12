<?php

require 'envsetup.php';

// Begin Session
session_start();

// Delete server session if client cookie doesn't exist
if (!isset($_COOKIE['MPACT_userid'])){
  unset($_SESSION['MPACT']);
}

// Check for good cookie (and expired session) - (re)set session values accordingly
if (isset($_COOKIE['MPACT_userid']) && !isset($_SESSION['MPACT'])){
  $query = "SELECT id, username, fullname FROM users WHERE id='".$_COOKIE['MPACT_userid']."'";
  $result = mysql_query($query) or die(mysql_error());
  $line = mysql_fetch_array($result);
  $_SESSION['MPACT']['userid'] = $line['id'];
  $_SESSION['MPACT']['username'] = $line['username'];
  $_SESSION['MPACT']['fullname'] = $line['fullname'];
}

// Display header
xhtml_web_header();

?>

<h1>The MPACT Project</h1>

<p><strong>The MPACT Project is an ongoing project devoted to defining and assessing
Mentoring as a scholarly activity. Based at the School of Information and Library
Science's Interaction Design Laboratory at the University of North Carolina at
Chapel Hill, the project's current focus is collecting data on dissertations and
dissertation committee service.  We invite participation of others to augment the
MPACT database and develop new MPACT metrics and theory.</strong></p>


<div id="board">
  <h1>Board of Advisors</h1>
    <ul>
      <li><a href="http://ella.slis.indiana.edu/~sugimoto/">Cassidy Sugimoto</a>, Director</li>
      <li><a href="http://www.terrellrussell.com">Terrell Russell</a></li>
      <li><a href="http://ils.unc.edu/~march/">Gary Marchionini</a></li>
    </ul>
</div>

<?php

xhtml_web_footer();

?>
