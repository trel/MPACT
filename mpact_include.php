<?php

###########    INCLUDE FUNCTIONS     ####################

# -------------------------------------------------------------------------------
function is_admin(){
  if (isset($_SESSION['MPACT']['username']))
  {
    return 1;
  }
  else
  {
    return 0;
  }
}

# -------------------------------------------------------------------------------
function not_admin(){
   action_box("Action Not Permitted.");
   mpact_logger("not admin");
}

# -------------------------------------------------------------------------------
# from http://www.richardlord.net/blog/php-password-security
// get a new salt - 8 hexadecimal characters long
// current PHP installations should not exceed 8 characters
// on dechex( mt_rand() )
// but we future proof it anyway with substr()
function generate_PasswordSalt(){
  return substr( str_pad( dechex( mt_rand() ), 8, '0', STR_PAD_LEFT ), -8 );
}

# -------------------------------------------------------------------------------
# from http://www.richardlord.net/blog/php-password-security
// calculate the hash from a salt and a password
function generate_PasswordHash( $salt, $password ){
  return $salt . ( sha1( $salt . $password ) );
}

# -------------------------------------------------------------------------------
# from http://www.richardlord.net/blog/php-password-security
// compare a password to a hash
function comparePassword( $password, $hashed ){
  $salt = substr( $hashed, 0, 8 );
  return $hashed == generate_PasswordHash( $salt, $password );
}

# -------------------------------------------------------------------------------
function mpact_logger($message,$type="general"){
  global $dbh;
  $ip = ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
  if (!get_magic_quotes_gpc()) {$message = addslashes($message);}
  $query = "INSERT logs
              SET
              logged_at = now(),
              ip        = '".$ip."',
              user      = '".$_SESSION['MPACT']['username']."',
              type      = '".$type."',
              message   = '".$message."',
              action    = '".$_SERVER['REQUEST_URI']."',
              agent     = '".$_SERVER['HTTP_USER_AGENT']."'
            ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
}

# -------------------------------------------------------------------------------
# http://us2.php.net/manual/en/function.array-diff.php#29808
function array_key_diff($ar1, $ar2) {  // , $ar3, $ar4, ...
   // returns copy of array $ar1 with those entries removed
   // whose keys appear as keys in any of the other function args
   $aSubtrahends = array_slice(func_get_args(),1);
   foreach ($ar1 as $key => $val)
       foreach ($aSubtrahends as $aSubtrahend)
           if (array_key_exists($key, $aSubtrahend))
               unset ($ar1[$key]);
   return $ar1;
}

# -------------------------------------------------------------------------------
function action_box($message,$bounce = "0",$location = "index.php"){

  echo "
  <p>
  <span class=\"actionbox\">$message</span>
  </p>
  <br /><br />
  ";

  if ($bounce != "0"){
    echo "
    <meta http-equiv=\"Refresh\" content=\"$bounce;url=$location\">
    <p>
    <a href=\"$location\">Forwarding in $bounce seconds (or click here to do the same)</a>
    </p>
    ";
  }

}

# -------------------------------------------------------------------------------
function xhtml_orig_web_header(){

  # make search box happy
  if (!isset($_POST['q'])){$_POST['q'] = "";}
  if (!isset($_POST['qta'])){$_POST['qta'] = "";}
  if (!isset($_POST['qn'])){$_POST['qn'] = "";}

  # print out header info
  echo "

  <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
  <html>
  <head>
  <title>MPACT</title>

  <link rel=\"stylesheet\" type=\"text/css\" href=\"mpact.css\">
  <script src=\"sorttable.js\" type=\"text/javascript\"></script>
  <script src=\"mpact.js\" type=\"text/javascript\"></script>

  </head>
  <body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" >

  <table width=\"90%\" height=\"95%\" border=\"0\" align=\"center\"
  cellpadding=\"0\" cellspacing=\"0\">

  <tr>
    <td height=\"40\">
      <table border=\"0\" width=\"100%\">
      <tr>
        <td valign=\"top\" height=\"20\" width=\"80%\">
          <form id=\"main_mpact\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
            <a href=\".\" title=\"MPACT\">MPACT</a> -
            <a href=\"mpact.php\" title=\"DB\">Database</a> -
            <input type=\"hidden\" name=\"op\" value=\"search\">
            People Search: <input type=\"text\" name=\"q\" size=\"10\" value=\"".$_POST['q']."\">
          </form> -
          <form id=\"main_mpact_title_abstract\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
          <input type=\"hidden\" name=\"op\" value=\"search_title_abstract\">
          Title/Abstract Search: <input type=\"text\" name=\"qta\" size=\"10\" value=\"".$_POST['qta']."\">
          </form>
";
  if (is_admin()){
    echo " - <form id=\"main_mpact_notes\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
    <input type=\"hidden\" name=\"op\" value=\"search_notes\">
    Admin Notes Search: <input type=\"text\" name=\"qn\" size=\"10\" value=\"".$_POST['qn']."\">
    </form>";
  }
  echo "</td>
        <td align=\"right\" width=\"20%\">
";
  if (is_admin()){
    echo $_SESSION['MPACT']['fullname']." (".$_SESSION['MPACT']['username'].") - <a href=\"mpact.php?op=logout\">Logout</a>";
  }
  else{
    $host_info = get_environment_info();
    if ($host_info['hostname'] != "sils"){
      echo "<a href=\"mpact.php?op=login\">Login</a>";
    }
  }
echo "
        </td>
      </tr>
      </table>
    </td>
  </tr>

  <tr><td valign=\"top\"><br />

  ";


}

# -------------------------------------------------------------------------------
function xhtml_web_header(){

  # make search box happy
  if (!isset($_POST['q'])){$_POST['q'] = "";}
  if (!isset($_POST['qta'])){$_POST['qta'] = "";}
  if (!isset($_POST['qn'])){$_POST['qn'] = "";}

  # print out header info
  echo "

  <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
  <html>
  <head>
  <title>MPACT</title>
  <link rel=\"stylesheet\" type=\"text/css\" href=\"mpact.css\">
  <script src=\"sorttable.js\" type=\"text/javascript\"></script>
  <script src=\"mpact.js\" type=\"text/javascript\"></script>
  </head>
  <body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" >

";

echo "<div id=\"accountstatus\"><p>";
if (is_admin()){
  echo $_SESSION['MPACT']['fullname']." (".$_SESSION['MPACT']['username'].") - <a href=\"mpact.php?op=logout\">Logout</a>";
}
else{
  echo "<a href=\"mpact.php?op=login\">Login</a>";
}
echo "</p></div>";
echo "<div class=\"clear\"></div>";

echo "
  <div id=\"headertable\">
  <table border=\"0\" align=\"center\" width=\"870\">
  <tr>

    <td>
      <a href=\"http://www.slis.indiana.edu/\"><img src=\"./images/slis_logo_crimson.png\" width=\"200\"></a>
    </td>

    <td>
      <span id=\"mpactlogo\"><a href=\"./\"><img src=\"./images/mpact-tassel.png\" width=\"470\"></a></span>
      <span class=\"navtextlinks\">
      <a href=\"publications.php\">Publications</a> &nbsp;&bull;&nbsp;
      <a href=\"mpact.php?op=statistics\">Project Statistics</a>
      <br />
      <br />
      <a href=\"mpact.php?op=glossary\">Glossary</a> &nbsp;&bull;&nbsp;
      <a href=\"mpact.php?op=show_schools\">Schools</a> &nbsp;&bull;&nbsp;
      <a href=\"mpact.php?op=show_disciplines\">Disciplines</a>
      </span>
      <form id=\"main_mpact\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
        <input type=\"hidden\" name=\"op\" value=\"search\">
        <span class=\"navtext\">People Search:&nbsp;<input type=\"text\" name=\"q\" size=\"13\" value=\"".$_POST['q']."\"></span>
      </form>
      &nbsp;&nbsp;&nbsp;
      <form id=\"main_mpact_title_abstract\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
        <input type=\"hidden\" name=\"op\" value=\"search_title_abstract\">
        <span class=\"navtext\">Title/Abstract Search:&nbsp;<input type=\"text\" name=\"qta\" size=\"13\" value=\"".$_POST['qta']."\"></span>
      </form>";
if (is_admin()){
  echo "<br /><br /><span class=\"navtextlinks\"><a href=\"mpact.php?op=admin\">Administrator Pages</a></span>";
  echo "<form id=\"main_mpact_notes\" method=\"post\" action=\"mpact.php\" class=\"inlinesearchbox\">
  <input type=\"hidden\" name=\"op\" value=\"search_notes\">
  <span class=\"navtext centered\">Admin Notes Search:&nbsp;<input type=\"text\" name=\"qn\" size=\"13\" value=\"".$_POST['qn']."\"></span>
  </form>";
}
echo "    </td>

    <td>
      <a href=\"http://sils.unc.edu/\"><img src=\"./images/sils_logo.png\" width=\"200\"></a>
    </td>

  </tr>
  </table>
  </div>

  <div class=\"clear\"></div>
  <br />

  <div id=\"main\">

  ";


}

# -------------------------------------------------------------------------------
function xhtml_web_footer(){
  echo "

  </div>

  </body>
  </html>
";
}

# -------------------------------------------------------------------------------
function is_email_valid ($address)
{

	# http://www.zend.com/codex.php?id=285&single=1

    return (preg_match(
        '/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+'.   // the user name
        '@'.                                     // the ubiquitous at-sign
        '([-0-9A-Z]+\.)+' .                      // host, sub-, and domain names
        '([0-9A-Z]){2,4}$/i',                    // top-level domain (TLD)
        trim($address)));
}

# -------------------------------------------------------------------------------
function find_glossaryterms()
{
  global $dbh;
  $query = "SELECT id, term, definition FROM glossary";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  $glossaryterms = array();
  while ( $line = mysqli_fetch_array($result)) {
    $glossaryterms['ids'][$line['term']] = $line['id'];
    $glossaryterms['defs'][$line['id']] = $line['definition'];
  }
  return $glossaryterms;
}

# -------------------------------------------------------------------------------
function show_glossary()
{
  global $dbh;
  $query = "SELECT id, term, definition FROM glossary ORDER BY term ASC";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $results = array();
  while ( $line = mysqli_fetch_array($result)) {
    array_push($results,$line);
  }

  echo "<h3>Glossary</h3>\n";

  echo "<p>\n";

  foreach ($results as $one)
  {
    echo "<strong>".$one['term']."</strong>: ";
    echo $one['definition'];
    if (is_admin())
    {
      echo " <a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary_edit&id=".$one['id']."\">EDIT</a> ";
    }
    echo "<br /><br />";
  }

  echo "</p>\n";

}

# -------------------------------------------------------------------------------
function show_glossaryterm($id)
{
  global $dbh;
  $query = "SELECT id, term, definition FROM glossary WHERE id = $id";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $results = array();
  while ( $line = mysqli_fetch_array($result)) {
    $glossaryterm = $line;
  }

  echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\">Full Glossary Listing</a></p>";

  echo "<h3>Glossary</h3>\n";

  echo "<p>\n";

  echo "<strong>".$glossaryterm['term']."</strong>: ";
  echo $glossaryterm['definition'];
  if (is_admin())
  {
    echo " <a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary_edit&id=$id\">EDIT</a> ";
  }
  echo "<br /><br />";

  echo "</p>\n";

}

# -------------------------------------------------------------------------------
function show_alphabet()
{

  $alphabet_letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $alphabet = preg_split('//', $alphabet_letters, -1, PREG_SPLIT_NO_EMPTY);

  echo "<div class=\"alphabet centered\">\n";
  echo "<p>Dissertation Authors and Mentors by Last Name</p>";
  echo "<p>";
  foreach ($alphabet as $letter)
  {
    if ($letter != "A"){echo " - ";}
    echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?show=$letter\">$letter</a>";
  }
  echo "</p>";
   echo "</div>\n";
}

# -------------------------------------------------------------------------------
function search_box($q="")
{
  echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
  echo "<input type=\"hidden\" name=\"op\" value=\"search\">\n";
  echo "<p>People Search: ";
  if ($q)
  {
    echo "<input type=\"text\" name=\"q\" value=\"$q\">\n";
  }
  else
  {
    echo "<input type=\"text\" name=\"q\">\n";
  }
  echo "</p>\n";
  echo "</form>\n";
}

# -------------------------------------------------------------------------------
function people_search($q)
{
  global $dbh;
  $q = trim($q);
  if (!get_magic_quotes_gpc()) {$q = addslashes($q);}
  $results = array();
  if (strlen($q) < 2){
    return $results;
  }

  $pieces = explode(" ",$q);
#  print_r($pieces);
  if (count($pieces) == 2)
  {
#    echo " ---------  two pieces";
    $query = "SELECT n.id as name_id, p.id as person_id
                FROM
                  names n, people p
                WHERE
                  (

                    (n.firstname  LIKE '%".$pieces[0]."%' OR
                    n.middlename LIKE '%".$pieces[0]."%' OR
                    n.lastname   LIKE '%".$pieces[0]."%' OR
                    n.suffix     LIKE '%".$pieces[0]."%')

                  AND

                    (n.firstname  LIKE '%".$pieces[1]."%' OR
                    n.middlename LIKE '%".$pieces[1]."%' OR
                    n.lastname   LIKE '%".$pieces[1]."%' OR
                    n.suffix     LIKE '%".$pieces[1]."%')

                  )
                  AND
                  n.person_id = p.id
                GROUP BY
                  p.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  elseif (count($pieces) == 3)
  {
#    echo " ---------  three pieces";
    $query = "SELECT n.id as name_id, p.id as person_id
                FROM
                  names n, people p
                WHERE
                  (

                    (n.firstname  LIKE '%".$pieces[0]."%' OR
                    n.middlename LIKE '%".$pieces[0]."%' OR
                    n.lastname   LIKE '%".$pieces[0]."%' OR
                    n.suffix     LIKE '%".$pieces[0]."%')

                  AND

                    (n.firstname  LIKE '%".$pieces[1]."%' OR
                    n.middlename LIKE '%".$pieces[1]."%' OR
                    n.lastname   LIKE '%".$pieces[1]."%' OR
                    n.suffix     LIKE '%".$pieces[1]."%')

                  AND

                    (n.firstname  LIKE '%".$pieces[2]."%' OR
                    n.middlename LIKE '%".$pieces[2]."%' OR
                    n.lastname   LIKE '%".$pieces[2]."%' OR
                    n.suffix     LIKE '%".$pieces[2]."%')

                  )
                  AND
                  n.person_id = p.id
                GROUP BY
                  p.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  else
  {
#    echo " ---  regular search - single piece";
    $query = "SELECT n.id as name_id, p.id as person_id
                FROM
                  names n, people p
                WHERE
                  (

                    (n.firstname  LIKE '%".$q."%' OR
                    n.middlename LIKE '%".$q."%' OR
                    n.lastname   LIKE '%".$q."%' OR
                    n.suffix     LIKE '%".$q."%')

                  )
                  AND
                  n.person_id = p.id
                GROUP BY
                  p.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    array_push($results,$line['person_id']);
  }
  return $results;
}

# -------------------------------------------------------------------------------
function title_abstract_search($q)
{
  global $dbh;
  $q = trim($q);
  if (!get_magic_quotes_gpc()) {$q = addslashes($q);}
  $results = array();
  if (strlen($q) < 2){
    return $results;
  }

  $pieces = explode(" ",$q);
#  print_r($pieces);
  if (count($pieces) == 2)
  {
#    echo " ---------  two pieces";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.title   LIKE '%".$pieces[0]."%' OR
                    d.abstract LIKE '%".$pieces[0]."%')

                  AND

                    (d.title   LIKE '%".$pieces[1]."%' OR
                    d.abstract LIKE '%".$pieces[1]."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  elseif (count($pieces) == 3)
  {
#    echo " ---------  three pieces";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.title   LIKE '%".$pieces[0]."%' OR
                    d.abstract LIKE '%".$pieces[0]."%')

                  AND

                    (d.title   LIKE '%".$pieces[1]."%' OR
                    d.abstract LIKE '%".$pieces[1]."%')

                  AND

                    (d.title   LIKE '%".$pieces[2]."%' OR
                    d.abstract LIKE '%".$pieces[2]."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  else
  {
#    echo " ---  regular search - single piece";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.title   LIKE '%".$q."%' OR
                    d.abstract LIKE '%".$q."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    array_push($results,$line['person_id']);
  }
  return $results;
}

# -------------------------------------------------------------------------------
function notes_search($q)
{
  global $dbh;
  $q = trim($q);
  if (!get_magic_quotes_gpc()) {$q = addslashes($q);}
  $results = array();
  if (strlen($q) < 2){
    return $results;
  }

  $pieces = explode(" ",$q);
#  print_r($pieces);
  if (count($pieces) == 2)
  {
#    echo " ---------  two pieces";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.notes    LIKE '%".$pieces[0]."%')

                  AND

                    (d.notes    LIKE '%".$pieces[1]."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  elseif (count($pieces) == 3)
  {
#    echo " ---------  three pieces";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.notes    LIKE '%".$pieces[0]."%')

                  AND

                    (d.notes    LIKE '%".$pieces[1]."%')

                  AND

                    (d.notes    LIKE '%".$pieces[2]."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  else
  {
#    echo " ---  regular search - single piece";
    $query = "SELECT d.person_id
                FROM
                  names n, dissertations d
                WHERE
                  (

                    (d.notes    LIKE '%".$q."%')

                  )
                  AND
                  n.person_id = d.person_id
                GROUP BY
                  d.id
                ORDER BY
                  n.lastname, n.suffix, n.firstname, n.middlename
              ";
  }
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    array_push($results,$line['person_id']);
  }
  return $results;
}

# -------------------------------------------------------------------------------
function encode_linebreaks($bigtext)
{
  # from http://us3.php.net/str_replace
  $order   = array("\r\n", "\n", "\r");
  $replace = '<br />';
  // Processes \r\n's first so they aren't converted twice.
  $with_breaks = str_replace($order, $replace, $bigtext);
  return $with_breaks;
}

# -------------------------------------------------------------------------------
function get_logs($offset="0")
{
  global $dbh;
#  echo "[$offset]";
  $query = "SELECT * FROM logs ORDER BY id DESC LIMIT 100";
  if ($offset != ""){$query .= " OFFSET $offset";}
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

#  echo $query;
  $entries = array();
  $counter = 0;
  while ( $line = mysqli_fetch_array($result)) {
    $counter++;
    $entries[$counter] = $line;
  }
  return $entries;
}


# -------------------------------------------------------------------------------
function get_dissertation_history($id)
{
  global $dbh;
  $query = "SELECT * FROM logs
              WHERE type='dissertation' AND message LIKE '$id %'
              ORDER BY id DESC";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

#  echo $query;
  $entries = array();
  $counter = 0;
  while ( $line = mysqli_fetch_array($result)) {
    $counter++;
    $entries[$counter] = $line;
  }
  return $entries;
}


# -------------------------------------------------------------------------------
function find_all_people()
{
  global $dbh;
  # return array of all people_ids in database (slow....)
    # all people
    $query = "SELECT id as person_id FROM people";
    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
    while ( $line = mysqli_fetch_array($result)) {
      $people[] = $line['person_id'];
    }
    return $people;
}

# -------------------------------------------------------------------------------
function get_person_link($person_id)
{

  $disciplines = find_disciplines();
  $person = find_person($person_id);

  $fullname = trim($person['firstname']." ".$person['middlename']." ".$person['lastname']." ".$person['suffix']);

  $dissertation = find_dissertation_by_person($person_id);
  $discipline = isset($dissertation['discipline_id']) ? $disciplines[$dissertation['discipline_id']] : "";

  $link = "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$person['id']."\" title=\"$discipline\">".$fullname."</a>";

  return $link;

}

# -------------------------------------------------------------------------------
function find_orphans()
{
  global $dbh;

  # finding people with no dissertation and no students

  # all people
  $query = "SELECT
              p.id as person_id, n.firstname, n.middlename, n.lastname, n.suffix
            FROM
              people p, names n
            WHERE
              p.preferred_name_id = n.id
            ORDER BY n.lastname,n.firstname,n.middlename
          ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $people[$line['person_id']] = $line;
#    echo " ".$line['person_id']."<br />\n";
  }

  echo "people: ".count($people)."<br />\n";

  # with dissertations only
  $query = "SELECT
              p.id as person_id
            FROM
              people p, dissertations d
            WHERE
              d.person_id = p.id
          ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $with_dissertations[$line['person_id']] = $line;
#    echo " ".$line['person_id']."<br />\n";
  }

  echo "dissertations: ".count($with_dissertations)."<br />\n";

  # all people - dissertations = teachers only
  $potentialmentors = array_key_diff($people,$with_dissertations);

  echo "potential mentors: ".count($potentialmentors)."<br />\n";

  # orphans = teachers who don't have students
  # get advisorships
  $query = "SELECT person_id FROM advisorships";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    $advisors[$line['person_id']] = $line;
  }
  echo "advisors: ".count($advisors)."<br />\n";
  # get committeeships
  $query = "SELECT person_id FROM committeeships";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    $committeemembers[$line['person_id']] = $line;
  }
  echo "committeemembers: ".count($committeemembers)."<br />\n";
  # subtract advisorships from teachers
  $potentialmentors = array_key_diff($potentialmentors,$advisors);
  # subtract committeeships from remaining teachers
  $orphans = array_key_diff($potentialmentors,$committeemembers);
  echo "orphans: ".count($orphans)."<br />\n";

  return $orphans;

}

# -------------------------------------------------------------------------------
function is_orphan($person_id)
{
  global $dbh;
  # confirm person exists
  $query = "SELECT * FROM people WHERE id = '".$person_id."'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  $line = mysqli_fetch_array($result);
  if (!$line['id']){echo "not a person";return 0;}
  # confirm no dissertation
  $query = "SELECT * FROM dissertations WHERE person_id = '".$person_id."'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    echo "found a dissertation";
    return 0;
  }
  # confirm not a committeemember
  $query = "SELECT * FROM committeeships WHERE person_id = '".$person_id."'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    echo "found a committeeship";
    return 0;
  }
  # confirm not an advisor
  $query = "SELECT * FROM advisorships WHERE person_id = '".$person_id."'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    echo "found an advisorship";
    return 0;
  }
  return 1;
}
# -------------------------------------------------------------------------------
function delete_person($person_id)
{
  global $dbh;
  if (is_orphan($person_id))
  {
    $before = find_person($person_id);
    # delete all names
    $query = "DELETE FROM names WHERE person_id = '".$person_id."'";
    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
    # delete the person
    $query = "DELETE FROM people WHERE id = '".$person_id."' LIMIT 1";
    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
    # log it
    mpact_logger("deleted person[".$person_id."] (".$before['fullname'].")");
    return 1;
  }
  else
  {
    action_box("Not an orphan - Cannot delete this person.");
    return 0;
  }
}
# -------------------------------------------------------------------------------
function remove_duplicate_names($person_id)
{
  global $dbh;

  $names = array();
  $deleteme = array();


  $before = find_person($person_id);

  // get the preferred_name_id
  $query = "SELECT preferred_name_id
        FROM people
        WHERE id = '".$person_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    extract($line);
  }

  // get the full preferred_name
  $query = "SELECT * FROM names
        WHERE
          id = '".$preferred_name_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $fullname = "".$line['firstname']." ".$line['middlename']." ".$line['lastname']." ".$line['suffix']."";
    $names[$fullname] = $line['id'];
  }

  // get the rest of the names for this person
  $query = "SELECT * FROM names
        WHERE
          person_id = '".$person_id."' AND
          id != '".$preferred_name_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $fullname = "".$line['firstname']." ".$line['middlename']." ".$line['lastname']." ".$line['suffix']."";
    if (isset($names[$fullname]))
    {
      $deleteme[] = $line['id'];
    }
    else
    {
      $names[$fullname] = $line['id'];
    }
  }

  // delete each deleteme id
  foreach ($deleteme as $one)
  {
    $query = "DELETE FROM names
          WHERE
            id = '".$one."'
        ";

    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  }

  # log it
  mpact_logger("removed duplicate names for person[".$person_id."] (".$before['fullname'].")");

}

# -------------------------------------------------------------------------------
function add_mentor($type,$student_id,$mentor_id)
{
  global $dbh;
  $student = find_person($student_id);
  $mentor = find_person($mentor_id);
  $dissertation = find_dissertation_by_person($student_id);

  if ($type == "A")
  {
    # find existing advisors for this student
    $advisors = find_advisors_for_person($student_id);
    # if new/different - then add them to the student's dissertation
    if (in_array($mentor_id,$advisors))
    {
      # skip - already listed as advisor
    }
    else
    {
      $query = "INSERT advisorships
                  SET
                  dissertation_id = '".$dissertation['id']."',
                  person_id       = '".$mentor_id."'
                ";
#      echo $query;
      $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
      # recalculate scores for both
      calculate_scores($student_id);
      calculate_scores($mentor_id);
      # log it
      mpact_logger("add advisorship between mentor[".$mentor_id."] (".$mentor['fullname'].") to dissertation[".$dissertation['id']."] (".$student['fullname'].")");
      # delete existing dotgraphs - they'll need to be regenerated
      # has to happen after we add the advisor link itself
      delete_associated_dotgraphs($student_id);
    }
  }
  elseif ($type == "C")
  {
    # find existing committee members for this student
    $committee = find_committee_for_person($student_id);
    # if new/different - then add them to the student's dissertation
    if (in_array($mentor_id,$committee))
    {
      # skip - already on committee
    }
    else
    {
      $query = "INSERT committeeships
                  SET
                  dissertation_id = '".$dissertation['id']."',
                  person_id       = '".$mentor_id."'
                ";
#      echo $query;
      $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
      # recalculate scores for both
      calculate_scores($student_id);
      calculate_scores($mentor_id);
      # log it
      mpact_logger("add committeeship between mentor[".$mentor_id."] (".$mentor['fullname'].") to dissertation[".$dissertation['id']."] (".$student['fullname'].")");
    }
  }
  else
  {
    # nothing
  }
}

# -------------------------------------------------------------------------------
function remove_mentor($type,$student_id,$mentor_id)
{
  global $dbh;
  $dissertation = find_dissertation_by_person($student_id);

  $student = find_person($student_id);
  $mentor = find_person($mentor_id);

  if ($type == "A")
  {
    # find dissertation id
    $dissertation = find_dissertation_by_person($student_id);
    # find existing advisors for this student
    $advisors = find_advisors_for_person($student_id);
    # if found in db, remove them
    if (!in_array($mentor_id,$advisors))
    {
      echo "not an advisor for this student";
      return 0;
    }
    else
    {
      # delete existing dotgraphs - they'll need to be regenerated
      # has to happen before we delete the advisor link itself
      delete_associated_dotgraphs($student_id);
      # delete the advisorship
      $query = "DELETE FROM advisorships
            WHERE
              person_id = '".$mentor_id."' AND
              dissertation_id = '".$dissertation['id']."'
            LIMIT 1
          ";
      $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
      # recalculate scores for both
      calculate_scores($student_id);
      calculate_scores($mentor_id);
      # log it
      mpact_logger("remove advisorship between mentor[".$mentor_id."] (".$mentor['fullname'].") to dissertation[".$dissertation['id']."] (".$student['fullname'].")");
      return 1;
    }
  }
  elseif ($type == "C")
  {
    # find dissertation id
    $dissertation = find_dissertation_by_person($student_id);
    # find existing committee members for this student
    $committee = find_committee_for_person($student_id);
    # if found in db, remove them
    if (!in_array($mentor_id,$committee))
    {
      echo "not on committee for this student";
      return 0;
    }
    else
    {
      $query = "DELETE FROM committeeships
            WHERE
              person_id = '".$mentor_id."' AND
              dissertation_id = '".$dissertation['id']."'
            LIMIT 1
          ";
      $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
      # recalculate scores for both
      calculate_scores($student_id);
      calculate_scores($mentor_id);
      # log it
      mpact_logger("remove advisorship between mentor[".$mentor_id."] (".$mentor['fullname'].") to dissertation[".$dissertation['id']."] (".$student['fullname'].")");
      return 1;
    }
  }
  else
  {
    echo "has to be A or C, dude";
    return 0;
  }

}

# -------------------------------------------------------------------------------
function find_all_people_for_selectbox($with_dissertation="0")
{
  global $dbh;
  if ($with_dissertation == "0")
  {
    $query = "SELECT
                p.id as person_id, n.firstname, n.middlename, n.lastname, n.suffix
              FROM
                people p, names n
              WHERE
                p.preferred_name_id = n.id
              ORDER BY n.lastname,n.firstname,n.middlename
            ";
  }
  else
  {
    $query = "SELECT
                p.id as person_id, n.firstname, n.middlename, n.lastname, n.suffix
              FROM
                people p, names n, dissertations d
              WHERE
                p.preferred_name_id = n.id AND
                d.person_id = p.id
              ORDER BY n.lastname,n.firstname,n.middlename
            ";
  }
  // echo $query;
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $people[$line['person_id']] = $line;
  }

  return $people;
}

# -------------------------------------------------------------------------------
function merge_two_people($from_id, $into_id)
{
  global $dbh;

  $from_person = find_person($from_id);
  $into_person = find_person($into_id);


  $query = "UPDATE advisorships
        SET
          person_id = '".$into_person['id']."'
        WHERE
          person_id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $query = "UPDATE dissertations
        SET
          person_id = '".$into_person['id']."'
        WHERE
          person_id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $query = "UPDATE committeeships
        SET
          person_id = '".$into_person['id']."'
        WHERE
          person_id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $query = "UPDATE urls
        SET
          person_id = '".$into_person['id']."'
        WHERE
          person_id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  // move each 'from' name to the 'into' person

  $query = "UPDATE names
        SET
          person_id = '".$into_person['id']."'
        WHERE
          person_id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  // remove any new duplicates in the names table

  remove_duplicate_names($into_person['id']);

  // remove 'from' person from the database

  $query = "DELETE FROM people
        WHERE
          id = '".$from_person['id']."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  # log it
  mpact_logger("merged two people [".$from_id."] (".$from_person['fullname'].") and [".$into_id."] (".$into_person['fullname'].")");


}

# -------------------------------------------------------------------------------
function find_aliases($person_id)
{
  global $dbh;

  $query = "SELECT preferred_name_id
        FROM people
        WHERE id = '".$person_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    extract($line);
  }

  $names = array();
  $query = "SELECT
        n.firstname, n.middlename,
        n.lastname, n.suffix, n.id
        FROM
          names n
        WHERE
          n.person_id = '".$person_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $names[$line['id']] = $line;
  }

  unset($names[$preferred_name_id]);

  return $names;
}

# -------------------------------------------------------------------------------
function find_person($person_id)
{
  global $dbh;
  $query = "SELECT
        n.firstname, n.middlename,
        n.lastname, n.suffix, p.degree,
        p.id, p.preferred_name_id
        FROM
          names n, people p
        WHERE
          p.preferred_name_id = n.id AND
          p.id = ".$person_id."
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $person = $line;
    $person['fullname'] = $person['firstname'];
    if ($person['middlename'] != ""){$person['fullname'] .= " ".$person['middlename'];}
    if ($person['lastname'] != ""){$person['fullname'] .= " ".$person['lastname'];}
    if ($person['suffix'] != ""){$person['fullname'] .= " ".$person['suffix'];}
  }

  return $person;
}

# -------------------------------------------------------------------------------
function find_url($url_id)
{
  global $dbh;

  $url_id = intval($url_id);
  $query = "SELECT
        u.id, u.url, u.description, u.updated_at, u.person_id
        FROM
          urls u
        WHERE
          u.id = '".$url_id."'
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $url = $line;
  }

  return $url;

}

# -------------------------------------------------------------------------------
function find_urls_by_person($person_id)
{
  global $dbh;

  $person_id = intval($person_id);
  $query = "SELECT
        u.id, u.url, u.description, u.updated_at, u.person_id
        FROM
          urls u
        WHERE
          u.person_id = '".$person_id."'
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $urls = array();
  while ( $line = mysqli_fetch_array($result)) {
    $urls[$line['id']] = $line;
  }

  return $urls;

}

# -------------------------------------------------------------------------------
function find_dissertation($dissertation_id)
{
  global $dbh;

  $dissertation_id = intval($dissertation_id);
  $query = "SELECT
        d.id, d.person_id, d.completedyear, d.status,
        d.title, d.abstract, d.notes, d.school_id, d.discipline_id
        FROM
          dissertations d
        WHERE
          d.id = '".$dissertation_id."'
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $dissertation = array();
  while ( $line = mysqli_fetch_array($result)) {
    $dissertation = $line;
  }

  return $dissertation;
}

# -------------------------------------------------------------------------------
function find_dissertation_by_person($person_id)
{
  global $dbh;

  $person_id = intval($person_id);
  $query = "SELECT
        d.id, d.person_id, d.completedyear, d.status,
        d.title, d.abstract, d.notes, d.school_id, d.discipline_id
        FROM
          dissertations d
        WHERE
          d.person_id = '".$person_id."'
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $dissertation = array();
  while ( $line = mysqli_fetch_array($result)) {
    $dissertation = $line;
  }

  return $dissertation;
}

# -------------------------------------------------------------------------------
function find_name($name_id)
{
  global $dbh;
  $query = "SELECT
        n.firstname, n.middlename,
        n.lastname, n.suffix
        FROM
          names n
        WHERE
          n.id = ".$name_id."
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $name = $line;
    $name['fullname'] = $name['firstname'];
    if ($name['middlename'] != ""){$name['fullname'] .= " ".$name['middlename'];}
    if ($name['lastname'] != ""){$name['fullname'] .= " ".$name['lastname'];}
    if ($name['suffix'] != ""){$name['fullname'] .= " ".$name['suffix'];}
  }

  return $name;
}

# -------------------------------------------------------------------------------
function find_discipline($discipline_id)
{
  global $dbh;
  $discipline_id = intval($discipline_id);
  $query = "SELECT
        d.title, d.description
        FROM
          disciplines d
        WHERE
          d.id = ".$discipline_id."
      ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $discipline = $line;
  }

  return $discipline;
}

# -------------------------------------------------------------------------------
function find_ancestors_for_group($groupofpeople)
{
  $originalgroup = $groupofpeople;
  // find advisors, and if they're new, add them to the list and recurse
  foreach ($originalgroup as $person)
  {
    $advisors = find_advisors_for_person($person);
    foreach ($advisors as $one)
    {
      $groupofpeople[] = $one;
    }
  }
  $groupofpeople = array_unique($groupofpeople);
  if (count(array_diff($groupofpeople,$originalgroup)) > 0)
  {
    return find_ancestors_for_group($groupofpeople);
  }
  else
  {
    return $originalgroup;
  }
}

# -------------------------------------------------------------------------------
function find_descendents_for_group($groupofpeople)
{
  $originalgroup = $groupofpeople;
  // find descendents, and if they're new, add them to the list and recurse
  foreach ($originalgroup as $person)
  {
    $advisees = find_advisorships_under_person($person);
    foreach ($advisees as $one)
    {
      $groupofpeople[] = $one;
    }
  }
  $groupofpeople = array_unique($groupofpeople);
  if (count(array_diff($groupofpeople,$originalgroup)) > 0)
  {
    return find_descendents_for_group($groupofpeople);
  }
  else
  {
    return $originalgroup;
  }
}

# -------------------------------------------------------------------------------
function T_score($person)
{
  # total number of descendants (nodes) in a person's tree
  $group = array($person);
  $total = count(find_descendents_for_group($group)); # including self
  $descendents = $total - 1; # minus self
  return $descendents;
}

# -------------------------------------------------------------------------------
function find_mpact_score($person,$score_type)
{
  global $dbh;
  $query = "SELECT $score_type as score FROM people WHERE id='$person'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    extract($line);
  }
  return $score;
}

# -------------------------------------------------------------------------------
function FMI_score($person)
{
    $FMI_score = count(find_advisorships_under_person($person));
#    print "person $person -- fmi $FMI_score<br />";
    $committeeships = find_committeeships_under_person($person);
    foreach ($committeeships as $one)
    {
      $numA = count(find_advisors_for_person($one));
#      echo " - #A [$numA] ---- <br />";
      $numC = count(find_committee_for_person($one));
#      echo "#C [$numC] ---- \n";
      $FMI_score += 1/($numA + $numC);
#      echo "<br />";
    }
  //  echo "fractional mentorship, inclusive (FMI =  A + eachC(1/(#A+#C)) )<br />\n";
    return round($FMI_score,3);
}

# -------------------------------------------------------------------------------
function FME_score($person)
{
    $FME_score = count(find_advisorships_under_person($person));
#    print "person $person -- fme $FME_score<br />";
    $committeeships = find_committeeships_under_person($person);
    foreach ($committeeships as $one)
    {
      $numC = count(find_committee_for_person($one));
#      echo "#C [$numC] ---- \n";
      $FME_score += 1/($numC);
#      echo "<br />";
    }
  //  echo "fractional mentorship, inclusive (FMI =  A + eachC(1/(#A+#C)) )<br />\n";
    return round($FME_score,3);
}

# -------------------------------------------------------------------------------
function TA_score($person)
{
  # total number of descendants who have advised students themselves
  $without_students = array();
  $descendents = find_descendents_for_group(array($person)); # including self
  foreach ($descendents as $d)
  {
    if (count(find_descendents_for_group(array($d))) == 1)
    {
      $without_students[] = $d;
    }
  }
  $descendents_who_advised = array_diff($descendents,$without_students); # including self
  return max(count($descendents_who_advised) - 1,0); # minus self
}

# -------------------------------------------------------------------------------
function W_score($person)
{
  # should handle multigenerational advisorships correctly...

  # return the max of the widths of each generation of descendents
  $descendents = array_diff(find_descendents_for_group(array($person)),array($person)); # without self # 16
  $gencount = 1;
  $advisees = find_advisorships_under_person($person); #5
  $gen_widths[$gencount] = count($advisees);
  $remaining = array_diff($descendents,$advisees); # 11
#  print "$gencount <-- gen<br />";
#  print count($advisees)." advisees - ";
#  print_r($advisees);
#  print "<br />";
#  print "<br />";
#  print count($remaining)." remaining - ";
#  print_r($remaining);
#  print "<br />";
#  print "<br />";
  while (count($remaining) > 0)
  {
    $gencount++;
#    print "$gencount <-- gen<br />";
    $advisees = advisees_of_group($advisees);
    $gen_widths[$gencount] = count($advisees);
    $remaining = array_diff($remaining,$advisees);
#    print count($advisees)." advisees - ";
#    print_r($advisees);
#    print "<br />";
#    print "<br />";
#    print count($remaining)." remaining - ";
#    print_r($remaining);
#    print "<br />";
#    print "<br />";
  }
#  print_r($gen_widths);
  return max($gen_widths);
}

# -------------------------------------------------------------------------------
function TD_score($person)
{
  # there is currently a bug in this implementation
  # if a student has two advisors, and they, themselves are related by mentorship
  # this algorithm currently counts gen_width based on advisee count only
  # since $td is calculated based on gen_width values, TD will be missing data
  # A is advisor to B and C
  # B is advisor to C
  # A should have a TD score of 2 (full credit for both advisees)
  # upon further reflection, perhaps this is working correctly, by accident

  # return the decayed score
  $descendents = array_diff(find_descendents_for_group(array($person)),array($person)); # without self # 16
  $gencount = 1;
  $advisees = find_advisorships_under_person($person); #5
  $gen_widths[$gencount] = count($advisees);
  $remaining = array_diff($descendents,$advisees); # 11
  while (count($remaining) > 0)
  {
    $gencount++;
    $advisees = advisees_of_group($advisees);
    $gen_widths[$gencount] = count($advisees);
    $remaining = array_diff($remaining,$advisees);
  }
#  print_r($gen_widths);
  $td = 0;
  foreach($gen_widths as $one => $two)
  {
    $num = $two;
    $den = (pow(2,($one-1)));
    $f = $num/$den;
#    print "$num/$den = $f<br />";
    $td = $td + $f;
  }
  return $td;
}

# -------------------------------------------------------------------------------
function G_score($person)
{
  # there is currently a bug in this implementation
  # if a student has two advisors, and they, themselves are related by mentorship
  # this algorithm currently misses a generation in the calculation of G
  # A is advisor to B and C
  # B is advisor to C
  # A should have a G score of 2, but will only be calculated a 1


  # return the number of generations of descendents
  $descendents = array_diff(find_descendents_for_group(array($person)),array($person)); # without self # 16
  $gencount = 1;
  $advisees = find_advisorships_under_person($person); #5
  $gen_widths[$gencount] = count($advisees);
  $remaining = array_diff($descendents,$advisees); # 11
  while (count($remaining) > 0)
  {
    $gencount++;
    $advisees = advisees_of_group($advisees);
    $gen_widths[$gencount] = count($advisees);
    $remaining = array_diff($remaining,$advisees);
  }
  if ($gen_widths[1] == 0)
  {
    return 0;
  }
  else
  {
    return count($gen_widths);
  }
}

# -------------------------------------------------------------------------------
function advisees_of_group($group)
{
  # W helper function
  $advisees = array();
  foreach ($group as $one)
  {
    $advisees[] = find_advisorships_under_person($one);
  }
  $advisees = flattenArray($advisees);
#  print_r($advisees);
#  print " <-- advisees of group<br/>";
  return $advisees;
}

# -------------------------------------------------------------------------------
function flattenArray($array)
{
   $flatArray = array();
   foreach( $array as $subElement ) {
       if( is_array($subElement) )
           $flatArray = array_merge($flatArray, flattenArray($subElement));
       else
           $flatArray[] = $subElement;
   }
   return $flatArray;
}

# -------------------------------------------------------------------------------
function calculate_scores($person_id)
{
  global $dbh;
  $A_score = count(find_advisorships_under_person($person_id));
  $C_score = count(find_committeeships_under_person($person_id));
  $AC_score = $A_score + $C_score;
  $G_score = G_score($person_id);
  $W_score = W_score($person_id);
  $T_score = T_score($person_id);
  $TA_score = TA_score($person_id);
  $TD_score = TD_score($person_id);
  $FMI_score = FMI_score($person_id);
  $FME_score = FME_score($person_id);

  $query = "UPDATE people
              SET
                a_score = '$A_score',
                c_score = '$C_score',
                ac_score = '$AC_score',
                g_score = '$G_score',
                w_score = '$W_score',
                t_score = '$T_score',
                ta_score = '$TA_score',
                td_score = '$TD_score',
                fmi_score = '$FMI_score',
                fme_score = '$FME_score',
                scores_calculated = now()
              WHERE
                id = ".$person_id."
            ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
}

# -------------------------------------------------------------------------------
function mpact_scores($passed_person)
{
  global $dbh;
  $glossaryterms = find_glossaryterms();

  $query = "SELECT
              a_score, c_score, ac_score, g_score, w_score,
              t_score, ta_score, td_score, fmi_score, fme_score,
              scores_calculated
              FROM people
              WHERE id = ".$passed_person."
            ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result)) {
    extract($line);
  }

  $mpact['A'] = $a_score;
  $mpact['C'] = $c_score;
  $mpact['AC'] = $ac_score;
  $scores_output = "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['A']]."\">A</a> = $a_score<br />\n";
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['C']]."\">C</a> = $c_score<br />\n";
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['A+C']]."\">A+C</a> = $ac_score<br />\n";

  $mpact['FMI'] = $fmi_score;
  $mpact['FME'] = $fme_score;
  $FMIdivFULL = ($ac_score>0) ? $fmi_score / $ac_score : 0;
  $FMIdivFULL = round($FMIdivFULL,3);
  $mpact['FMIdivFULL'] = $FMIdivFULL;
  $FMEdivFULL = ($ac_score>0) ? $fme_score / $ac_score : 0;
  $FMEdivFULL = round($FMEdivFULL,3);
  $mpact['FMEdivFULL'] = $FMEdivFULL;
  if (is_admin())
  {
    $scores_output .= "FMI = $fmi_score<br />\n";
    $scores_output .= "FME = $fme_score<br />\n";
    $scores_output .= "FMI/(A+C) = $FMIdivFULL<br />\n";
    $scores_output .= "FME/(A+C) = $FMEdivFULL<br />\n";
  }

  $mpact['T'] = $t_score;
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['T']]."\">T</a> = $t_score<br />\n";
  $mpact['G'] = $g_score;
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['G']]."\">G</a> = $g_score<br />\n";
  $mpact['W'] = $w_score;
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['W']]."\">W</a> = $w_score<br />\n";
  $mpact['TD'] = $td_score;
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['T<sub>D</sub>']]."\">T<sub>D</sub></a> = $td_score<br />\n";
  $mpact['TA'] = $ta_score;
  $scores_output .= "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title=\"".$glossaryterms['defs'][$glossaryterms['ids']['T<sub>A</sub>']]."\">T<sub>A</sub></a> = $ta_score<br />\n";
  $scores_output .= "calculated $scores_calculated<br />\n";
  $mpact['output'] = $scores_output;

  return $mpact;
}

# -------------------------------------------------------------------------------
function find_advisors_for_person($person_id)
{
  global $dbh;

  // get person's own advisor(s)

  $listing = array();


  $query = "SELECT a.person_id
        FROM
          dissertations d,
          advisorships a
        WHERE
          d.person_id = ".$person_id." AND
          a.dissertation_id = d.id
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $listing[] = $line['person_id'];
  }

  return $listing;
}

# -------------------------------------------------------------------------------
function find_committee_for_person($person_id)
{
  global $dbh;

  // get person's own committee member(s)

  $listing = array();

  $query = "SELECT c.person_id
        FROM
          dissertations d,
          committeeships c
        WHERE
          d.person_id = ".$person_id." AND
          c.dissertation_id = d.id
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $listing[] = $line['person_id'];
  }

  return $listing;
}

# -------------------------------------------------------------------------------
function find_advisorships_under_person($person_id)
{
  global $dbh;

  // get person's advisorships (below)

  $listing = array();

  $query = "SELECT d.person_id
        FROM
          dissertations d,
          advisorships a
        WHERE
          a.person_id = ".$person_id." AND
          a.dissertation_id = d.id
        ORDER BY
          d.completedyear ASC
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $listing[] = $line['person_id'];
  }

  return $listing;
}

# -------------------------------------------------------------------------------
function find_committeeships_under_person($person_id)
{
  global $dbh;

  // get person's committeeships (below)

  $listing = array();

  $query = "SELECT d.person_id
        FROM
          dissertations d,
          committeeships c
        WHERE
          c.person_id = '".$person_id."' AND
          c.dissertation_id = d.id
        ORDER BY
          d.completedyear
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $listing[] = $line['person_id'];
  }

  return $listing;
}

# -------------------------------------------------------------------------------
function set_preferred_name($person_id,$name_id)
{
  global $dbh;

  # delete current family tree of dotgraphs
  delete_associated_dotgraphs($person_id);

  # set it

  $listing = array();

  $before = find_person($person_id);

  $query = "UPDATE people
        SET
          preferred_name_id = '".$name_id."'
        WHERE
          id = '".$person_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  $after = find_person($person_id);

  # log it
  mpact_logger("set preferred name for person[".$person_id."] from name[".$before['preferred_name_id']."] (".$before['fullname'].") to name[".$name_id."] (".$after['fullname'].")");
}

# -------------------------------------------------------------------------------
function generate_profs_at_dept($school_id,$discipline_id)
{
  global $dbh;

  $dissertations = array();
  $listing = array();
  $advisors = array();
  $committeemembers = array();

  // get all dissertations at this dept

  $query = "SELECT d.id
        FROM dissertations d, schools s
        WHERE
          d.school_id = s.id AND
          s.id = '".$school_id."' AND
          d.discipline_id = '".$discipline_id."'
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $dissertations[] = $line['id'];
  }

  if (count($dissertations)>0)
  {
    // get all advisors for these dissertations

    $query = "SELECT a.person_id
          FROM dissertations d, advisorships a
          WHERE
            a.dissertation_id IN (";
    $query .= implode(", ", $dissertations);
    $query .= ") AND
            a.dissertation_id = d.id";

    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

    while ( $line = mysqli_fetch_array($result))
    {
      $advisors[$line['person_id']] = $line['person_id'];
    }

    // get all committeemembers for these dissertations

    $query = "SELECT c.person_id
          FROM dissertations d, committeeships c
          WHERE
            c.dissertation_id IN (";
    $query .= implode(", ", $dissertations);
    $query .= ") AND
            c.dissertation_id = d.id";

    $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

    while ( $line = mysqli_fetch_array($result))
    {
      $committeemembers[$line['person_id']] = $line['person_id'];
    }
  }

  // return the combined list (uniquified when combined)

//  echo "\na - ".count($advisors);
//  echo "\nc - ".count($committeemembers);
  $listing = $advisors + $committeemembers;
//  echo "\ncombined before - ".count($listing);
  return $listing;

}

# -------------------------------------------------------------------------------
function find_profs_at_dept($school_id,$discipline_id)
{
  global $dbh;
  $query = "SELECT professors
              FROM profs_at_dept
              WHERE
                school_id     = '$school_id' AND
                discipline_id = '$discipline_id'
            ";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result))
  {
    $listing = unserialize($line['professors']);
  }
  return $listing;

}

# -------------------------------------------------------------------------------
function find_disciplines($school_id=null)
{
  global $dbh;

  $disciplines = array();
  if ($school_id)
  {
    // get all disciplines at this school
    $query = "SELECT d.id, d.title
                FROM
                  disciplines d, schools s, dissertations diss
                WHERE
                  diss.school_id = s.id AND
                  diss.discipline_id = d.id AND
                  s.id = '$school_id'
                ORDER BY
                  d.title
             ";
  }
  else
  {
    $query = "SELECT id, title
                FROM
                  disciplines d
                ORDER BY
                  title
              ";
  }
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $disciplines[$line['id']] = $line['title'];
  }

  if (isset($disciplines))
  {
    return $disciplines;
  }
}

# -------------------------------------------------------------------------------
function is_duplicate_discipline($title)
{
  global $dbh;
  $disciplinefound = 0;

  $query = "SELECT id FROM disciplines WHERE title = '$title'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $disciplinefound = $line['id'];
  }

  if ($disciplinefound > 0){
    return true;
  }

  return false;
}

# -------------------------------------------------------------------------------
function is_empty_discipline($discipline_id)
{
  global $dbh;
  $query = "SELECT count(*) as howmany FROM dissertations WHERE discipline_id = $discipline_id";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    extract($line);
  }

  if ($howmany > 0){
    return false;
  }

  return true;
}

# -------------------------------------------------------------------------------
function is_duplicate_school($fullname)
{
  global $dbh;
  $schoolfound = 0;

  $query = "SELECT id FROM schools WHERE fullname = '$fullname'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    $schoolfound = $line['id'];
  }

  if ($schoolfound > 0){
    return true;
  }

  return false;
}

# -------------------------------------------------------------------------------
function is_empty_school($school_id)
{
  global $dbh;
  $query = "SELECT count(*) as howmany FROM dissertations WHERE school_id = $school_id";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result))
  {
    extract($line);
  }

  if ($howmany > 0){
    return false;
  }

  return true;
}

# -------------------------------------------------------------------------------
function find_discipline_counts()
{
  global $dbh;

   // get all discipline counts from the database

   $disciplinecounts = array();

   $query = "SELECT discipline_id, count(*) as disciplinecount
               FROM dissertations
               GROUP BY discipline_id
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $disciplinecounts[$line['discipline_id']] = $line['disciplinecount'];
   }

   if (isset($disciplinecounts))
   {
     return $disciplinecounts;
   }
}

# -------------------------------------------------------------------------------
function find_discipline_statuses($discipline_id)
{
  global $dbh;

   // get status counts for this discipline

   GLOBAL $statuscodes;

   $statuscounts = array();

   $query = "SELECT status, count(*) as disciplinecount
               FROM dissertations
               WHERE discipline_id='$discipline_id'
               GROUP BY status
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $statuscounts[$line['status']] = $line['disciplinecount'];
   }

   foreach (range(0,4) as $one)
   {
     if (!isset($statuscounts[$one]))
     {
       $statuscounts[$one] = 0;
     }
   }
   return $statuscounts;
}

# -------------------------------------------------------------------------------
function find_dept_counts($school_id,$discipline_id)
{
  global $dbh;

   // get all department counts from the database

   $deptcounts = array();

   $query = "SELECT discipline_id, count(*) as disciplinecount
               FROM dissertations
               GROUP BY discipline_id
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $deptcounts[$line['discipline_id']] = $line['disciplinecount'];
   }

   if (isset($deptcounts))
   {
     return $deptcounts;
   }
}

# -------------------------------------------------------------------------------
function find_dept_statuses($school_id,$discipline_id)
{
  global $dbh;

   // get status counts for this school in this discipline

   GLOBAL $statuscodes;

   $statuscounts = array();

   $query = "SELECT status, count(*) as disciplinecount
               FROM dissertations
               WHERE
                discipline_id='$discipline_id' AND
                school_id='$school_id'
               GROUP BY status
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $statuscounts[$line['status']] = $line['disciplinecount'];
   }

   foreach (range(0,4) as $one)
   {
     if (!isset($statuscounts[$one]))
     {
       $statuscounts[$one] = 0;
     }
   }
   return $statuscounts;
}

# -------------------------------------------------------------------------------
function find_schools($discipline_id=null)
{
  global $dbh;
  // get all schools in a discipline (or all disciplines)
  $schools = array();
  if ($discipline_id)
  {
    // look up schools at dissertations in this discipline
    $query = "SELECT s.id, s.fullname
              FROM
                dissertations d, schools s
              WHERE
                d.school_id = s.id AND
                d.discipline_id = '$discipline_id'
              GROUP BY
                d.school_id
              ORDER BY
                s.fullname ASC
            ";
  }
  else
  {
    $query = "SELECT id, fullname
                FROM
                  schools s
                ORDER BY fullname
              ";
  }
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
  while ( $line = mysqli_fetch_array($result))
  {
    $schools[$line['id']] = $line['fullname'];
  }

  if (isset($schools))
  {
    return $schools;
  }
}

# -------------------------------------------------------------------------------
function find_school_counts()
{
  global $dbh;

   // get all school dissertation counts from the database

   $schoolcounts = array();

   $query = "SELECT school_id, count(*) as disscount
               FROM dissertations
               GROUP BY school_id
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $schoolcounts[$line['school_id']] = $line['disscount'];
   }

   if (isset($schoolcounts))
   {
     return $schoolcounts;
   }
}

# -------------------------------------------------------------------------------
function find_school_statuses($school_id)
{
  global $dbh;

   // get status counts for this school

   GLOBAL $statuscodes;

   $statuscounts = array();

   $query = "SELECT status, count(*) as disscount
               FROM dissertations
               WHERE school_id='$school_id'
               GROUP BY status
            ";

   $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

   while ( $line = mysqli_fetch_array($result))
   {
    $statuscounts[$line['status']] = $line['disscount'];
   }

   foreach (range(0,4) as $one)
   {
     if (!isset($statuscounts[$one]))
     {
       $statuscounts[$one] = 0;
     }
   }
   return $statuscounts;
}


# -------------------------------------------------------------------------------
function find_persons_school($passed_person)
{
  global $dbh;

  // get person's degree school

  $query = "SELECT
              d.id as dissertation_id,
              d.completedyear,
              d.title,
              d.abstract,
              s.fullname,
              s.country,
              s.id as schoolid
        FROM
          dissertations d,
          schools s
        WHERE
          d.person_id = ".$passed_person." AND
          d.school_id = s.id
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $thisperson['dissertation_id']  = $line['dissertation_id'];
    $thisperson['completedyear']  = $line['completedyear'];
    $thisperson['title']  = $line['title'];
    $thisperson['abstract']  = $line['abstract'];
    $thisperson['country'] = $line['country'];
    $thisperson['school'] = $line['fullname'];
    $thisperson['schoolid'] = $line['schoolid'];
  }

  if (isset($thisperson))
  {
    return $thisperson;
  }
}

# -------------------------------------------------------------------------------
function find_school($school_id)
{
  global $dbh;

  // get school

  $query = "SELECT
              s.fullname,
              s.country
        FROM
          schools s
        WHERE
          s.id = ".$school_id."
      ";

  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

  while ( $line = mysqli_fetch_array($result)) {
    $school['country'] = $line['country'];
    $school['fullname'] = $line['fullname'];
  }

  if (isset($school))
  {
    return $school;
  }
}

# -------------------------------------------------------------------------------
function get_environment_info()
{
  $host_info = array();
  $hostname = $_SERVER['SERVER_NAME'];
  if ($hostname == ""){$hostname = exec(hostname);}
#  echo "hostname = [$hostname]<br />";
  if ($hostname == "www.ibiblio.org" || $hostname == "www-dev.ibiblio.org" || $hostname == "login1.ibiblio.org")
  {
    # the main install on ibiblio
    $host_info['hostname']    = "ibiblio";
    $host_info['ownername']   = "mpact";
    $host_info['dotlocation'] = "/export/sunsite/users/mpact/terrelllocal/bin/dot";
    $host_info['appdir']      = "/public/html/mpact";
    $host_info['webdir']      = "http://www.ibiblio.org/mpact";
    $host_info['dotcachedir'] = "dotgraphs";
    $host_info['dotfiletype'] = "png";
    $host_info['dotfontface'] = "Times-Roman";
  }
  else if ($hostname == "trel.dyndns.org")
  {
    # my local development machine
    $host_info['hostname']    = "home";
    $host_info['ownername']   = "trel";
    $host_info['dotlocation'] = "/sw/bin/dot";
    $host_info['appdir']      = "/Library/WebServer/Documents/MPACTlocal/app";
    $host_info['webdir']      = "http://trel.dyndns.org:9000/mpactlocal/app";
    $host_info['dotcachedir'] = "dotgraphs";
    $host_info['dotfiletype'] = "png";
    $host_info['dotfontface'] = "cour";
  }
  else if ($hostname == "localhost.com" || $hostname == "trelpancake")
  {
    # my laptop
    $host_info['hostname']    = "laptop";
    $host_info['ownername']   = "trel";
    $host_info['dotlocation'] = "/opt/local/bin/dot";
    $host_info['appdir']      = "/Users/trel/Sites/MPACT";
    $host_info['webdir']      = "http://localhost.com/~trel/MPACT";
    $host_info['dotcachedir'] = "dotgraphs";
    $host_info['dotfiletype'] = "png";
    $host_info['dotfontface'] = "cour";
  }
  else
  {
    # unknown host
    #exit;
  }

  return $host_info;
}

# -------------------------------------------------------------------------------
function delete_associated_dotgraphs($passed_person)
{
  $host_info = get_environment_info();
  $group = array($passed_person);
  $ancestors = find_ancestors_for_group($group);
  $descendents = find_descendents_for_group($group);
  $entire_family_tree = array_unique($ancestors + $descendents);
  foreach ($entire_family_tree as $one)
  {
    # set the filenames
    $dotfilename = $host_info['appdir']."/".$host_info['dotcachedir']."/$one.dot";
    $imagefilename = $host_info['appdir']."/".$host_info['dotcachedir']."/$one.".$host_info['dotfiletype'];
    $imagemapfilename = $host_info['appdir']."/".$host_info['dotcachedir']."/$one.map";
    # delete each if they exist
    if (file_exists($dotfilename))
    {
      `rm $dotfilename`;
    }
    if (file_exists($imagefilename))
    {
      `rm $imagefilename`;
    }
    if (file_exists($imagemapfilename))
    {
      `rm $imagemapfilename`;
    }
    # mark as 'dirty' so cronjob can recreate images
    mark_record_as_dirty($one);
  }
}

# -------------------------------------------------------------------------------
function mark_record_as_dirty($passed_person)
{
  global $dbh;
  # mark database record for this person as dirty
  # a cronjob will pick these up and regenerate their dotgraphs
  # gets around permission issues on the server if necessary
  $query = "UPDATE people SET regenerate_dotgraph = '1' WHERE id = '".$passed_person."'";
  $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));
}

# -------------------------------------------------------------------------------
function narray_slice($array, $offset, $length) {
    # http://us3.php.net/manual/en/function.array-slice.php#73882

  //Check if this version already supports it
  if (str_replace('.', '', PHP_VERSION) >= 502)
    return array_slice($array, $offset, $length, true);

  foreach ($array as $key => $value) {

    if ($a >= $offset && $a - $offset <= $length)
      $output_array[$key] = $value;
    $a++;

  }
  return $output_array;
}

# -------------------------------------------------------------------------------
function generate_dotfile($passed_person){
  $a = find_person($passed_person);
  if ($a == null)
  {
    return 0;
  }
  else
  {
    $dotfilecontents = "";
    $dotfilecontents .= "digraph familytree\n";
    $dotfilecontents .= "{\n";
    $dotfilecontents .= "rankdir=\"LR\"\n";
    $dotfilecontents .= "node [fontname = Times, fontsize=10, shape = rect, height=.15]\n";
    # ancestors
    $upgroup = array();
    $upgroup[] = $passed_person;
    $ancestors = find_ancestors_for_group($upgroup);
    foreach ($ancestors as $one)
    {
      $person = find_person($one);
      $dotfilecontents .= "$one [label = \"".$person['fullname']."\" URL=\"mpact.php?op=show_tree&id=".$one."\"];\n";
      $advisors = find_advisors_for_person($one);
      foreach ($advisors as $adv)
      {
        $dotfilecontents .= "$adv -> $one;\n";
      }
    }
    # descendents
    $downgroup = array();
    $downgroup[] = $passed_person;
    $descendents = find_descendents_for_group($downgroup);
    foreach ($descendents as $one)
    {
      $person = find_person($one);
      $dotfilecontents .= "$one [label = \"".$person['fullname']."\" URL=\"mpact.php?op=show_tree&id=".$one."\"";
      if ($one == $passed_person){
        $dotfilecontents .= " color=\"red\" style=\"filled\" fillcolor=\"grey\"";
      }
      $dotfilecontents .= "];\n";
      $advisees = find_advisorships_under_person($one);
      foreach ($advisees as $adv)
      {
        $dotfilecontents .= "$one -> $adv;\n";
      }
    }
    $dotfilecontents .= "}\n";

    return $dotfilecontents;
  }
}

# -------------------------------------------------------------------------------
function draw_tree_dotgraph($passed_person)
{
  $person = $passed_person;
  $host_info = get_environment_info();
  if (isset($host_info['appdir']))
  {
    $webfilename = generate_dotgraph($person);
    if ($webfilename == "marked_as_dirty"){
      echo "generating graph, please reload";
    }
    else{
      echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_graph&id=$person\"><img src=\"$webfilename\" width=\"300\" border=\"0\" alt=\"Directed Graph\" title=\"Click to Enlarge\"></a><br />";
    }
  }
  else
  {
    echo "graphics libraries are not configured";
  }
}

# -------------------------------------------------------------------------------
function generate_dotgraph($passed_person, $forcenew="no")
{
  $person = $passed_person;
  $host_info = get_environment_info();
#  print_r($host_info);
  $appcache = $host_info['appdir']."/".$host_info['dotcachedir'];
  $webcache = $host_info['webdir']."/".$host_info['dotcachedir'];
  $appfilename = "$appcache/$person.".$host_info['dotfiletype'];
#  echo "appfilename = $appfilename<br />\n";
  $dotfilename = "$appcache/$person.dot";
#  echo "dotfilename = $dotfilename<br />\n";
  $webfilename = "$webcache/$person.".$host_info['dotfiletype'];
#  echo "webfilename = $webfilename<br />\n";
  $appimagemap = "$appcache/$person.map";
#  echo "appimagemap = $appimagemap<br />\n";

  if (!file_exists($appfilename)) {
    # assumption is that the cachedir exists... (run setupmpact.sh)
    # generate dotfile
    if (!file_exists($dotfilename) or $forcenew == "force") {
#      print " - creating dotfile...\n";
      $dotfilecontents = generate_dotfile($person);
      $fh = fopen($dotfilename, 'w');
      fwrite($fh, $dotfilecontents);
      fclose($fh);
      exec("chmod 666 $dotfilename");
    }
    # generate graph
    $getandgenerategraph = "/bin/cat $dotfilename | ".$host_info['dotlocation']." -Nfontname=".$host_info['dotfontface']." -Gcharset=latin1 -Tcmapx -o$appimagemap -T".$host_info['dotfiletype']." -o$appfilename 2>&1";
#      echo "getandgenerategraph = $getandgenerategraph<br />";
    exec($getandgenerategraph);
    exec("chmod 666 $appimagemap");
    exec("chmod 666 $appfilename");
    if (!file_exists($appfilename)) {
      # mark as dirty if it didn't work
      mark_record_as_dirty($person);
      return "marked_as_dirty";
    }
  }
  else
  {
#    echo "SHOWING CACHED COPY<br />";
  }

  return $webfilename;
}

# -------------------------------------------------------------------------------
function draw_graph($passed_person)
{
  if (!$passed_person){action_box("No ID given.");}

    $person = $passed_person;
    $host_info = get_environment_info();
    if (isset($host_info['appdir']))
    {
      $appcache = $host_info['appdir']."/".$host_info['dotcachedir'];
      $webcache = $host_info['webdir']."/".$host_info['dotcachedir'];
      $appfilename = "$appcache/$person.".$host_info['dotfiletype'];
      #echo "appfilename = $appfilename<br />";
      $webfilename = "$webcache/$person.".$host_info['dotfiletype'];
      #echo "webfilename = $webfilename<br />";
      $appimagemap = "$appcache/$person.map";
      #echo "appimagemap = $appimagemap<br />";


      echo "<img src=\"$webfilename\" usemap=\"#familytree\" border=\"0\" />";
      echo file_get_contents($appimagemap);
    }
    else
    {
      echo "graphics libraries are not configured";
    }
}

# -------------------------------------------------------------------------------
function draw_tree($passed_person)
{
  global $dbh;
        GLOBAL $statuscodes;
        GLOBAL $inspectioncodes;

        // shows a person

        if (!$passed_person){action_box("No ID given.");}
        else
        {
          $personcount = 0;
          $thisperson = array();
          $dissertation = array();

          // get person's preferred name

          $query = "SELECT
                n.firstname, n.middlename,
                n.lastname, n.suffix, p.degree
                FROM
                  names n, people p
                WHERE
                  p.preferred_name_id = n.id AND
                  p.id = '".$passed_person."'
              ";
          $result = mysqli_query($dbh, $query) or die(mysqli_error($dbh));

          while ( $line = mysqli_fetch_array($result)) {
            $thisperson['firstname']  = $line['firstname'];
            $thisperson['middlename'] = $line['middlename'];
            $thisperson['lastname']   = $line['lastname'];
            $thisperson['suffix']     = $line['suffix'];
            $thisperson['degree']     = $line['degree'];
            $personcount++;
          }

          $schoolinfo = find_persons_school($passed_person);
          $thisperson['dissertation_id'] = $schoolinfo['dissertation_id'];
          $thisperson['completedyear'] = $schoolinfo['completedyear'];
          $thisperson['country'] = $schoolinfo['country'];
          $thisperson['school'] = $schoolinfo['school'];
          $thisperson['schoolid'] = $schoolinfo['schoolid'];

          if ($thisperson['dissertation_id'] == "")
          {
            $thisperson['status'] = "";
            $thisperson['notes'] = "";
            $thisperson['title'] = "N/A";
            $thisperson['abstract'] = "N/A";
            $thisperson['abstract_html'] = "N/A";
          }
          else
          {
            $dissertation = find_dissertation($thisperson['dissertation_id']);
            $thisperson['status'] = $dissertation['status'];
            $thisperson['notes'] = $dissertation['notes'];
            $thisperson['discipline_id'] = $dissertation['discipline_id'];
            $thisperson['title'] = $dissertation['title'];
            $thisperson['abstract'] = $dissertation['abstract'];
            $thisperson['abstract_html'] = encode_linebreaks($dissertation['abstract']);
            if ($thisperson['title'] == "") { $thisperson['title'] = "N/A";}
            if ($thisperson['abstract'] == "")
            {
              $thisperson['abstract'] = "N/A";
              $thisperson['abstract_html'] = "N/A";
            }
          }

          $thisperson['advisors'] = find_advisors_for_person($passed_person);
          $thisperson['cmembers'] = find_committee_for_person($passed_person);
          $thisperson['advisorships'] = find_advisorships_under_person($passed_person);
          $thisperson['committeeships'] = find_committeeships_under_person($passed_person);


          if ($personcount < 1)
          {
            action_box("Person ".$passed_person." Not Found");
          }
          else
          {

            echo "<table><tr><td>\n";

            # Name / Aliases
            $count = 0;
            $printme = "";
            $fullname = $thisperson['firstname']." ".$thisperson['middlename']." ".$thisperson['lastname']." ".$thisperson['suffix'];
            $printme .= "<h3>Dissertation Information for $fullname</h3>";
            $printme .= "<p>";
            if (is_admin())
            {
              if (isset($thisperson['completedyear']))
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_dissertation&id=".$thisperson['dissertation_id']."\">Edit</a>)";
              }
              else
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_dissertation&person_id=".$passed_person."\">Create Dissertation for $fullname</a>)";
              }
              $printme .= "<br />\n";
            }
            $printme .= "</p>";
            $printme .= "<p>";
            $printme .= "NAME: ";
            if (is_admin())
            {
              $printme .= "(<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_name&id=".$passed_person."\">Add</a>)";
            }
            $printme .= "<br />";
            $printme .= " - ";
            $printme .= get_person_link($passed_person);
            if (is_admin())
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_name&id=$passed_person\">Edit</a>)";
            }
            $printme .= "<br />\n";

            $aliases = 0;
            foreach (find_aliases($passed_person) as $one)
            {
              $printme .= " - (Alias) ";
              $printme .= $one['firstname']." ";
              $printme .= $one['middlename']." ";
              $printme .= $one['lastname']." ";
              $printme .= $one['suffix']." ";
              if (is_admin())
              {
                $printme .= "(<a href=\"".$_SERVER['SCRIPT_NAME']."?op=set_preferred_name&id=".$passed_person."&name=".$one['id']."\">Set as Primary</a>)";
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=delete_name&id=".$passed_person."&name=".$one['id']."\">Delete</a>)";
              }
              $printme .= "<br />";
              $aliases++;
            }
            $printme .=  "</p>";
            echo $printme;

            # Degree
            $printme = "<p>\n";
            $printme .= "DEGREE:<br />\n";
            $printme .= " - ".$thisperson['degree'];
            if (is_admin())
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_degree&id=$passed_person\">Edit</a>)";
            }
            $printme .=  "</p>";
            echo $printme;

            # Discipline
            $printme = "<p>\n";
            $printme .= "DISCIPLINE:<br />\n";
            if (isset($thisperson['discipline_id']))
            {
              $discipline = find_discipline($thisperson['discipline_id']);
              $printme .= " - <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_discipline&id=".$thisperson['discipline_id']."\">".$discipline['title']."</a>";
              if (is_admin())
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_dissertation&id=".$thisperson['dissertation_id']."\">Edit</a>)";
              }
            }
            else
            {
              $printme .= " - None";
              if (is_admin())
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_dissertation&person_id=".$passed_person."\">Create Dissertation for $fullname</a>)";
              }
            }
            $printme .=  "</p>";
            echo $printme;

            # School
            $printme = "<p>\n";
            $printme .= "SCHOOL:<br />\n";
            if (isset($thisperson['completedyear']))
            {
              $printme .= " - <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_school&id=".$thisperson['schoolid']."\">".$thisperson['school']."</a> (".$thisperson['country'].") (".$thisperson['completedyear'].")";
              if (is_admin())
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_dissertation&id=".$thisperson['dissertation_id']."\">Edit</a>)";
              }
            }
            else
            {
              $printme .= " - None";
              if (is_admin())
              {
                $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_dissertation&person_id=".$passed_person."\">Create Dissertation for $fullname</a>)";
              }
            }
            $printme .= "</p>\n";
            echo $printme;


            # Advisors
            $count = 0;
            $printme = "";
            $printme .= "<p>";
            $printme .=  "ADVISORS: ";
            if (is_admin() && isset($thisperson['completedyear']))
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_mentor&id=$passed_person&type=A\">Add</a>)";
            }
            $printme .= "<br />";
            if (isset($thisperson['advisors'])){
              foreach ($thisperson['advisors'] as $one)
              {
                $printme .=  " - ";
                $printme .=  get_person_link($one);
                if (is_admin())
                {
                  $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=remove_mentor&student_id=$passed_person&mentor_id=$one&type=A\">Remove</a>)";
                }
                $printme .=  "<br />";
                $count++;
              }
            }
            if ($count < 1){$printme .= " - None";}
            $printme .=  "</p>";
            echo $printme;

            # Committee Members
            $count = 0;
            $printme = "";
            $printme .= "<p>";
            $printme .= "COMMITTEE MEMBERS: ";
            if (is_admin() && isset($thisperson['completedyear']))
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_mentor&id=$passed_person&type=C\">Add</a>)";
            }
            $printme .= "<br />";
            if (isset($thisperson['cmembers'])){
              foreach ($thisperson['cmembers'] as $one)
              {
                $printme .= " - ";
                $printme .= get_person_link($one);
                if (is_admin())
                {
                  $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=remove_mentor&student_id=$passed_person&mentor_id=$one&type=C\">Remove</a>)";
                }
                $printme .= "<br />";
                $count++;
              }
            }
            if ($count < 1){$printme .= " - None";}
            $printme .=  "</p>\n\n";

            # Admin Notes
            if (is_admin())
            {
              if ($thisperson['notes'] != "")
              {
                $printme .=  "<p>";
                $printme .= "<table border=\"1\">\n";
                $printme .= "<tr><td bgcolor=\"#FF8888\">\n";
                $printme .= "<strong>Admin Notes:</strong> ".$thisperson['notes']."<br />\n";
                $printme .= "</tdr></tr>\n";
                $printme .= "</table>\n";
                $printme .=  "</p>\n\n";
              }
            }

            $glossaryterms = find_glossaryterms();
            # Status and Inspection
            $printme .=  "<p>";
            $printme .= "<strong><a href=\"".$_SERVER['SCRIPT_NAME']."?op=glossary\" title='".$glossaryterms['defs'][$glossaryterms['ids']['status']]."'>MPACT Status</a>:</strong> ".$statuscodes[$thisperson['status']]."<br />\n";
            $printme .=  "</p>\n\n";

            # Title and Abstract
            $printme .=  "<p>";
            $printme .= "<strong>Title:</strong> ".$thisperson['title']."<br />";
            $printme .=  "</p>\n";
            $printme .=  "<p>";
            $printme .= "<strong>Abstract:</strong> ".$thisperson['abstract_html']."<br />";
            $printme .=  "</p>\n\n";

            # print it all out...
            echo $printme;

            # URLS

            if (is_admin())
            {
              $urls = find_urls_by_person($passed_person);
              echo "<a name=\"urls\"><p>\n";
              echo "REFERENCE URLS";
              echo " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_url&id=$passed_person\">Add</a>)";
              echo "<br />\n";
              if (count($urls) > 0)
              {
                echo "<table border='1'>\n";
              }
              else
              {
                echo " - None\n";
              }
              foreach ($urls as $one)
              {
                echo "<tr><td><strong>Updated: ".$one['updated_at']."</strong>";
                echo " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_url&id=".$one['id']."\">Edit</a>)";
                echo " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=delete_url&id=".$one['id']."\">Delete</a>)";
                echo "</td></tr>\n";
                echo "<tr><td><a href=\"".$one['url']."\">".$one['url']."</a></td></tr>\n";
                echo "<tr><td>".$one['description']."</td></tr>\n";
              }
              if (count($urls) > 0)
              {
                echo "</table>\n";
              }
              echo "</p>\n";
            }

            # EDIT HISTORY

            if (is_admin())
            {
              $entries = get_dissertation_history($thisperson['dissertation_id']);
              if (count($entries) > 0)
              {
                echo "<a name=\"history\"><p>\n";
                echo "EDIT HISTORY (<a href=\"#history\" onclick=\"javascript:ToggleVisible('edithistory');\">Show/Hide</a>)\n";
                echo "<span id=\"edithistory\" style=\"display:none;\">";
                echo "<table border='1'>\n";
              }

              foreach ($entries as $one)
              {
                echo "<tr>";
                echo "<td width='140'>".$one['logged_at']."</td>";
                echo "<td>".$one['user']."</td>";
                echo "<td>".$one['message']."</td>";
                echo "</tr>\n";
              }
              if (count($entries) > 0)
              {
                echo "</table></span>\n";
                echo "</p>\n";
              }
            }



            echo "</td><td width=\"40\"></td><td valign=\"top\">\n";

            # MPACT Scores
            $printme = "";
            $printme .=  "<h3>MPACT Scores for $fullname</h3>";
            $printme .=  "<p>";
            $mpact = mpact_scores($passed_person);
            $printme .= $mpact['output'];
            $printme .=  "</p>\n";
            if (is_admin())
            {
              $printme .=  "<p>(<a href=\"".$_SERVER['SCRIPT_NAME']."?op=recalculate_mpact&id=$passed_person\">Recalculate</a>)</p>";
            }
            echo $printme;

            # Draw FamilyTree Graph for this person
            echo "<h3>Advisors and Advisees Graph</h3>\n";
            echo "<p>";
            draw_tree_dotgraph($passed_person);
            echo "</p>";

            echo "</td></tr></table>\n";


            # Students
            echo  "<h3>Students under $fullname</h3>\n\n";

            # Advisees
            $count = 0;
            $printme = "";
            $printme .= "<p>";
            $printme .= "ADVISEES: ";
            if (is_admin())
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_student&id=$passed_person&type=A\">Add</a>)";
            }
            $printme .= "<br />";
            if (isset($thisperson['advisorships'])){
              foreach ($thisperson['advisorships'] as $one)
              {
                $printme .= " - ";
                $printme .= get_person_link($one);
                $schoolinfo = find_persons_school($one);
                if (isset($schoolinfo['completedyear']))
                {
                  $printme .= " - <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_school&id=".$schoolinfo['schoolid']."\">".$schoolinfo['school']."</a> (".$schoolinfo['completedyear'].")";
                }
                if (is_admin())
                {
                  $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=remove_mentor&student_id=$one&mentor_id=$passed_person&type=A\">Remove</a>)";
                }
                $printme .= "<br />";
                $count++;
              }
            }
            if ($count < 1){$printme .= " - None";}
            $printme .=  "</p>\n";
            echo $printme;

            # Committeeships
            $ccount = 0;
            $printme = "";
            $printme .= "<p>";
            $printme .= "COMMITTEESHIPS: ";
            if (is_admin())
            {
              $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=add_student&id=$passed_person&type=C\">Add</a>)";
            }
            $printme .= "<br />";
            if (isset($thisperson['committeeships'])){
              foreach ($thisperson['committeeships'] as $one)
              {
                $printme .= " - ";
                $printme .= get_person_link($one);
                $schoolinfo = find_persons_school($one);
                if (isset($schoolinfo['completedyear']))
                {
                  $printme .= " - <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_school&id=".$schoolinfo['schoolid']."\">".$schoolinfo['school']."</a> (".$schoolinfo['completedyear'].")";
                }
                if (is_admin())
                {
                  $printme .= " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=remove_mentor&student_id=$one&mentor_id=$passed_person&type=C\">Remove</a>)";
                }
                $printme .= "<br />";
                $count++;
              }
            }
            if ($count < 1){$printme .= " - None";}
            $printme .=  "</p>";
            echo $printme;

          }

        }


}


?>
