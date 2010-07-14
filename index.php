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

<p><strong>The MPACT Project is an academic genealogy project devoted to defining and
assessing mentoring as a scholarly activity, examining the emergence and
interaction of disciplines, and identifying patterns of knowledge diffusion.
MPACT is a joint project between Indiana University Bloomington and
the University of North Carolina at Chapel Hill.</strong></p>

<p><strong>The current focus of the project is on collecting dissertation data
from a wide variety of disciplines and institutions.  We invite the participation
of others to augment and utilize the MPACT database to develop new metrics and theory,
rooted in academic genealogy.</strong></p>


<div id="board">
  <h1>Board of Advisors</h1>
    <ul>
      <li><a href="http://ella.slis.indiana.edu/~sugimoto/">Cassidy Sugimoto</a>, Director</li>
      <li><a href="http://www.terrellrussell.com">Terrell Russell</a>, Database and Open Source Administrator</li>
      <li><a href="http://ils.unc.edu/~march/">Gary Marchionini</a></li>
    </ul>
</div>

<?php

xhtml_web_footer();

?>
