<?php

/*************************************************

//  MPACT - Mentoring Impact Factor

//  Terrell Russell
//  2005 - 2010
//  unc@terrellrussell.com

*************************************************/

/*

splitting a person
 - give current list of A's and C's and names
 - allow A's and C's to be divided (all have to be moved to #1 or #2)
 - names need to be split, but can be in both (since that's the problem in the first place)

live search for entering new links
 - 2 characters and then hit DB

incoming suggestions/changes
 - just a big bucket
 - contact info and single big box to state their claim
 - should be the start of a conversation, we're not just taking their word
 - batch submissions?  just start a conversation

*/

require 'envsetup.php';

###############################################

$statuscodes = array(
  NULL => "N/A",
  "-1" => "N/A",
  "0"  => "Incomplete - Not_Inspected",
  "1"  => "Incomplete - Inspected",
  "2"  => "Incomplete - Ambiguous",
  "3"  => "Complete - Except Indecipherables",
  "4"  => "Fully Complete"
);

$inspectioncodes = array(
  NULL => "N/A",
  "0" => "No",
  "1" => "Yes"
);

$degree_types = array(
  "Unknown",
  "Unknown - Investigated",
  "A.L.A.",
  "B.A.",
  "B.S.",
  "B.L.S.",
  "L.L.B.",
  "H.A.",
  "M.A.",
  "M.S.",
  "M.F.A.",
  "M.P.A.",
  "D.E.A.",
  "Ed.M.",
  "F.L.A. (by thesis)",
  "LL.M.",
  "LL.D.",
  "M.L.S.",
  "D.S.N.",
  "D.S.W.",
  "D.Engr.",
  "D.Lib.",
  "Ed.D.",
  "Th.D.",
  "Pharm.D.",
  "D.Sc.",
  "D.L.S.",
  "J.D.",
  "M.D.",
  "Ph.D."
);

// Begin Session
session_start();

// Delete server session if client cookie doesn't exist
if (!isset($_COOKIE['MPACT_userid'])){
  unset($_SESSION['MPACT']);
}

// Check for good cookie (and expired session) - (re)set session values accordingly
if (isset($_COOKIE['MPACT_userid']) && !isset($_SESSION['MPACT'])){
  $query = "SELECT id, username, fullname FROM users WHERE id='".$_COOKIE['MPACT_userid']."'";
  $line = $dbh->querySingle($query, true);
  $_SESSION['MPACT']['userid'] = $line['id'];
  $_SESSION['MPACT']['username'] = $line['username'];
  $_SESSION['MPACT']['fullname'] = $line['fullname'];
}

###############################################
// DISPLAY SOMETHING - something from the URL
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // Display header
  xhtml_web_header();

//  echo "-----\n";
//  print_r($_COOKIE);
//  echo "-----\n";
//  print_r($_SESSION);
//  echo "-----\n";
  if (isset($_GET['op']))
  {
    switch ($_GET['op'])
    {
      ###############################################
      case "login";

        $host_info = get_environment_info();
        if ($host_info['hostname'] == "sils"){
          # no logins here - go to dev server
          action_box("No Logins Here...",2,$_SERVER['SCRIPT_NAME']);
        }
        else{
          if (!isset($_SESSION['MPACT']['userid'])){
            # login form
            echo "<h1>Welcome</h1>\n";
            echo "<p>Please login if you have an account.</p>\n";
            echo "<form id=\"loginform\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
            echo "<input type=\"hidden\" name=\"op\" value=\"login\">\n";
            echo "<table border=\"0\">\n";
            echo "<tr><td><p>Username:</p></td><td><input type='text' name='username' /></td></tr>\n";
            echo "<tr><td><p>Password:</p></td><td><input type='password' name='password' /></td></tr>\n";
            echo "<tr><td colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='Submit' value='Login' /></td></tr>\n";
            echo "</table>\n";
            echo "</form>";
          }
          else{
            # um, already logged in...
            action_box("Already Logged In...",2,$_SERVER['SCRIPT_NAME']);
          }
        }

        break;
      ###############################################
      case "logout";

        setcookie('MPACT_userid',$_SESSION['MPACT']['userid'],time()-1000);
        unset($_SESSION['MPACT']);
        action_box("Logged Out",2,$_SERVER['SCRIPT_NAME']);

        break;
      ###############################################
      case "glossary";

        if ( isset($_GET['id']) ){
          show_glossaryterm($_GET['id']);
        }
        else
        {
          show_glossary();
        }

        break;
      ###############################################
      case "admin";

        if (is_admin())
        {
          echo "<h3>Administrator Pages</h3>\n";

          echo "<table border='0' width='90%'>\n";
          echo "<tr><td>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_logs\">Show Logs</a>\n";
          echo "</p>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_orphans\">Show Orphans</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=citation_correlation\">Citation Correlation</a>\n";
          echo "</p>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_multi_dissertations\">People with Multiple Dissertations (Errors in DB)</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_zero_year\">Year 0000</a>\n";
          echo "</p>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_person\">Add a New Person to the Database</a>\n";
          echo "</p>\n";

          echo "</td><td>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_no_title_abstract\">No Title/Abstract</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_no_title_abstract_lis\">LIS, No Title/Abstract</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_no_title_lis\">LIS, No Title</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=find_no_abstract_lis\">LIS, No Abstract</a>\n";
          echo "<br />\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_nocommittee\">LIS, No Committee</a>\n";
          echo "</p>\n";

          echo "</td><td>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_allyears\">LIS, Graduates By Year</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_history\">LIS History</a>\n";
          echo "</p>\n";

          echo "<p>\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_summary\">LIS Professors Summary</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_with_mpact\">LIS Professors with MPACT</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_degrees\">LIS Professors and their Degrees (large CSV)</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_unknown_degree\">LIS Professors with Unknown Degree</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_unknowninvestigated_degree\">LIS Professors with Unknown-Investigated Degree</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profswithoutdiss\">LIS Professors without Dissertations</a>\n";
          echo "<br />\n";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profsnotfromlis\">LIS Professors without LIS Dissertations</a>\n";
          echo "</p>\n";

          echo "</td></tr>\n";
          echo "</table>\n";

          echo "<br />";

          show_alphabet();

        }
        else
        {
          not_admin();
        }

        break;
      ###############################################
      case "glossary_edit";

        if ( isset($_GET['id']) && is_admin() ){

          echo "<h3>Edit a Glossary Definition</h3>\n";

          $query = "SELECT id, term, definition FROM glossary WHERE id='".$_GET['id']."'";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $results['term'] = $line['term'];
            $results['definition'] = $line['definition'];
          }

          echo "<p>";

          echo "<form id=\"glossaryedit\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"glossary_edit\">\n";
          echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">\n";
          echo "<input type=\"hidden\" name=\"term\" value=\"".$results['term']."\">\n";

          echo "<strong>".$results['term']."</strong>:<br />\n";
          echo "<textarea cols=\"50\" rows=\"8\" name=\"definition\">".$results['definition']."</textarea>\n";
          echo "<br />\n";
          echo "<input type=\"submit\" name=\"button\" value=\"Save\">";
          echo "</form>";

          echo "</p>";


        }
        else
        {
          not_admin();
        }

        break;
      ###############################################
      case "show_tree":

        if (!$_GET['id']){action_box("No ID given.");}
        else
        {
          draw_tree((int)$_GET['id']);
        }

        break;

      ###############################################
      case "show_graph":

        if (!$_GET['id']){action_box("No ID given.");}
        else
        {
          echo "<br /><br />\n";
          echo "Advisors and Advisees of ".get_person_link($_GET['id']);
          echo "<br /><br />\n";
          draw_graph((int)$_GET['id']);
        }

        break;

      ###############################################
      case "statistics":

        echo "<p>\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_incomplete\">LIS Incomplete</a> - \n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_a\">Top A</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_c\">Top C</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_ac\">Top A+C</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_t\">Top T</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_g\">Top G</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_w\">Top W</a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_td\">Top T<sub>D</sub></a> -\n";
        echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=top_ta\">Top T<sub>A</sub></a>\n";
        echo "</p>\n";

        echo "<h3>Overall MPACT Statistics</h3>\n";

        echo "<table border='1'>\n";

        # disciplines
        $query = "SELECT count(*) as disciplinecount FROM disciplines";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>Disciplines</td><td align=\"right\">".$disciplinecount."</td></tr>\n";

        # schools
        $query = "SELECT count(*) as schoolcount FROM schools";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>Schools</td><td align=\"right\">".$schoolcount."</td></tr>\n";

        # diss
        $query = "SELECT count(*) as disscount FROM dissertations";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>Dissertations</td><td align=\"right\">".$disscount."</td></tr>\n";

        # a
        $query = "SELECT count(*) as advisorcount FROM advisorships";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>Advisorships</td><td align=\"right\">".$advisorcount."</td></tr>\n";

        # c
        $query = "SELECT count(*) as committeeshipscount FROM committeeships";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>Committeeships</td><td align=\"right\">".$committeeshipscount."</td></tr>\n";

        # full
        $query = "SELECT count(*) as peoplecount FROM people";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<tr><td>People</td><td align=\"right\">".$peoplecount."</td></tr>\n";
        echo "</table>\n";


        # dissertations by country
        echo "<br /><br />\n";
        echo "<p><b>Dissertations by Country:</b></p><br />\n";
        $query = "SELECT count(*) as dissnone FROM dissertations WHERE school_id IN (SELECT id FROM schools WHERE country = \"\")";
        $line = $dbh->querySingle($query, true);
        extract($line);
        $query = "SELECT count(*) as dissusa FROM dissertations WHERE school_id IN (SELECT id FROM schools WHERE country = \"USA\")";
        $line = $dbh->querySingle($query, true);
        extract($line);
        $query = "SELECT count(*) as disscanada FROM dissertations WHERE school_id IN (SELECT id FROM schools WHERE country = \"Canada\")";
        $line = $dbh->querySingle($query, true);
        extract($line);
        $query = "SELECT count(*) as dissother FROM dissertations WHERE school_id IN
          (SELECT id FROM schools WHERE (country != \"\" AND country != \"USA\" AND country != \"Canada\"))";
        $line = $dbh->querySingle($query, true);
        extract($line);
        echo "<table border='1'>\n";
        echo "<tr><td>USA</td><td align=\"right\">".$dissusa."</td>
                  <td align=\"right\">".sprintf("%.2f",100*$dissusa/$disscount)."%</td>
                  <td align=\"right\">".sprintf("%.2f",100*($dissusa)/$disscount)."%</td></tr>\n";
        echo "<tr><td>Canada</td><td align=\"right\">".$disscanada."</td>
                  <td align=\"right\">".sprintf("%.2f",100*$disscanada/$disscount)."%</td>
                  <td align=\"right\">".sprintf("%.2f",100*($dissusa+$disscanada)/$disscount)."%</td></tr>\n";
        echo "<tr><td>Other</td><td align=\"right\">".$dissother."</td>
                  <td align=\"right\">".sprintf("%.2f",100*$dissother/$disscount)."%</td>
                  <td align=\"right\">".sprintf("%.2f",100*($dissusa+$disscanada+$dissother)/$disscount)."%</td></tr>\n";
        echo "<tr><td>None Listed</td><td align=\"right\">".$dissnone."</td>
                  <td align=\"right\">".sprintf("%.2f",100*$dissnone/$disscount)."%</td>
                  <td align=\"right\">".sprintf("%.2f",100*($dissusa+$disscanada+$dissother+$dissnone)/$disscount)."%</td></tr>\n";
        echo "<tr><td>Total</td><td align=\"right\">".$disscount."</td>
                  <td align=\"right\">".sprintf("%.2f",100*$disscount/$disscount)."%</td>
                  <td></td></tr>\n";
        echo "</table>\n";

        # dissertations by year
        echo "<br /><br />\n";
        echo "<p><b>Dissertations by Year:</b></p><br />\n";
        $query = "SELECT completedyear, count(*) as disscount FROM dissertations GROUP BY completedyear";
        $results = $dbh->query($query);
        while ( $line = $results->fetchArray() ) {
          $counts[$line['completedyear']] = $line['disscount'];
        }
        ksort($counts);
        echo "<table cellpadding=1 cellspacing=0>\n";
        echo "<tr>\n";
        foreach ($counts as $one => $two)
        {
          echo "<td valign='bottom'><img src='images/blue.gif' height='".$two."' width='8' title='".$two." in ".$one."'></td>\n";
        }
        echo "</tr>";
        echo "<tr>\n";
        foreach ($counts as $one => $two)
        {
          echo "<td>";
          if ($one%10==0)
          {
            echo $one;
          }
          echo "</td>\n";
        }
        echo "</tr>";
        echo "</table>\n";

        break;


      ###############################################
      case "show_logs":

        if (is_admin())
        {
          echo "<h3>Recent Activity</h3>\n";

          if (isset($_GET['offset']))
          {
            $entries = get_logs($_GET['offset']);
          }
          else
          {
            $entries = get_logs();
          }
          echo "<table border=\"1\">\n";
          foreach ($entries as $entry)
          {
            echo "<tr>";
            echo "<td width=\"150\">".$entry['logged_at']."<br />".$entry['ip']."</td>";
            echo "<td>".$entry['user']."</td>";
            echo "<td>".$entry['message']."</td>";
            echo "</tr>\n";
          }
          echo "</table>\n";

        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_multi_dissertations":

        if (is_admin())
        {
          echo "<h3>People With Multiple Dissertations (db needs to be edited by hand)</h3>\n";

          $query = "SELECT
                count(d.id) as howmany,
                d.id, d.person_id, d.completedyear, d.status,
                d.title, d.abstract, d.notes, d.school_id, d.discipline_id
                FROM
                  dissertations d
                GROUP BY
                  d.person_id
                ORDER BY
                  howmany DESC
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $multicount = 0;
          echo "<p>\n";
          foreach ($dissertations as $d)
          {
            if ($d['howmany'] > 1)
            {
              print "person id = ".$d['person_id'];
              print "<br />";
              print "<br />";
              $multicount++;
            }
          }
          if ($multicount == 0)
          {
            print " - None - database is clear.";
          }
          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_zero_year":

        if (is_admin())
        {
          echo "<h3>Dissertations from the year 0000</h3>\n";

          $query = "SELECT
                d.id, d.person_id, d.school_id, d.discipline_id, d.completedyear
                FROM
                  dissertations d
                WHERE
                  d.completedyear = '0000'
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $zerocount = 0;
          echo "<table>";
          foreach ($dissertations as $d)
          {
            $zerocount++;
            # get school
            $query = "SELECT fullname
                        FROM
                          schools
                        WHERE id = '".$d['school_id']."'
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $schoolname = $line['fullname'];
            }
            # get degree
            $query = "SELECT degree
                        FROM
                          people
                        WHERE id = '".$d['person_id']."'
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $degree = $line['degree'];
            }
            $discipline = find_discipline($d['discipline_id']);
            echo "<tr>";
            echo "<td>$zerocount.</td>";
            echo "<td>".get_person_link($d['person_id'])."</td>";
            print "<td>$degree</td>";
            print "<td>".$discipline['title']."</td>";
            print "<td>$schoolname</td>";
            print "</tr>";
          }
          if ($zerocount == 0)
          {
            print "<tr><td>None - database is clear.</td></tr>";
          }
          echo "</table>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_no_title_lis":

        if (is_admin())
        {
          echo "<h3>LIS Dissertations Without Title</h3>\n";

          $query = "SELECT
                d.id, d.person_id, d.school_id, d.completedyear
                FROM
                  dissertations d
                WHERE
                  d.discipline_id = 1 AND
                  ( d.title = '' )
                ORDER BY
                  d.school_id
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $zerocount = 0;
          echo "<p>\n";
          foreach ($dissertations as $d)
          {
            $zerocount++;
            print "$zerocount. ";
            echo get_person_link($d['person_id']);
            $thisperson = find_persons_school($d['person_id']);
            print " - ".$thisperson['school'];
            print " (".$d['completedyear'].")";
            print "<br />";
          }
          if ($zerocount == 0)
          {
            print " - None - All LIS dissertations have titles.";
          }
          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_no_abstract_lis":

        if (is_admin())
        {
          echo "<h3>LIS Dissertations Without Abstract</h3>\n";

          $query = "SELECT
                d.id, d.person_id, d.school_id, d.completedyear
                FROM
                  dissertations d
                WHERE
                  d.discipline_id = 1 AND
                  ( d.abstract = '' )
                ORDER BY
                  d.school_id
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $zerocount = 0;
          echo "<p>\n";
          foreach ($dissertations as $d)
          {
            $zerocount++;
            print "$zerocount. ";
            echo get_person_link($d['person_id']);
            $thisperson = find_persons_school($d['person_id']);
            print " - ".$thisperson['school'];
            print " (".$d['completedyear'].")";
            print "<br />";
          }
          if ($zerocount == 0)
          {
            print " - None - All LIS dissertations have abstracts.";
          }
          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_no_title_abstract":

        if (is_admin())
        {
          echo "<h3>Dissertations Without Title/Abstract</h3>\n";

          $query = "SELECT
                d.id, d.person_id, d.school_id, d.completedyear
                FROM
                  dissertations d
                WHERE
                  d.title = '' OR d.abstract = ''
                ORDER BY
                  d.school_id
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $zerocount = 0;
          echo "<p>\n";
          foreach ($dissertations as $d)
          {
            $zerocount++;
            print "$zerocount. ";
            echo get_person_link($d['person_id']);
            $thisperson = find_persons_school($d['person_id']);
            print " - ".$thisperson['school'];
            print " (".$d['completedyear'].")";
            print "<br />";
          }
          if ($zerocount == 0)
          {
            print " - None - All dissertations have titles and abstracts.";
          }
          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "find_no_title_abstract_lis":

        if (is_admin())
        {
          echo "<h3>LIS Dissertations Without Title/Abstract</h3>\n";

          $query = "SELECT
                d.id, d.person_id, d.school_id, d.completedyear
                FROM
                  dissertations d
                WHERE
                  d.discipline_id = 1 AND
                  ( d.title = '' OR d.abstract = '' )
                ORDER BY
                  d.school_id
              ";
          $dissertations = array();
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $dissertations[] = $line;
          }
          $zerocount = 0;
          echo "<p>\n";
          foreach ($dissertations as $d)
          {
            $zerocount++;
            print "$zerocount. ";
            echo get_person_link($d['person_id']);
            $thisperson = find_persons_school($d['person_id']);
            print " - ".$thisperson['school'];
            print " (".$d['completedyear'].")";
            print "<br />";
          }
          if ($zerocount == 0)
          {
            print " - None - All LIS dissertations have titles and abstracts.";
          }
          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "lis_profs_unknown_degree":

        if (is_admin())
        {
          echo "<h3>LIS Professors with Unknown Degree</h3>\n";

          # advisors or committee members with unknown degree,
          # that served on an lis dissertation

          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }

          $lis_with_unknown = array();

          # get advisors for each diss, check for advisor's degree
          # if unknown degree, save
          foreach ($lis_dissertations as $id){
            $advisors = array();
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $advisors[] = $line['person_id'];
            }
            foreach($advisors as $aid){
              $adv = find_person($aid);
              if ($adv['degree'] == "Unknown"){
                $lis_with_unknown[] = $aid;
              }
            }
          }

          # get committeeships for each diss, check for comm's degree
          # if unknown degree, save
          foreach ($lis_dissertations as $id){
            $committeemembers = array();
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $committeemembers[] = $line['person_id'];
            }
            foreach($committeemembers as $cid){
              $com = find_person($cid);
              if ($com['degree'] == "Unknown"){
                $lis_with_unknown[] = $cid;
              }
            }
          }

          # uniquify
          $lis_with_unknown = array_unique($lis_with_unknown);

          # print them out
          echo "<p>\n";
          $count = 0;
          foreach($lis_with_unknown as $pid){
            $count++;
            $person = find_person($pid);
            print "$count. ";
            print get_person_link($pid);
            print "<br />\n";
          }
          if ($count == 0)
          {
            print " - None";
          }
          echo "</p>\n";

        }
        else
        {
          not_admin();
        }

        break;

        ###############################################
        case "lis_profs_unknowninvestigated":
        case "lis_profs_unknowninvestigated_degree":

#        if (is_admin())
#        {
          echo "<h3>LIS Professors with Unknown-Investigated Degree</h3>\n";

          # advisors or committee members with unknown-investigated degree,
          # that served on an lis dissertation

          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }

          $lis_with_unknown = array();

          # get advisors for each diss, check for advisor's degree
          # if unknown degree, save
          foreach ($lis_dissertations as $id){
            $advisors = array();
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $advisors[] = $line['person_id'];
            }
            foreach($advisors as $aid){
              $adv = find_person($aid);
              if ($adv['degree'] == "Unknown - Investigated"){
                $lis_with_unknown[] = $aid;
              }
            }
          }

          # get committeeships for each diss, check for comm's degree
          # if unknown degree, save
          foreach ($lis_dissertations as $id){
            $committeemembers = array();
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $committeemembers[] = $line['person_id'];
            }
            foreach($committeemembers as $cid){
              $com = find_person($cid);
              if ($com['degree'] == "Unknown - Investigated"){
                $lis_with_unknown[] = $cid;
              }
            }
          }

          # uniquify
          $lis_with_unknown = array_unique($lis_with_unknown);

          # print them out
          echo "<p>\n";
          $count = 0;
          foreach($lis_with_unknown as $pid){
            $count++;
            $person = find_person($pid);
            print "$count. ";
            print get_person_link($pid);
            $advisorcount = count(find_advisorships_under_person($pid));
            $commcount = count(find_committeeships_under_person($pid));
            $totalcommitteeships = $advisorcount + $commcount;
            if ($totalcommitteeships > 1){print "<span style='color:red'><strong>";}
            print " : $advisorcount + $commcount = $totalcommitteeships";
            if ($totalcommitteeships > 1){print "</strong></span>";}
            print "<br />\n";
            $allcommitteeships += $totalcommitteeships;
          }
          if ($count == 0)
          {
            print " - None";
          }

          print "<br />TOTAL COMMITTEESHIPS = $allcommitteeships<br />";

          echo "</p>\n";

#        }
#        else
#        {
#          not_admin();
#        }

        break;


        ###############################################
        case "lis_profswithoutdiss":

        if (is_admin())
        {
          echo "<h3>LIS Professors without Dissertations</h3>\n";

          # advisors or committee members with no dissertation,
          # that served on an lis dissertation

          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }
          # get advisors for each diss
          $advisors = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $advisors[] = $line['person_id'];
            }
          }
          # get committeeships for each diss
          $committeemembers = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $committeemembers[] = $line['person_id'];
            }
          }
          $total = array_merge($advisors,$committeemembers);
          $unique = array_unique($total);

          $unique_list = implode(",",$unique);
          $listed = array();
          $query = "SELECT person_id
                      FROM dissertations
                      WHERE
                        person_id IN ($unique_list)
                    ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $listed[] = $line['person_id'];
          }
          $notlisted = array_diff($unique,$listed);

          # print them out
          echo "<p>\n";
          $count = 0;
          foreach($notlisted as $pid){
            $count++;
            print "$count. ";
            print get_person_link($pid);
            print "<br />\n";
          }
          if ($count == 0)
          {
            print " - None";
          }
          echo "</p>\n";

        }
        else
        {
          not_admin();
        }

        break;

        ###############################################
        case "lis_profs_degrees":

        if (is_admin())
        {
          echo "<h3>LIS Professors and their Degrees</h3>\n";

          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }

          print "<p>Copy and Paste the text below into a .csv file.</p>";

          # big loop
          print "<pre style=\"background-color:#ddd\">\n";
          print "<br />";
          print "Count,DissID,DissYear,DissLastName,DissFirstName,DissSchool,DissCountry,AdvisorID,AdvisorType,AdvisorLastName,AdvisorFirstName,";
          print "AdvisorDegree,AdvisorYear,AdvisorDiscipline,AdvisorSchool,AdvisorCountry\n";
          $count = 0;
          foreach($lis_dissertations as $did){
            # loop through advisors
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $did
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {

              # advisor line

                # Count
                $count++;
                print "\"$count\",";
                # DissID
                print "\"$did\",";
                # DissYear
                $diss = find_dissertation($did);
                print "\"".$diss['completedyear']."\",";
                # DissLastName
                $author = find_person($diss['person_id']);
                print "\"".$author['lastname']."\",";
                # DissFirstName
                print "\"".$author['firstname']."\",";
                # DissSchool
                $school = find_school($diss['school_id']);
                print "\"".$school['fullname']."\",";
                # DissCountry
                print "\"".$school['country']."\",";
                # AdvisorID
                $pid = $line['person_id'];
                print "\"$pid\",";
                # AdvisorType
                print "\"Advisor\",";
                # AdvisorLastName
                $person = find_person($pid);
                print "\"".$person['lastname']."\",";
                # AdvisorFirstName
                print "\"".$person['firstname']."\",";
                # AdvisorDegree
                print "\"".$person['degree']."\",";
                $diss = find_dissertation_by_person($pid);
                if ($diss){
                  # AdvisorYear
                  print "\"".$diss['completedyear']."\",";
                  # AdvisorDiscipline
                  $disc = find_discipline($diss['discipline_id']);
                  print "\"".$disc['title']."\",";
                  # AdvisorSchool
                  $school = find_school($diss['school_id']);
                  print "\"".$school['fullname']."\",";
                  # AdvisorCountry
                  print "\"".$school['country']."\"";
                }
                else{
                  # no dissertation for this person
                  print "\"\",";
                  print "\"\",";
                  print "\"\",";
                  print "\"\"";
                }
                print "\n";

            }
            # loop through committeeships
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $did
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {

              # committeeship line

                # Count
                $count++;
                print "\"$count\",";
                # DissID
                print "\"$did\",";
                # DissYear
                $diss = find_dissertation($did);
                print "\"".$diss['completedyear']."\",";
                # DissLastName
                $author = find_person($diss['person_id']);
                print "\"".$author['lastname']."\",";
                # DissFirstName
                print "\"".$author['firstname']."\",";
                # DissSchool
                $school = find_school($diss['school_id']);
                print "\"".$school['fullname']."\",";
                # DissCountry
                print "\"".$school['country']."\",";
                # AdvisorID
                $pid = $line['person_id'];
                print "\"$pid\",";
                # AdvisorType
                print "\"Committee\",";
                # AdvisorLastName
                $person = find_person($pid);
                print "\"".$person['lastname']."\",";
                # AdvisorFirstName
                print "\"".$person['firstname']."\",";
                # AdvisorDegree
                print "\"".$person['degree']."\",";
                $diss = find_dissertation_by_person($pid);
                if ($diss){
                  # AdvisorYear
                  print "\"".$diss['completedyear']."\",";
                  # AdvisorDiscipline
                  $disc = find_discipline($diss['discipline_id']);
                  print "\"".$disc['title']."\",";
                  # AdvisorSchool
                  $school = find_school($diss['school_id']);
                  print "\"".$school['fullname']."\",";
                  # AdvisorCountry
                  print "\"".$school['country']."\"";
                }
                else{
                  # no dissertation for this person
                  print "\"\",";
                  print "\"\",";
                  print "\"\",";
                  print "\"\"";
                }
                print "\n";

            }
          }
          echo "</pre>\n";

        }
        else
        {
          not_admin();
        }

        break;

        ###############################################
        case "lis_profsnotfromlis":

          if (is_admin())
          {

            echo "<h3>LIS Professors without LIS Dissertations</h3>\n";

            # and then once the data collection is done
            # cassidy wants a list of all the people who were advisors and committee
            # members for lis, but were not themselves lis

            # get list of LIS dissertations
            $query = "SELECT
                      d.id
                    FROM
                      dissertations d
                    WHERE
                      d.discipline_id = '1'
                    ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $lis_dissertations[] = $line['id'];
            }
            # get advisors for each diss
            $advisors = array();
            foreach ($lis_dissertations as $id){
              $query = "SELECT person_id
                          FROM advisorships
                          WHERE dissertation_id = $id
                        ";
              $results = $dbh->query($query);
              while ( $line = $results->fetchArray() ) {
                $advisors[] = $line['person_id'];
              }
            }
            # get committeeships for each diss
            $committeemembers = array();
            foreach ($lis_dissertations as $id){
              $query = "SELECT person_id
                          FROM committeeships
                          WHERE dissertation_id = $id
                        ";
              $results = $dbh->query($query);
              while ( $line = $results->fetchArray() ) {
                $committeemembers[] = $line['person_id'];
              }
            }
            $total = array_merge($advisors,$committeemembers);
            $unique = array_unique($total);

            $unique_list = implode(",",$unique);
            $listed = array();
            $query = "SELECT person_id
                        FROM dissertations
                        WHERE
                          person_id IN ($unique_list)
                          AND
                          discipline_id = '1'
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $listed[] = $line['person_id'];
            }
            $notlisted = array_diff($unique,$listed);

            # print them out
            echo "<p>\n";
            $count = 0;
            foreach($notlisted as $pid){
              $count++;
              print "$count. ";
              print get_person_link($pid);
              print "<br />\n";
            }
            if ($count == 0)
            {
              print " - None";
            }
            echo "</p>\n";

          }
          else
          {
             not_admin();
          }

        break;

        ###############################################
        case "lis_nocommittee":

          if (is_admin())
          {

            echo "<h3>LIS Dissertations with no committee</h3>\n";

            # get list of LIS dissertations
            $query = "SELECT
                      d.id
                    FROM
                      dissertations d
                    WHERE
                      d.discipline_id = '1'
                    ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $lis_dissertations[] = $line['id'];
            }
            # get advisors for each diss
            $advisors = array();
            foreach ($lis_dissertations as $id){
              $query = "SELECT count(*) as howmany
                          FROM advisorships
                          WHERE dissertation_id = $id
                        ";
              $results = $dbh->query($query);
              while ( $line = $results->fetchArray() ) {
                if ($line['howmany'] > 0){$hascomm[] = $id;};
              }
            }
            # get committeeships for each diss
            $committeemembers = array();
            foreach ($lis_dissertations as $id){
              $query = "SELECT count(*) as howmany
                          FROM committeeships
                          WHERE dissertation_id = $id
                        ";
              $results = $dbh->query($query);
              while ( $line = $results->fetchArray() ) {
                if ($line['howmany'] > 0){$hascomm[] = $id;};
              }
            }
            $unique = array_unique($hascomm);
            $nocomm = array_diff($lis_dissertations,$unique);

            # print them out
            echo "<p>\n";
            $count = 0;
            foreach($nocomm as $did){
              $count++;
              print "$count. ";
              $d = find_dissertation($did);
              print get_person_link($d['person_id']);
              print "<br />\n";
            }
            if ($count == 0)
            {
              print " - None";
            }
            echo "</p>\n";

          }
          else
          {
             not_admin();
          }

        break;

        ###############################################
        case "lis_profs_with_mpact":

        if (is_admin())
        {
          echo "<h3>LIS Professors With MPACT</h3>\n";

          # advisors or committee members
          # that served on an lis dissertation


          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }

          # get advisors for each diss
          $advisors = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $advisors[] = $line['person_id'];
            }
          }

          # get committeeships for each diss
          $committeemembers = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $committeemembers[] = $line['person_id'];
            }
          }

          $total = array_merge($advisors,$committeemembers);

          $unique = array_unique($total);

          echo "<pre>\n";
          echo "Count|Name|Year|A|C|A+C|T\n";
          foreach ($unique as $prof){
            $mpact = mpact_scores($prof);
            $person = find_person($prof);
            $dissertation = find_dissertation_by_person($prof);
            # count
            $count += 1;
            echo "$count";
            echo "|";
            # name
            echo $person['fullname'];
            echo "|";
            # year
            echo $dissertation['completedyear'];
            echo "|";
            # a
            echo $mpact['A'];
            echo "|";
            # c
            echo $mpact['C'];
            echo "|";
            # a+c
            echo $mpact['AC'];
            echo "|";
            # t
            echo $mpact['T'];
            echo "\n";
          }
          echo "</pre>\n";
        }

        break;

        ###############################################
        case "lis_profs_summary":

        if (is_admin())
        {
          echo "<h3>LIS Professors Summary</h3>\n";

          # advisors or committee members
          # that served on an lis dissertation

          echo "<table border=1>\n";

          # get list of LIS dissertations
          $query = "SELECT
                    d.id
                  FROM
                    dissertations d
                  WHERE
                    d.discipline_id = '1'
                  ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $lis_dissertations[] = $line['id'];
          }
          echo "<tr><td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_allyears\">Total LIS Dissertations</a></td><td>".count($lis_dissertations)."</td></tr>\n";

          # get advisors for each diss
          $advisors = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM advisorships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $advisors[] = $line['person_id'];
            }
          }
          echo "<tr><td>Total LIS Dissertation Advisorships</td><td>";
          echo count($advisors);
          echo "</td></tr>\n";

          # get committeeships for each diss
          $committeemembers = array();
          foreach ($lis_dissertations as $id){
            $query = "SELECT person_id
                        FROM committeeships
                        WHERE dissertation_id = $id
                      ";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $committeemembers[] = $line['person_id'];
            }
          }
          echo "<tr><td>Total LIS Dissertation Committeeships</td><td>";
          echo count($committeemembers);
          echo "</td></tr>\n";

          $total = array_merge($advisors,$committeemembers);
          echo "<tr><td>Total LIS Dissertation Advisorships and Committeeships:</td><td>";
          echo count($total);
          echo "</td></tr>\n";

          $unique = array_unique($total);
          echo "<tr><td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profs_with_mpact\">Total number of unique advisor/committee members on LIS dissertations</a>:</td><td>";

          echo count($unique);
          echo "</td></tr>\n";

          $unique_list = implode(",",$unique);
          $known = array();
          $query = "SELECT person_id
                      FROM dissertations
                      WHERE
                        person_id IN ($unique_list)
                    ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $known[] = $line['person_id'];
          }
          echo "<tr><td>";
          echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?op=lis_profswithoutdiss\">Subset of ".count($unique)." without a listed dissertation</a>:</td><td>";
          echo count(array_diff($unique,$known));
          echo "</td></tr>\n";
          echo "<tr><td>Subset of ".count($unique)." with a listed dissertation:</td><td>";
          echo count($known);
          echo "</td></tr>\n";

          $query = "SELECT count(*) as howmany
                      FROM dissertations
                      WHERE
                        person_id IN ($unique_list)
                        AND
                        discipline_id != 16
                    ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $howmany = $line['howmany'];
          }
          echo "<tr><td> - Subset of ".count($known)." with known discipline:</td><td>";
          echo $howmany;
          echo "</td></tr>\n";

          $query = "SELECT count(*) as howmany
                      FROM dissertations
                      WHERE
                        person_id IN ($unique_list)
                        AND
                        completedyear != 0000
                    ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $howmany = $line['howmany'];
          }
          echo "<tr><td> - Subset of ".count($known)." with known year:</td><td>";
          echo $howmany;
          echo "</td></tr>\n";

          $query = "SELECT count(*) as howmany
                      FROM dissertations
                      WHERE
                        person_id IN ($unique_list)
                        AND
                        completedyear != 107
                    ";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $howmany = $line['howmany'];
          }
          echo "<tr><td> - Subset of ".count($known)." with known school:</td><td>";
          echo $howmany;
          echo "</td></tr>\n";

          echo "</table>\n";

        }
        else
        {
          not_admin();
        }

        break;

        ###############################################
        case "lis_allyears":

          if (is_admin())
          {
            echo "<h3>LIS, Graduates By Year</h3>\n";
            print "<pre>\n";

            # get list of dissertations
            $query = "SELECT
                      d.id, d.person_id, d.completedyear
                    FROM
                      dissertations d
                    WHERE
                      d.discipline_id = '1'
                    ORDER BY
                      d.completedyear ASC,
                      d.school_id ASC
                    ";
            $schools = array();
            $count = 0;
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $dissertation = find_dissertation($line['id']);
              $person = find_person($line['person_id']);
              $schoolinfo = find_persons_school($line['person_id']);
              $count++;
              print $count;
              print "|";
              print $line['completedyear'];
              print "|";
              print $schoolinfo['school'];
              print "|";
              print $person['fullname'];
#              print "|";
#              print $dissertation['title'];
#              print "|";
#              print $dissertation['abstract'];
              print "\n";
            }

            print "</pre>\n";
          }
          else
          {
             not_admin();
          }

        break;

        ###############################################
        case "lis_history":

          if (is_admin())
          {
            echo "<h3>LIS Dissertations by School by Year</h3>\n";

            $firstyear = 1920;
            $lastyear = 2008;

            print "<pre>\n";
            print "school|";
            for ($i = $firstyear; $i <= $lastyear; $i++)
            {
                print "$i|";
            }
            print "\n";

            # get list of schools (all of them)
            $query = "SELECT
                    s.id, s.fullname
                    FROM
                      schools s
                    ORDER BY
                      s.fullname
                    ";
            $schools = array();
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $schools[] = $line;
            }
            foreach ($schools as $s)
            {
              # loop through each school and find count by year
              $query = "SELECT
                    d.id, d.person_id, d.completedyear, COUNT(d.completedyear) AS yeartotal
                    FROM
                      dissertations d
                    WHERE
                      d.discipline_id = '1' AND
                      d.school_id = '".$s['id']."'
                    GROUP BY
                      d.completedyear
                  ";

              print $s['fullname']."|";
              $d = array();
              $results = $dbh->query($query);
              while ( $line = $results->fetchArray() ) {
                $d[$s['id']][$line['completedyear']] = $line['yeartotal'];
              }

              # walk through all years, and print out counts for this school
              for ($i = $firstyear; $i <= $lastyear; $i++)
              {
                if ($d[$s['id']][$i] > 0)
                  print $d[$s['id']][$i]."|";
                else
                  print "0|";
              }
              print "\n";
            }

            print "</pre>";
          }
          else
          {
             not_admin();
          }

        break;

        ###############################################
        case "lis_incomplete":

          echo "<h3>Incomplete LIS Dissertation Listings</h3>\n";

          $discipline_id = 1; # hard coded for LIS
          $schools = find_schools($discipline_id);

          foreach ($schools as $one => $two)
          {

            $year_conferred = array();
            $degree_status = array();

            $query = "SELECT d.person_id, d.completedyear, d.status
                  FROM
                    dissertations d,
                    schools s,
                    people p,
                    names n
                  WHERE
                    s.id = '$one' AND
                    d.discipline_id = '$discipline_id' AND
                    d.school_id = s.id AND
                    d.person_id = p.id AND
                    p.preferred_name_id = n.id AND
                    d.status < 4
                  ORDER BY
                    s.id ASC, d.completedyear ASC, n.lastname ASC, n.firstname ASC
                ";

            $resultcount = 0;
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $resultcount++;
              extract($line);
              $year_conferred[$person_id] = $completedyear;
              $degree_status[$person_id] = $status;
            }

            if ($resultcount > 0)
            {
              echo "<h3 style=\"padding:8px;background-color:#ddd;\"><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_department&s=$one&d=$discipline_id\">$two</a></h3>\n";
            }

            $incompletecount = 0;
            echo "<table>";
            foreach ($year_conferred as $person => $year)
            {
              $incompletecount++;
              $printme = "<tr>\n";
              $printme .= "<td width=\"310\">$incompletecount. ";
              $printme .= "<strong>".get_person_link($person)."</strong> ($year)</td>\n";

              $printme .= "<td>".$statuscodes[$degree_status[$person]]."</td>\n";
              $printme .= "</tr>\n";

              echo $printme;
            }
            echo "</table>";

          }
          echo "<br /><br />";

        break;

        ###############################################
        case "all_edges":

          if (is_admin())
          {
            echo "<h3>All Dissertations and Mentor Relationships</h3>\n";

            print "<pre>\n";
            print "mentor|role|protege|year|school\n";

            # each dissertation
            $query = "SELECT id, person_id, completedyear, school_id FROM dissertations";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              extract($line);
              $student = find_person($person_id);
              $query2 = "SELECT fullname as schoolname FROM schools WHERE id = '$school_id'";
              $line2 = $dbh->querySingle($query2, true);
              extract($line2);
              print $student['fullname']."|dissertation|null|$completedyear|$schoolname\n";
              # get advisorships
              $advisors = find_advisors_for_person($person_id);
              foreach ($advisors as $mentor_id){
                $mentor = find_person($mentor_id);
                print $mentor['fullname']."|advisorship|".$student['fullname']."|$completedyear|$schoolname\n";
              }
              # get committeeships
              $committee = find_committee_for_person($person_id);
              foreach ($committee as $mentor_id){
                $mentor = find_person($mentor_id);
                print $mentor['fullname']."|committeeship|".$student['fullname']."|$completedyear|$schoolname\n";
              }
            }
            print "</pre>";
          }
          else
          {
             not_admin();
          }

        break;

        ###############################################
        case "citation_correlation":

          if (is_admin())
          {
            echo "<h3>Citation Correlation Data</h3>\n";

            $correlated_ids = array(
              953,
              2098,
#              5086,   bc
              3659,
#              1329,  dalhousie
              5087,
              478,
              3094,
              3497,
              4784,
              1250,
              5088,
              5089,
              5090,
              4657,
#              5091,  mcdowell, not phd yet
              5092,
              5093,
              5094,
              2668,
              4076,
              2683,
              3978,
              2425,
              3645,
              2660,
              2233,
              2665,
              1310,
              2548,
              2708,
              2592,
              4648,
              5095,
              5096,
              4654,
              5097,
              5098,
              1234,
              1299,
              1294,
              3062,
              3110,
              1283,
              1220,
              874,
              584,
              3127,
              1142,
              3116,
              5099,
              5100,
              1025,
              486,
              3130,
              1321,
              5101,
              4502,
              5102,
              535,
              5160,   # koohang
              2673,
              5103,
              5104,
              1950,
              3972,
              3278,
              5105,
              3571,
              5106,
              3994,
              1504,
              4181,
              3140,
              2323,   # benoit added
              5107,
              800,
              4438,
              5108,
              5109,
              4760,
              2570,
              1866,
              3238,
              1846,
              1806,
              2527,
              3703,
              4758,
              3683,
              3846,
              2603,
              4011,
              2343,
              2329,
              5110,
              5111,
              4706,
              2761,
              1413,
              3028,
              3590,
              3668,
              2883,
              3063,
              3091,
              1705,
              3031,
# gary again  486,
              3979,
              3018,
              1855,
              3409,
              2747,
              3093,
              3065,
              3060,
              1685,
              2114,
              5112,
# moore rm    5113,
              2309,
# liu rm      2215,
              4086,
              4013,
              1573
            );

            echo "<table border=\"1\">\n";
            echo "<tr>
              <td>-</td>
              <td>Name</td>
              <td>A</td>
              <td>C</td>
              <td>A+C</td>
              <td>FMI</td>
              <td>FME</td>
              <td>FMI/(A+C)</td>
              <td>FME/(A+C)</td>
              </tr>\n";
            $counter = 0;
            foreach ($correlated_ids as $one)
            {
              $counter++;
              echo "<tr>";
              echo "<td>$counter.</td>";
              echo "<td>";
              echo get_person_link($one);
              echo "</td>";
              $scores = mpact_scores($one);
              echo "<td>".$scores['A']."</td>";
              echo "<td>".$scores['C']."</td>";
              echo "<td>".$scores['AC']."</td>";
              echo "<td>".$scores['FMI']."</td>";
              echo "<td>".$scores['FME']."</td>";
              echo "<td>".$scores['FMIdivFULL']."</td>";
              echo "<td>".$scores['FMEdivFULL']."</td>";
              echo "</tr>\n";
            }
            echo "</table>\n";

          }
          else
          {
             not_admin();
          }

          break;

        ###############################################
        case "top_a":
        case "top_c":
        case "top_ac":
        case "top_g":
        case "top_td":
        case "top_w":
        case "top_t":
        case "top_ta":

        $list_type = strtoupper(substr($_GET['op'],4));
        echo "<h3>Top ";
        if ($list_type == "TD"){echo "T<sub>D</sub>";}
        else if ($list_type == "TA"){echo "T<sub>A</sub>";}
        else if ($list_type == "AC"){echo "A+C";}
        else {echo $list_type;}
        echo " List</h3>\n";
        $score_type = strtolower($list_type)."_score";
        $people = find_all_people();
        foreach ($people as $p)
        {
          $scores[$p] = find_mpact_score($p,$score_type);
        }
        # zero out indecipherables
        $scores[28] = 0;
        $scores[391] = 0;
        asort($scores);
        $scores = array_reverse($scores, true);
        $top = narray_slice($scores, 0, 100); #calls custom function, end of mpact_include (passes true)
        $count = 0;
        $lasttotal = 0;
        echo "<table>\n";
        foreach ($top as $one => $two)
        {
          $count++;
          echo "<tr>";
          echo "<td>";
          if ($two != $lasttotal){echo $count.".";}
          echo "</td>";
          echo "<td>";
          echo get_person_link($one);
          echo "</td>";
          echo "<td>";
          echo $two;
          echo "</td>";
          echo "</tr>\n";
          $lasttotal = $two;
        }
        echo "</table>\n";
        break;

        ###############################################
        case "create_discipline":

        if (is_admin())
        {
          echo "<h3>Creating a New Discipline</h3>\n";

          echo "<p>";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"create_discipline\">\n";

          echo "<table border=1>";
          echo "<tr><td>Title</td></tr>";


          echo "<tr>";
          echo "<td><input type=\"text\" size=\"70\" name=\"title\"></td>";
          echo "</tr>";

          echo "</table>";

          echo "<input type=\"submit\" name=\"button\" value=\"Create\">";
          echo "</form>";

          echo "</p>";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "edit_discipline":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          elseif (!is_empty_discipline($_GET['id']))
          {
            action_box("Discipline must be empty to edit...",2,$_SERVER['SCRIPT_NAME']."?op=show_disciplines");
          }
          else{

            $query = "SELECT title FROM disciplines WHERE id=".$_GET['id'];
            $line = $dbh->querySingle($query, true);
            extract($line);

            echo "<h3>Editing a Discipline</h3>\n";

            echo "<p>";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
            echo "<input type=\"hidden\" name=\"discipline_id\" value=\"".$_GET['id']."\">\n";
            echo "<input type=\"hidden\" name=\"op\" value=\"edit_discipline\">\n";

            echo "<table border=1>";
            echo "<tr><td>Title</td></tr>";


            echo "<tr>";
            echo "<td><input type=\"text\" size=\"70\" name=\"title\" value=\"$title\"></td>";
            echo "</tr>";

            echo "</table>";

            echo "<input type=\"submit\" name=\"button\" value=\"Save\">";
            echo "</form>";

            echo "</p>";
          }
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "create_school":

        if (is_admin())
        {
          echo "<h3>Creating a New School</h3>\n";

          echo "<p>";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"create_school\">\n";

          echo "<table border=1>";
          echo "<tr><td>Fullname</td><td>Country</td></tr>";


          echo "<tr>";
          echo "<td><input type=\"text\" size=\"70\" name=\"fullname\"></td>";
          echo "<td><input type=\"text\" size=\"25\" name=\"country\" value=\"USA\"></td>";
          echo "</tr>";

          echo "</table>";

          echo "<input type=\"submit\" name=\"button\" value=\"Create\">";
          echo "</form>";

          echo "</p>";
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "edit_school":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          elseif (!is_empty_school($_GET['id']))
          {
            action_box("School must be empty to edit...",2,$_SERVER['SCRIPT_NAME']."?op=show_schools");
          }
          else{

            $query = "SELECT fullname, country FROM schools WHERE id=".$_GET['id'];
            $line = $dbh->querySingle($query, true);
            extract($line);

            echo "<h3>Editing a School</h3>\n";

            echo "<p>";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
            echo "<input type=\"hidden\" name=\"school_id\" value=\"".$_GET['id']."\">\n";
            echo "<input type=\"hidden\" name=\"op\" value=\"edit_school\">\n";

            echo "<table border=1>";
            echo "<tr><td>Fullname</td><td>Country</td></tr>";


            echo "<tr>";
            echo "<td><input type=\"text\" size=\"70\" name=\"fullname\" value=\"$fullname\"></td>";
            echo "<td><input type=\"text\" size=\"25\" name=\"country\" value=\"$country\"></td>";
            echo "</tr>";

            echo "</table>";

            echo "<input type=\"submit\" name=\"button\" value=\"Save\">";
            echo "</form>";

            echo "</p>";
          }
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "create_person":

        if (is_admin())
        {
          echo "<h3>Creating a New Person</h3>\n";

          echo "<p>";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"create_person\">\n";

          echo "<table border=1>";
          echo "<tr><td>First</td><td>Middle</td><td>Last</td><td>Suffix</td><td>Degree</td></tr>";


          echo "<tr>";
          echo "<td><input type=\"text\" size=\"10\" name=\"firstname\"></td>";
          echo "<td><input type=\"text\" size=\"10\" name=\"middlename\"></td>";
          echo "<td><input type=\"text\" size=\"12\" name=\"lastname\"></td>";
          echo "<td><input type=\"text\" size=\"5\" name=\"suffix\"></td>";
          echo "<td>";
          echo "<select name=\"degree\">\n";
          foreach ($degree_types as $one){
            echo "<option value=\"$one\"";
            echo ">$one</option>\n";
          }
          echo "</select>\n";
          echo "</td>";
          echo "</tr>";

          echo "</table>";

          echo "<input type=\"submit\" name=\"button\" value=\"Create\">";
          echo "</form>";

          echo "</p>";
        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "add_mentor":

        if (is_admin())
        {
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_person\">Add a New Person to the Database</a></p>\n";

          echo "<h3>Adding a Mentor</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"add_mentor\">\n";
          echo "<input type=\"hidden\" name=\"student_id\" value=\"".$_GET['id']."\">\n";

          echo "<p>";

          echo "Mentor Type:<br />\n";
          echo "<input type=\"radio\" name=\"mentor_type\" value=\"A\"";
          if ($_GET['type'] == "A"){echo " checked";}
          echo "> Advisor<br />\n";
          echo "<input type=\"radio\" name=\"mentor_type\" value=\"C\"";
          if ($_GET['type'] == "C"){echo " checked";}
          echo "> Committee Member\n";
          echo "<br /><br />\n";

          echo "Mentor:<br />\n";
          echo "<select name=\"mentor_id\">\n";
          $people = find_all_people_for_selectbox();
          foreach ($people as $person_id => $person)
          {
            echo "<option value=\"$person_id\">".$person['lastname'];
            if ($person['suffix'] != ""){echo " ".$person['suffix'];}
            echo ", ".$person['firstname']." ".$person['middlename']."</option>\n";
          }
          echo "</select>\n";

          echo "<br /><br />\n";

          echo "Student: <br />\n". get_person_link($_GET['id']);
          echo "<br /><br />\n";

          echo "<input type=\"submit\" name=\"button\" value=\"Add Mentor\">";

          echo "</p>";
          echo "</form>";

          draw_tree($_GET['id']);

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "add_student":

        if (is_admin())
        {
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_person\">Add a New Person to the Database</a></p>\n";

          echo "<h3>Adding a Student</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"add_student\">\n";
          echo "<input type=\"hidden\" name=\"mentor_id\" value=\"".$_GET['id']."\">\n";

          echo "<p>";

          echo "Mentor Type:<br />\n";
          echo "<input type=\"radio\" name=\"mentor_type\" value=\"A\"";
          if ($_GET['type'] == "A"){echo " checked";}
          echo "> Advisor<br />\n";
          echo "<input type=\"radio\" name=\"mentor_type\" value=\"C\"";
          if ($_GET['type'] == "C"){echo " checked";}
          echo "> Committee Member\n";
          echo "<br /><br />\n";

          echo "Mentor: <br />\n". get_person_link($_GET['id']);
          echo "<br /><br />\n";


          echo "Student:<br />\n";
          echo "<select name=\"student_id\">\n";
          $people = find_all_people_for_selectbox("1"); # the 1 is to only get people with dissertations
          foreach ($people as $person_id => $person)
          {
            echo "<option value=\"$person_id\">".$person['lastname'];
            if ($person['suffix'] != ""){echo " ".$person['suffix'];}
            echo ", ".$person['firstname']." ".$person['middlename']."</option>\n";
          }
          echo "</select>\n";

          echo "<br /><br />\n";
          echo "<input type=\"submit\" name=\"button\" value=\"Add Student\">";

          echo "</p>";
          echo "</form>";

          draw_tree($_GET['id']);

        }
        else
        {
           not_admin();
        }

        break;



      ###############################################
      case "remove_mentor":

        if (is_admin())
        {
          echo "<h3>Removing a Mentor</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"remove_mentor\">\n";
          echo "<input type=\"hidden\" name=\"student_id\" value=\"".intval($_GET['student_id'])."\">\n";
          echo "<input type=\"hidden\" name=\"mentor_id\" value=\"".intval($_GET['mentor_id'])."\">\n";
          echo "<input type=\"hidden\" name=\"mentor_type\" value=\"".$_GET['type']."\">\n";

          echo "<p>";

          echo "Are you sure you want to remove this mentor relationship?<br /><br />\n";
          if ($_GET['type'] == "A"){echo "Advisor: ";}
          if ($_GET['type'] == "C"){echo "Commitee Member: ";}
          echo get_person_link(intval($_GET['mentor_id']))."<br />\n";
          echo "Student: ".get_person_link(intval($_GET['student_id']))."<br />\n";
          echo "<br />\n";

          echo "<input type=\"submit\" name=\"button\" value=\"Yes, remove this relationship.\">";
          echo " If NOT, please go BACK";

          echo "</p>";
          echo "</form>";

          draw_tree(intval($_GET['student_id']));

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "add_url";

        if (is_admin())
        {
          echo "<h3>Adding a Reference URL</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"add_url\">\n";
          echo "<input type=\"hidden\" name=\"person_id\" value=\"".intval($_GET['id'])."\">\n";

          echo "<p>";

          echo "<table border='0'>\n";
          echo "<tr><td>URL:</td><td><input type=\"text\" name=\"url\" size=\"70\"></td></tr>\n";
          echo "<tr><td>Description:</td><td><textarea rows=\"4\" cols=\"68\" name=\"description\"></textarea></td></tr>\n";
          echo "</table>\n";

          echo "<input type=\"submit\" name=\"button\" value=\"Add this URL\">";

          echo "</p>";
          echo "</form>";

          draw_tree(intval($_GET['id']));

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "edit_url";

        if (is_admin())
        {
          $url = find_url(intval($_GET['id']));
          echo "<h3>Editing a Reference URL</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"edit_url\">\n";
          echo "<input type=\"hidden\" name=\"id\" value=\"".intval($_GET['id'])."\">\n";

          echo "<p>";

          echo "<table border='0'>\n";
          echo "<tr><td>URL:</td><td><input type=\"text\" name=\"url\" size=\"70\" value=\"".$url['url']."\"></td></tr>\n";
          echo "<tr><td>Description:</td><td><textarea rows=\"4\" cols=\"68\" name=\"description\">".$url['description']."</textarea></td></tr>\n";
          echo "<tr><td>Last Updated:</td><td>".$url['updated_at']."</td></tr>\n";
          echo "</table>\n";

          echo "<input type=\"submit\" name=\"button\" value=\"Edit this URL\">";

          echo "</p>";
          echo "</form>";

          draw_tree($url['person_id']);

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "delete_url";

        if (is_admin())
        {
          $url = find_url(intval($_GET['id']));
          echo "<h3>Deleting a Reference URL</h3>\n";

          echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
          echo "<input type=\"hidden\" name=\"op\" value=\"delete_url\">\n";
          echo "<input type=\"hidden\" name=\"id\" value=\"".intval($_GET['id'])."\">\n";

          echo "<p>";

          echo "Are you sure you want to delete this Reference URL?<br /><br />\n";
          echo "<table border='0'>\n";
          echo "<tr><td>URL:</td><td><a href=\"".$url['url']."\">".$url['url']."</a></td></tr>\n";
          echo "<tr><td>Description:</td><td>".$url['description']."</td></tr>\n";
          echo "<tr><td>Last Updated:</td><td>".$url['updated_at']."</td></tr>\n";
          echo "</table>\n";
          echo "<br />\n";

          echo "<input type=\"submit\" name=\"button\" value=\"Yes, delete this Reference URL\">";
          echo " If NOT, please go BACK";

          echo "</p>";
          echo "</form>";

          draw_tree($url['person_id']);

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "edit_degree":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          else
          {

            $person = find_person($_GET['id']);

            echo "<h3>Editing a Degree</h3>";
            echo "<p>";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
            echo "<input type=\"hidden\" name=\"op\" value=\"edit_degree\">";
            echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">";


            echo "<table border=1>";
            echo "<tr><td>Name</td><td>Degree</td></tr>";


            echo "<tr>";
            echo "<td>".$person['fullname']."</td>";
            echo "<td>";
            echo "<select name=\"degree\">\n";
            foreach ($degree_types as $one){
              echo "<option value=\"$one\"";
              if ($person['degree'] == $one){
                echo " selected";
              }
              echo ">$one</option>\n";
            }
            echo "</select>\n";
            echo "</td>";
            echo "</tr>";

            echo "</table>";

            echo "<input type=\"submit\" name=\"submit\" value=\"Save\">";

            echo "</form>";

            echo "</p>";


            echo "<table border=\"0\"><tr><td>";
            draw_tree($_GET['id']);
            echo "</td></tr></table>";



          }
        }
        else
        {
           not_admin();
        }

        break;


      ###############################################
      case "add_name":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          else
          {

            $person = find_person($_GET['id']);

            echo "<h3>Adding an Alias</h3>";
            echo "<p>";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
            echo "<input type=\"hidden\" name=\"op\" value=\"add_name\">";
            echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">";


            echo "<table border=1>";
            echo "<tr><td>First</td><td>Middle</td><td>Last</td><td>Suffix</td></tr>";


            echo "<tr>";
            echo "<td><input type=\"text\" size=\"10\" name=\"firstname\"></td>";
            echo "<td><input type=\"text\" size=\"10\" name=\"middlename\"></td>";
            echo "<td><input type=\"text\" size=\"10\" name=\"lastname\"></td>";
            echo "<td><input type=\"text\" size=\"10\" name=\"suffix\"></td>";
            echo "</tr>";

            echo "</table>";

            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";

            echo "</form>";

            echo "</p>";


            echo "<table border=\"0\"><tr><td>";
            draw_tree($_GET['id']);
            echo "</td></tr></table>";



          }
            }
            else
            {
               not_admin();
            }

        break;

      ###############################################
      case "edit_name":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          else
          {

            $person = find_person($_GET['id']);

            echo "<h3>Editing a Name</h3>";
            echo "<p>";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
            echo "<input type=\"hidden\" name=\"op\" value=\"edit_name\">";
            echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">";
            echo "<input type=\"hidden\" name=\"name_id\" value=\"".$person['preferred_name_id']."\">";


            echo "<table border=1>";
            echo "<tr><td>First</td><td>Middle</td><td>Last</td><td>Suffix</td></tr>";


            echo "<tr>";
            echo "<td><input type=\"text\" size=\"10\" name=\"firstname\" value=\"".$person['firstname']."\"></td>";
            echo "<td><input type=\"text\" size=\"10\" name=\"middlename\" value=\"".$person['middlename']."\"></td>";
            echo "<td><input type=\"text\" size=\"12\" name=\"lastname\" value=\"".$person['lastname']."\"></td>";
            echo "<td><input type=\"text\" size=\"5\" name=\"suffix\" value=\"".$person['suffix']."\"></td>";
            echo "</tr>";

            echo "</table>";

            echo "<input type=\"submit\" name=\"submit\" value=\"Save\">";

            echo "</form>";

            echo "</p>";


            echo "<table border=\"0\"><tr><td>";
            draw_tree($_GET['id']);
            echo "</td></tr></table>";



          }
        }
        else
        {
           not_admin();
        }

        break;

        ###############################################
        case "create_dissertation":

          if (is_admin())
          {

            $disciplines = find_disciplines();
            $schools = find_schools();

            if (!$_GET['person_id']){action_box("No ID given.");}
            else
            {

              $person = find_person($_GET['person_id']);
              if (!$person)
              {
                action_box("Person not found.");
              }
              else{

                echo "<h3>CREATE DISSERTATION</h3>\n";

                echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
                echo "<input type=\"hidden\" name=\"op\" value=\"create_dissertation\">";
                echo "<input type=\"hidden\" name=\"person_id\" value=\"".intval($person['id'])."\">";

                echo "<p>";
                echo get_person_link($person['id']);
                echo "<br />";

                echo "Degree: <select name=\"degree\">\n";
                foreach ($degree_types as $one)
                {
                  echo "<option value=\"$one\"";
                  if ($one == "Ph.D."){
                    echo " selected";
                  }
                  echo ">$one</option>\n";
                }
                echo "</select>\n";
                echo "<br />";

                echo "Status: <select name=\"status\">\n";
                foreach (range(0,4) as $one)
                {
                  echo "<option value=\"$one\"";
                  echo ">".$statuscodes[$one]."</option>\n";
                }
                echo "</select>\n";
                echo "<br />";

                echo "Discipline: <select name=\"discipline_id\">\n";
                foreach ($disciplines as $one => $two)
                {
                  echo "<option value=\"$one\">$two</option>\n";
                }
                echo "</select>\n";
                echo "<br />";


                echo "School: ";
                echo "<select name=\"school_id\">\n";
                foreach ($schools as $one => $two)
                {
                  echo "<option value=\"$one\"";
                  echo ">$two</option>\n";
                }
                echo "</select>\n";
                echo "<br />Year:\n";
                echo "<input type=\"text\" size=\"10\" name=\"completedyear\"><br />";
                echo "Title:<br /><textarea cols=\"60\" rows=\"3\" name=\"title\"></textarea><br />";
                echo "Abstract:<br /><textarea cols=\"60\" rows=\"7\" name=\"abstract\"></textarea><br />";
                echo "<input type=\"submit\" name=\"submit\" value=\"Save\">";
                echo "</p>";

                echo "</form>";

                echo "<p>";
                echo "Currently:";
                echo "</p>";
                echo "<table border=\"0\"><tr><td>";
                draw_tree($person['id']);
                echo "</td></tr></table>";
              }

            }
          }
          else
          {
             not_admin();
          }

          break;


        ###############################################
        case "edit_dissertation":

          if (is_admin())
          {
            if (!$_GET['id']){action_box("No ID given.");}
            else
            {

              $disciplines = find_disciplines();
              $schools = find_schools();
              $dissertation = find_dissertation(intval($_GET['id']));
              if (!$dissertation)
              {
                action_box("Dissertation not found.");
              }
              else{

                $person = find_person($dissertation['person_id']);

                echo "<h3>EDIT DISSERTATION DETAILS</h3>\n";

                echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
                echo "<input type=\"hidden\" name=\"op\" value=\"edit_dissertation\">";
                echo "<input type=\"hidden\" name=\"id\" value=\"".intval($_GET['id'])."\">";
                echo "<input type=\"hidden\" name=\"person_id\" value=\"".intval($person['id'])."\">";

                echo "<p>";
                echo get_person_link($person['id']);
                echo "<br />";

                echo "Degree: <select name=\"degree\">\n";
                foreach ($degree_types as $one)
                {
                  echo "<option value=\"$one\"";
                  if ($person['degree'] == $one)
                  {
                    echo " selected";
                  }
                  echo ">$one</option>\n";
                }
                echo "</select>\n";
                echo "<br />";

                echo "Status: <select name=\"status\">\n";
                foreach (range(0,4) as $one)
                {
                  echo "<option value=\"$one\"";
                  if ($dissertation['status'] == $one)
                  {
                    echo " selected";
                  }
                  echo ">".$statuscodes[$one]."</option>\n";
                }
                echo "</select>\n";
                echo "<br />";

                echo "Discipline: <select name=\"discipline_id\">\n";
                foreach ($disciplines as $one => $two)
                {
                  echo "<option value=\"$one\"";
                  if ($dissertation['discipline_id'] == $one)
                  {
                    echo " selected";
                  }
                  echo ">$two</option>\n";
                }
                echo "</select>\n";
                echo "<br />";

                echo "School: <select name=\"school_id\">\n";
                foreach ($schools as $one => $two)
                {
                  echo "<option value=\"$one\"";
                  if ($dissertation['school_id'] == $one)
                  {
                    echo " selected";
                  }
                  echo ">$two</option>\n";
                }
                echo "</select>\n";

                echo "<br />Year:\n";
                echo "<input type=\"text\" size=\"10\" name=\"completedyear\" value=\"".$dissertation['completedyear']."\"><br />";
                echo "Title:<br /><textarea cols=\"60\" rows=\"3\" name=\"title\">".$dissertation['title']."</textarea><br />";
                echo "Abstract:<br /><textarea cols=\"60\" rows=\"7\" name=\"abstract\">".$dissertation['abstract']."</textarea><br />";
                echo "Admin Notes:<br /><textarea cols=\"60\" rows=\"7\" name=\"notes\">".$dissertation['notes']."</textarea><br />";
                echo "<input type=\"submit\" name=\"submit\" value=\"Save\">";
                echo "</p>";

                echo "</form>";

                echo "<p>";
                echo "Currently:";
                echo "</p>";
                echo "<table border=\"0\"><tr><td>";
                draw_tree($person['id']);
                echo "</td></tr></table>";
              }

            }
          }
          else
          {
             not_admin();
          }

          break;

      ###############################################
      case "delete_name":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          else
          {

            $person = find_name($_GET['name']);

            echo "<p>";

            echo "Are you sure you want to delete this name?";

            echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
            echo "<input type=\"hidden\" name=\"op\" value=\"delete_name\">\n";
            echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">\n";
            echo "<input type=\"hidden\" name=\"name_id\" value=\"".$_GET['name']."\">";

            echo "<p>";
            echo "[ ".$person['firstname']." ".$person['middlename']." ".$person['lastname']." ".$person['suffix']." ]<br />";
            echo "</p>";


            echo "<p>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Yes\">";
            echo " &nbsp;&nbsp;<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_GET['id']."\">Cancel</a></p>";

            echo "</form>";

            echo "</p>";




          }
            }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "set_preferred_name":

        if (is_admin())
        {

          set_preferred_name($_GET['id'],$_GET['name']);

          action_box("Preferred Name Selected",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_GET['id']);

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "recalculate_mpact":

        if (is_admin()){

          # recalculate MPACT scores for passed person id
          calculate_scores($_GET['id']);

          action_box("MPACT Scores Recalculated",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_GET['id']);

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "show_orphans":

        if (is_admin())
        {

          $orphans = find_orphans();

          echo "<h2>Orphans</h2>\n";

          echo "<p>\n";
          $counter = 0;
          foreach ($orphans as $orphan)
          {
            $counter++;
            echo $counter." ";
            echo get_person_link($orphan['person_id']);
            echo " (<a href=\"".$_SERVER['SCRIPT_NAME']."?op=delete_person&id=".$orphan['person_id']."\">Delete from Database</a>)";
            echo "<br />\n";
          }
          if ($counter < 1){echo " - No orphans at this time.";}
          echo "</p>\n";

        }
        else
        {
           not_admin();
        }

        break;

      ###############################################
      case "delete_person":

        if (is_admin())
        {
          if (!$_GET['id']){action_box("No ID given.");}
          else
          {

            if (is_orphan($_GET['id']))
            {
              echo "<p>";

              echo "Are you sure you want to delete this person?";

              echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
              echo "<input type=\"hidden\" name=\"op\" value=\"delete_person\">\n";
              echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">\n";

              echo "<p>";
              echo get_person_link($_GET['id']);
              echo "</p>";


              echo "<p>";
              echo "<br /><br />\n";
              echo "<input type=\"submit\" name=\"submit\" value=\"Yes, Delete this person completely.\">";
              echo " &nbsp;&nbsp;<a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_GET['id']."\">Cancel</a></p>";

              echo "</form>";

              echo "</p>";
            }
            else
            {
              action_box("This person cannot be deleted.");
            }
          }
        }
        else
        {
           not_admin();
        }

        break;


      ###############################################
      case "show_disciplines":

        $statustotals = array(0,0,0,0,0);
        $disciplines = find_disciplines();
        $disciplinecounts = find_discipline_counts();

        if (is_admin()){
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_discipline\">Add a New Discipline to the Database</a></p>\n";
        }

        echo "<h2>Disciplines Represented Across All Schools</h2>\n";

        echo "<p>\n";
        echo "<table border='0'>\n";
        echo "<tr>";
        echo "<td width='400'>Discipline</td>";
        foreach (range(0,4) as $one)
        {
          echo "<td>$statuscodes[$one]</td>";
        }
        echo "</tr>";
        foreach ($disciplines as $one => $two)
        {
          $thecount = $disciplinecounts[$one] ?? 0;
          echo "<tr>";
          echo "<td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_discipline&id=$one\">$two</a> (".intval($thecount).")</td>";
          $statuscounts = find_discipline_statuses($one);
          foreach (range(0,4) as $three)
          {
            $statustotals[$three] += $statuscounts[$three];
            if (!isset($disciplinecounts[$one]) || $disciplinecounts[$one]==0)
            {
              $fraction = 0;
            }
            else
            {
              $fraction = 100*$statuscounts[$three]/intval($disciplinecounts[$one]);
            }
            echo "<td width='150'>".$statuscounts[$three];
            if ($fraction != 0)
            {
              echo " (".sprintf("%.1f",$fraction)."%)";
            }
            echo "</td>";
          }
          echo "</tr>\n";
        }
        # summary line
        echo "<tr><td>------------------</td><td colspan=\"5\"></td></tr>";
        echo "<tr>";
        echo "<td>";
        echo count($disciplines)." Disciplines (".array_sum($disciplinecounts)." dissertations)";
        echo "</td>";
        foreach (range(0,4) as $three)
        {
          $fraction = 100*$statustotals[$three]/array_sum($disciplinecounts);
          echo "<td width='150'>".$statustotals[$three];
          if ($fraction != 0)
          {
            echo " (".sprintf("%.1f",$fraction)."%)";
          }
          echo "</td>";
        }
        echo "</tr>";
        echo "</table>\n";
        echo "</p>\n";

        break;

        ###############################################
        case "show_discipline":

        if (!$_GET['id']){action_box("No ID given.");}
        else
        {

          $discipline_id = intval($_GET['id']);

          # Show Discipline Name
          $query = "SELECT d.title as disciplinename
                FROM disciplines d
                WHERE
                  d.id = '$discipline_id'
              ";

          $line = $dbh->querySingle($query, true);
          extract($line);

          echo "<h2>$disciplinename</h2>";

          # Show Histograph
          $counts = array();
          echo "<p>\n";
          echo "Dissertations by Year:<br />\n";
          $query = "SELECT completedyear, count(*) as disscount FROM dissertations WHERE discipline_id='$discipline_id' GROUP BY completedyear";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $counts[$line['completedyear']] = $line['disscount'];
          }
          if (count($counts)>0)
          {
            $bigyear = max($counts);
            echo "<table cellpadding=\"1\" cellspacing=\"0\">\n";
            echo "<tr>\n";
              foreach ($counts as $one => $two)
              {
                $height=$two*intval(200/$bigyear);
                echo "<td valign='bottom'><img src='images/blue.gif' height='".$height."' width='8' title='".$two." in ".$one."'></td>\n";
              }
            echo "</tr>";
            echo "<tr>\n";
            foreach ($counts as $one => $two)
            {
              echo "<td>";
              if ($one%10==0)
              {
                echo $one;
              }
              echo "</td>\n";
            }
            echo "</tr>";
            echo "</table>\n";
          }
          echo "</p>\n";
        }

        $schools = find_schools($discipline_id);
        $dissertation_count = 0;
        $statustotals = array(0,0,0,0,0);
        echo "<p>Schools Represented</p>\n";

        echo "<p>\n";
        echo "<table border='0'>\n";
        echo "<tr>";
        echo "<td width='400'>School</td>";
        foreach (range(0,4) as $one)
        {
          echo "<td>$statuscodes[$one]</td>";
        }
        echo "</tr>";
        foreach ($schools as $one => $two)
        {
          $statuscounts = find_dept_statuses($one,$discipline_id);
          $dissertation_count += array_sum($statuscounts);
          echo "<tr>";
          echo "<td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_department&s=$one&d=$discipline_id\">$two</a> (".array_sum($statuscounts).")</td>";
          foreach (range(0,4) as $three)
          {
            $statustotals[$three] += $statuscounts[$three];
            $fraction = 100*$statuscounts[$three]/array_sum($statuscounts);
            echo "<td width='150'>".$statuscounts[$three];
            if ($fraction != 0)
            {
              echo " (".sprintf("%.1f",$fraction)."%)";
            }
            echo "</td>";
          }
          echo "</tr>\n";
        }
        # summary line
        echo "<tr><td>------------------</td><td colspan=\"5\"></td></tr>";
        echo "<tr>";
        echo "<td>";
        echo count($schools)." Schools ($dissertation_count dissertations)";
        echo "</td>";
        foreach (range(0,4) as $three)
        {

          if (array_sum($statustotals)==0)
          {
            $fraction = 0;
          }
          else
          {
            $fraction = 100*$statustotals[$three]/array_sum($statustotals);
          }
          echo "<td width='150'>".$statustotals[$three];
          if ($fraction != 0)
          {
            echo " (".sprintf("%.1f",$fraction)."%)";
          }
          echo "</td>";
        }
        echo "</tr>";
        echo "</table>\n";
        echo "</p>\n";

        # link to edit the discipline name
        if (is_empty_discipline($_GET['id']))
        {
          echo "<br /><br />";
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_discipline&id=".$_GET['id']."\">Edit Discipline Name</a></p>\n";
        }

        break;

      ###############################################
      case "show_school":

        if (!$_GET['id']){action_box("No ID given.");}
        else
        {

          $school_id = intval($_GET['id']);

          # Show School Name
          $query = "SELECT s.fullname as schoolname, s.country
                FROM schools s
                WHERE
                  s.id = '$school_id'
              ";

          $line = $dbh->querySingle($query, true);
          extract($line);

          echo "<h2>$schoolname ($country)</h2>";

          # Show Histograph
          echo "<p>\n";
          echo "Dissertations by Year:<br />\n";
          $query = "SELECT completedyear, count(*) as disscount FROM dissertations WHERE school_id='$school_id' GROUP BY completedyear";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $counts[$line['completedyear']] = $line['disscount'];
          }
          $bigyear = max($counts);
          echo "<table cellpadding=1 cellspacing=0>\n";
          echo "<tr>\n";
          foreach ($counts as $one => $two)
          {
            $height=$two*intval(200/$bigyear);
            echo "<td valign='bottom'><img src='images/blue.gif' height='".$height."' width='8' title='".$two." in ".$one."'></td>\n";
          }
          echo "</tr>";
          echo "<tr>\n";
          foreach ($counts as $one => $two)
          {
            echo "<td>";
            if ($one%10==0)
            {
              echo $one;
            }
            echo "</td>\n";
          }
          echo "</tr>";
          echo "</table>\n";
          echo "</p>\n";


          $disciplines = find_disciplines($school_id);
          $dissertation_count = 0;
          $statustotals = array(0,0,0,0,0);
          echo "<p>Disciplines Represented</p>\n";

          echo "<p>\n";
          echo "<table border='0'>\n";
          echo "<tr>";
          echo "<td width='400'>Discipline</td>";
          foreach (range(0,4) as $one)
          {
            echo "<td>$statuscodes[$one]</td>";
          }
          echo "</tr>";
          foreach ($disciplines as $one => $two)
          {
            $statuscounts = find_dept_statuses($school_id,$one);
            $dissertation_count += array_sum($statuscounts);
            echo "<tr>";
            echo "<td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_department&s=$school_id&d=$one\">$two</a> (".array_sum($statuscounts).")</td>";
            foreach (range(0,4) as $three)
            {
              $statustotals[$three] += $statuscounts[$three];
              $fraction = 100*$statuscounts[$three]/array_sum($statuscounts);
              echo "<td width='150'>".$statuscounts[$three];
              if ($fraction != 0)
              {
                echo " (".sprintf("%.1f",$fraction)."%)";
              }
              echo "</td>";
            }
            echo "</tr>\n";
          }
          # summary line
          echo "<tr><td>------------------</td><td colspan=\"5\"></td></tr>";
          echo "<tr>";
          echo "<td>";
          echo count($disciplines)." Disciplines ($dissertation_count dissertations)";
          echo "</td>";
          foreach (range(0,4) as $three)
          {
            $fraction = 100*$statustotals[$three]/array_sum($statustotals);
            echo "<td width='150'>".$statustotals[$three];
            if ($fraction != 0)
            {
              echo " (".sprintf("%.1f",$fraction)."%)";
            }
            echo "</td>";
          }
          echo "</tr>";
          echo "</table>\n";
          echo "</p>\n";
        }

        # link to edit the school info
        if (is_empty_school($_GET['id']))
        {
          echo "<br /><br />";
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=edit_school&id=".$_GET['id']."\">Edit School Information</a></p>\n";
        }

        break;

      ###############################################
      case "show_schools":

        $statustotals = array(0,0,0,0,0);
        $schools = find_schools();
        $schoolcounts = find_school_counts();

        if (is_admin()){
          echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_school\">Add a New School to the Database</a></p>\n";
        }
        echo "<h2>Schools Represented Across All Disciplines</h2>\n";

        echo "<p>\n";
        echo "<table border='0'>\n";
        echo "<tr>";
        echo "<td width='400'>School</td>";
        foreach (range(0,4) as $one)
        {
          echo "<td>$statuscodes[$one]</td>";
        }
        echo "</tr>";
        foreach ($schools as $one => $two)
        {
          echo "<tr>";
          echo "<td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_school&id=$one\">$two</a> (".intval($schoolcounts[$one]).")</td>";
          $statuscounts = find_school_statuses($one);
          foreach (range(0,4) as $three)
          {
            $statustotals[$three] += $statuscounts[$three];
            if (intval($schoolcounts[$one]) == 0){
              $fraction = 0;
            }
            else{
              $fraction = 100*$statuscounts[$three]/intval($schoolcounts[$one]);
            }
            echo "<td width='150'>".$statuscounts[$three];
            if ($fraction != 0)
            {
              echo " (".sprintf("%.1f",$fraction)."%)";
            }
            echo "</td>";
          }
          echo "</tr>\n";
        }
        # summary line
        echo "<tr><td>------------------</td><td colspan=\"5\"></td></tr>";
        echo "<tr>";
        echo "<td>";
        echo count($schools)." Schools (".array_sum($schoolcounts)." dissertations)";
        echo "</td>";
        foreach (range(0,4) as $three)
        {
          $fraction = 100*$statustotals[$three]/array_sum($schoolcounts);
          echo "<td width='150'>".$statustotals[$three];
          if ($fraction != 0)
          {
            echo " (".sprintf("%.1f",$fraction)."%)";
          }
          echo "</td>";
        }
        echo "</tr>";
        echo "</table>\n";
        echo "</p>\n";

        break;

      ###############################################
      case "show_department":

        if (!$_GET['d'] || !$_GET['s']){action_box("No ID given.");}
        else
        {

          $school_id = $_GET['s'];
          $discipline_id = $_GET['d'];


          # Show Discipline Name
          $query = "SELECT d.title as disciplinename
                FROM disciplines d
                WHERE
                  d.id = '$discipline_id'
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);
          # Show School Name
          $query = "SELECT s.fullname as schoolname, s.country
                FROM schools s
                WHERE
                  s.id = '$school_id'
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);

          echo "
          <h2>
          <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_discipline&id=$discipline_id\">$disciplinename</a>
          @
          <a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_school&id=$school_id\">$schoolname ($country)</a>
          </h2>
          ";

          # Advisor and Committee Activity
          echo "<h3>Dissertation Activity and MPACT Scores (click table headings to sort)</h3>";

          echo "<p>";
          $theprofs = find_profs_at_dept($school_id,$discipline_id);
          if (count($theprofs)>0)
          {
            $sortedprofs = array();
            $proflist = "";
            $query = "SELECT id FROM people WHERE id IN (";
            foreach ($theprofs as $prof){$proflist .= "$prof,";}
            $proflist = rtrim($proflist, ",");
            $query .= "$proflist) ORDER BY ac_score DESC";
            $results = $dbh->query($query);
            while ( $line = $results->fetchArray() ) {
              $sortedprofs[] = $line['id'];
            }
            $profcount = 0;
            echo "<table border=\"0\" width=\"90%\" class=\"sortable\">\n";
            echo "<tr>
              <td>-</td>
              <td>Name</td>
              <td>A</td>
              <td>C</td>
              <td>A+C</td>
              <td>G</td>
              <td>T</td>
              <td>T<sub>A</sub></td>
              <td>T<sub>D</sub></td>
              <td>W</td>
              </tr>\n";
            foreach ($sortedprofs as $one)
            {
              echo "<tr>";
              $profcount++;
              echo "<td>$profcount.</td>";
              echo "<td>";
              echo get_person_link($one);
              echo "</td>";
              $scores = mpact_scores($one);
              echo "<td>".$scores['A']."</td>";
              echo "<td>".$scores['C']."</td>";
              echo "<td>".$scores['AC']."</td>";
              echo "<td>".$scores['G']."</td>";
              echo "<td>".$scores['T']."</td>";
              echo "<td>".$scores['TA']."</td>";
              echo "<td>".$scores['TD']."</td>";
              echo "<td>".$scores['W']."</td>";
              echo "</tr>\n";
            }
            echo "</table>\n";
            echo "</p>";
          }
          else
          {
            echo "<p>- None Listed</p>\n";
          }
          echo "<hr />";




          # Dissertations Conferred
          echo "<h3>Dissertations Conferred</h3>";




          # Show Histograph
          echo "<p>\n";
          echo "Dissertations by Year:<br />\n";
          $query = "SELECT completedyear, count(*) as disscount
                      FROM dissertations
                      WHERE
                        school_id='$school_id' AND
                        discipline_id=$discipline_id
                      GROUP BY
                        completedyear";
          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            $counts[$line['completedyear']] = $line['disscount'];
          }
          echo "<table cellpadding=1 cellspacing=0>\n";
          echo "<tr>\n";
          foreach ($counts as $one => $two)
          {
            $height=$two*10;
            echo "<td valign='bottom'><img src='images/blue.gif' height='".$height."' width='8' title='".$two." in ".$one."'></td>\n";
          }
          echo "</tr>";
          echo "<tr>\n";
          foreach ($counts as $one => $two)
          {
            echo "<td>";
            if ($one%10==0)
            {
              echo $one;
            }
            echo "</td>\n";
          }
          echo "</tr>";
          echo "</table>\n";
          echo "</p>\n";





          $query = "SELECT d.person_id, d.completedyear, d.status
                FROM
                  dissertations d,
                  schools s,
                  people p,
                  names n
                WHERE
                  s.id = '$school_id' AND
                  d.discipline_id = '$discipline_id' AND
                  d.school_id = s.id AND
                  d.person_id = p.id AND
                  p.preferred_name_id = n.id
                ORDER BY
                  d.completedyear ASC, n.lastname ASC, n.firstname ASC
              ";

          $results = $dbh->query($query);
          while ( $line = $results->fetchArray() ) {
            extract($line);
            $year_conferred[$person_id] = $completedyear;
            $degree_status[$person_id] = $status;
          }

          $schoolcount = 0;
          echo "<p>";
          foreach ($year_conferred as $person => $year)
          {
            $schoolcount++;
            echo "<br />$schoolcount. ";
            echo get_person_link($person);
            echo " ($year)";
            if ($degree_status[$person] < 4)
            {
              echo " ---------------------------------- ".$statuscodes[$degree_status[$person]];
            }
          }
          echo "</p>";

        }

        break;

      ###############################################
      default:
        // oops - not supposed to be here
        action_box("invalid action",1);
    }
  }
  else
  {

    if (isset($_GET['show']))
    {
      if ($_GET['show'] == "all")
      {
        $alphabet_limiter = ""; // set it to show everything
      }
      else
      {
        $alphabet_limiter = $_GET['show'];
      }

      show_alphabet();

      if (is_admin())
      {
        echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_person\">Add a New Person to the Database</a></p>\n";
      }

      if (is_admin()){
        echo "<form id=\"main_mpact\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
        echo "<input type=\"hidden\" name=\"op\" value=\"merge_confirm\">";
        echo "<input type=\"hidden\" name=\"show\" value=\"$alphabet_limiter\">";
        echo "<input type=\"submit\" name=\"submit\" value=\"Merge These People\">";
      }

      // GET ALL DISSERTATIONS IN A HASH
      $disciplines = find_disciplines();
      $query = "SELECT id, person_id, discipline_id FROM dissertations";
      $results = $dbh->query($query);
      while ( $line = $results->fetchArray() ) {
        $graduates[$line['person_id']] = $line['discipline_id'];
      }

      // LOOP THROUGH ALL PEOPLE FOR THIS LETTER
      echo "<table border=\"0\" width=\"95%\">\n";
      echo "<tr>\n";
      if (is_admin()){echo "<td><strong>Merge</strong></td>\n";}
      echo "<td><strong>Count</strong></td>\n";
      echo "<td><strong>Last Name</strong></td>\n";
      echo "<td><strong>Full Name</strong></td>\n";
      echo "<td><strong>Dissertation Discipline</strong></td>\n";
      echo "</tr>\n";

      $query = "SELECT n.id, n.firstname,
            n.middlename, n.lastname, n.suffix,
            p.id as person_id
            FROM names n, people p
            WHERE
              n.id = p.preferred_name_id AND
              n.lastname LIKE '".$alphabet_limiter."%'
            ORDER BY n.lastname ASC, n.firstname ASC
            ";

      $rowcount = 0;
      $results = $dbh->query($query);
      while ( $line = $results->fetchArray() ) {
        extract($line);

        $rowcount++;

        echo "<tr";
        if ($rowcount % 2 == 0){echo " bgcolor=\"#eeeeee\"";}
        echo ">";
           if (is_admin()){
          echo "<td width=\"15\"><input type=\"checkbox\" name=\"mergers[]\" value=\"$person_id\"></td>";
           }
        echo "<td width=\"15\">$rowcount.</td>";
        echo "<td><a href=\"".$_SERVER['SCRIPT_NAME']."?op=show_tree&id=$person_id\"><strong>$lastname</strong></a></td>";
        $discipline = isset($graduates[$person_id]) ? $disciplines[$graduates[$person_id]] : "";
        echo "<td>$firstname $middlename $lastname $suffix</td>";
        echo "<td>$discipline</td>";
        echo "</tr>";

        if (is_admin()){
          if ($rowcount % 25 == 0){
            echo "<tr><td colspan=\"4\"></td><td colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\"Merge These People\"></td></tr>";
          }
        }

      }

      echo "</table>\n";

      if (is_admin()){
        echo "<input type=\"submit\" name=\"submit\" value=\"Merge These People\">";
        echo "</form>";
       }

      echo "<br /><br />\n";

      show_alphabet();

    }
    else
    {
      // DISPLAY THE FRONT PAGE

      action_box("Front Page...",0.1,"./");

    }

  }

}









###############################################
// SOMETHING WAS SUBMITTED - through a form
else
{

  if ($_POST['op'] != "login"){
    // Display header
    xhtml_web_header();
  }

  switch ($_POST['op'])
  {
    ###############################################
    case "login";

      $host_info = get_environment_info();
      if ($host_info['hostname'] == "sils"){
        # no logins here - go to dev server
        action_box("No Logins Here...",2,$_SERVER['SCRIPT_NAME']);
      }
      else{
        # check credentials / start session
        $query = "SELECT id, username, fullname FROM users
                    WHERE username = '".addslashes($_POST['username'])."'
                    AND password = '".addslashes($_POST['password'])."'";
        $line = $dbh->querySingle($query, true);
        if (isset($line['id'])){
          # save cookie info for one week
          setcookie('MPACT_userid',$line['id'],time()+60*60*24*7);
          # redirect
          header("Location: ".$_SERVER['SCRIPT_NAME']);
        }
        else{
          // Display header
          xhtml_web_header();
          # incorrect credentials
          action_box("Please try again...",2,$_SERVER['SCRIPT_NAME']."?op=login");
        }
      }

      break;

    ###############################################
    case "glossary_edit";
      if (is_admin())
      {

        $_POST = array_map('addslashes',$_POST);

        $query = "UPDATE glossary
              SET
                definition = '".$_POST['definition']."'
              WHERE
                id = '".$_POST['id']."'
            ";
        $dbh->exec($query);

        # log it
        mpact_logger("updated glossary [".$_POST['term']." (".$_POST['id'].")] to (".$_POST['definition'].")");

        action_box("Definition Saved",2,$_SERVER['SCRIPT_NAME']."?op=glossary");
      }
      else
      {
        not_admin();
      }
      break;

    ###############################################
    case "merge_confirm":

      if (is_admin()){

        echo "<form id=\"merge_em\" method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">";
        echo "<input type=\"hidden\" name=\"op\" value=\"merge_doit\">";
        echo "<input type=\"hidden\" name=\"show\" value=\"".$_POST['show']."\">";

        foreach ($_POST['mergers'] as $one)
        {
          echo "<table border=\"1\"><tr><td>";
          echo "<p>[$one]</p>";
          echo draw_tree( $one );
          echo "<input type=\"hidden\" name=\"mergers[]\" value=\"$one\">";
          echo "</td></tr></table>";
        }

        echo "<input type=\"submit\" name=\"submit\" value=\"YES, THESE ARE THE SAME PERSON - MERGE THEM NOW\">";
        echo "</form>";

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "search":

      $results = people_search($_POST['q']);

      if (count($results) > 0)
      {
        echo "<p>People Search Results for [".$_POST['q']."]:</p>";
      }

      echo "<p>\n";
      $count = 0;
      foreach ($results as $person_id)
      {
        $count++;
        $schoolinfo = find_persons_school($person_id);
        echo "$count. ".get_person_link($person_id);
        if ($schoolinfo['completedyear'])
        {
          echo " - ".$schoolinfo['school']." (".$schoolinfo['completedyear'].")";
        }
        echo "<br />\n";
      }
      if ($count < 1)
      {
        echo "<p>\n";
        echo "There were no results for [".$_POST['q']."].";
        if (strlen($_POST['q']) < 2)
        {
          echo "<br /><br />\n";
          echo "Please use at least two characters to search.";
        }
        else
        {
          if (is_admin()){
            echo "<br /><br />\n";
            echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."?op=create_person\">Add a New Person to the Database</a></p>\n";
          }
        }
        echo "</p>\n";
      }

      echo "</p>\n";

      break;

    ###############################################
    case "search_title_abstract":

      $results = title_abstract_search($_POST['qta']);

      if (count($results) > 0)
      {
        echo "<p>Title/Abstract Search Results for [".$_POST['qta']."]:</p>";
      }

      echo "<p>\n";
      $count = 0;
      foreach ($results as $person_id)
      {
        $count++;
        $schoolinfo = find_persons_school($person_id);
        echo "$count. ".get_person_link($person_id);
        if ($schoolinfo['completedyear'])
        {
          echo " - ".$schoolinfo['school']." (".$schoolinfo['completedyear'].")";
        }
        echo "<br />\n";
      }
      if ($count < 1)
      {
        echo "<p>\n";
        echo "There were no results for [".$_POST['qta']."].";
        if (strlen($_POST['qta']) < 2)
        {
          echo "<br /><br />\n";
          echo "Please use at least two characters to search.";
        }
        else
        {
          if (is_admin()){
            echo "<br /><br />\n";
          }
        }
        echo "</p>\n";
      }

      echo "</p>\n";

      break;

      ###############################################
      case "search_notes":

        if (is_admin()){

          $results = notes_search($_POST['qn']);

          if (count($results) > 0)
          {
            echo "<p>Admin Notes Search Results for [".$_POST['qn']."]:</p>";
          }

          echo "<p>\n";
          $count = 0;
          foreach ($results as $person_id)
          {
            $count++;
            $schoolinfo = find_persons_school($person_id);
            echo "$count. ".get_person_link($person_id);
            if ($schoolinfo['completedyear'])
            {
              echo " - ".$schoolinfo['school']." (".$schoolinfo['completedyear'].")";
            }
            echo "<br />\n";
          }
          if ($count < 1)
          {
            echo "<p>\n";
            echo "There were no results for [".$_POST['qn']."].";
            if (strlen($_POST['qn']) < 2)
            {
              echo "<br /><br />\n";
              echo "Please use at least two characters to search.";
            }
            else
            {
              if (is_admin()){
                echo "<br /><br />\n";
              }
            }
            echo "</p>\n";
          }

          echo "</p>\n";
        }
        else
        {
           not_admin();
        }

        break;

    ###############################################
    case "merge_doit":

      if (is_admin()){

        # delete the family tree's dotgraphs of each person
        foreach ($_POST['mergers'] as $one)
        {
          delete_associated_dotgraphs($one);
        }

        # pop off one of the people_ids
        $into = array_pop($_POST['mergers']);

        # merge each remaining person into the one above
        foreach ($_POST['mergers'] as $one)
        {
          merge_two_people($one,$into);
        }

        # recalculate MPACT scores for merged person id
        calculate_scores($into);

        action_box("Merge was Successful",2,$_SERVER['SCRIPT_NAME']."?show=".$_POST['show']);

      }
      else
      {
         not_admin();
      }

      break;


    ###############################################
    case "create_discipline":

      if (is_admin()){
        $_POST = array_map('addslashes',$_POST);

        if ($_POST['title'] == "")
        {
          action_box("Need to have at least a Discipline Title.",3,$_SERVER['SCRIPT_NAME']."?op=create_discipline");
        }
        elseif (is_duplicate_discipline($_POST['title']))
        {
          action_box("Discipline (".$_POST['title'].") already exists.",3,$_SERVER['SCRIPT_NAME']."?op=show_disciplines");
        }
        else
        {

          # Create Discipline
          $query = "INSERT disciplines
                SET
                  title   = '".$_POST['title']."'
              ";
          $dbh->exec($query);

          # Get the just created discipline_id
          $query = "SELECT id as new_discipline_id, title FROM disciplines
                WHERE
                  title   = '".$_POST['title']."'
                ORDER BY id DESC
                LIMIT 1
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);
          # log it
          mpact_logger("created discipline[".$new_discipline_id."] (".$title.")");

          action_box("Discipline Created",2,$_SERVER['SCRIPT_NAME']."?op=show_disciplines");

        }
      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "edit_discipline":

      if (is_admin()){
        $_POST = array_map('addslashes',$_POST);

        if ($_POST['title'] == "")
        {
          action_box("Need to have at least a Discipline Title.",3,$_SERVER['SCRIPT_NAME']."?op=edit_discipline&id=".$_POST['discipline_id']."");
        }
        elseif (is_duplicate_discipline($_POST['title']))
        {
          action_box("Discipline (".$_POST['title'].") already exists.",3,$_SERVER['SCRIPT_NAME']."?op=show_disciplines");
        }
        else
        {

          # Edit Discipline
          $query = "UPDATE disciplines
                SET
                  title   = '".$_POST['title']."'
                WHERE
                  id = '".$_POST['discipline_id']."'
              ";
          $dbh->exec($query);

          # log it
          mpact_logger("edited discipline[".$_POST['discipline_id']."] (".$_POST['title'].")");

          action_box("Discipline Edited",2,$_SERVER['SCRIPT_NAME']."?op=show_disciplines");

        }
      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "create_school":

      if (is_admin()){
        $_POST = array_map('addslashes',$_POST);

        $_POST = array_map('trim',$_POST);

        if ($_POST['fullname'] == "" || $_POST['country'] == "")
        {
          action_box("Need to have at least a School name and Country.",3,$_SERVER['SCRIPT_NAME']."?op=create_school");
        }
        elseif (is_duplicate_school($_POST['fullname']))
        {
          action_box("School (".$_POST['fullname'].") already exists.",3,$_SERVER['SCRIPT_NAME']."?op=show_schools");
        }
        else
        {

          # Create School
          $query = "INSERT schools
                SET
                  fullname   = '".$_POST['fullname']."',
                  country   = '".$_POST['country']."'
              ";
          $dbh->exec($query);

          # Get the just created school_id
          $query = "SELECT id as new_school_id, fullname FROM schools
                WHERE
                  fullname   = '".$_POST['fullname']."'
                ORDER BY id DESC
                LIMIT 1
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);
          # log it
          mpact_logger("created school[".$new_school_id."] (".$fullname.")");

          action_box("School Created",2,$_SERVER['SCRIPT_NAME']."?op=show_schools");

        }
      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "edit_school":

      if (is_admin()){
        $_POST = array_map('addslashes',$_POST);

        if ($_POST['fullname'] == "" || $_POST['country'] == "")
        {
          action_box("Need to have at least a School name and Country.",3,$_SERVER['SCRIPT_NAME']."?op=edit_school&id=".$_POST['school_id']."");
        }
        elseif (is_duplicate_school($_POST['fullname']))
        {
          action_box("School (".$_POST['fullname'].") already exists.",3,$_SERVER['SCRIPT_NAME']."?op=show_schools");
        }
        else
        {

          # Edit School
          $query = "UPDATE schools
                SET
                  fullname   = '".$_POST['fullname']."',
                  country    = '".$_POST['country']."'
                WHERE
                  id = '".$_POST['school_id']."'
              ";
          $dbh->exec($query);

          # log it
          mpact_logger("edited school[".$_POST['school_id']."] (".$_POST['fullname'].")");

          action_box("School Edited",2,$_SERVER['SCRIPT_NAME']."?op=show_schools");

        }
      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "create_person":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        if ($_POST['lastname'] == "")
        {
          action_box("Need to have at least a Last Name.",3,$_SERVER['SCRIPT_NAME']."?op=create_person");
        }
        else
        {
          # Create Name
          $query = "INSERT names
                SET
                  firstname   = '".$_POST['firstname']."',
                  middlename  = '".$_POST['middlename']."',
                  lastname    = '".$_POST['lastname']."',
                  suffix      = '".$_POST['suffix']."'
              ";
          $dbh->exec($query);

          # Get the just created name_id
          $query = "SELECT id as new_name_id FROM names
                WHERE
                  firstname   = '".$_POST['firstname']."' AND
                  middlename  = '".$_POST['middlename']."' AND
                  lastname    = '".$_POST['lastname']."' AND
                  suffix      = '".$_POST['suffix']."'
                ORDER BY id DESC
                LIMIT 1
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);

          # Create Person with new_name_id
          $query = "INSERT people
                SET
                  preferred_name_id = '".$new_name_id."',
                  degree = '".$_POST['degree']."'
              ";
          $dbh->exec($query);

          # Get the just created person_id
          $query = "SELECT id as new_person_id FROM people
                WHERE
                  preferred_name_id = '".$new_name_id."'
                ORDER BY id DESC
                LIMIT 1
              ";
          $line = $dbh->querySingle($query, true);
          extract($line);

          # Sync them together with new_person_id
          $query = "UPDATE names
                SET
                  person_id = '".$new_person_id."'
                WHERE
                  id = '".$new_name_id."'
              ";
          $dbh->exec($query);

          $after = find_person($new_person_id);

          action_box("Person Created",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$new_person_id);

          # log it
          mpact_logger("created person[".$new_person_id."] (".$after['fullname'].")");

       }
     }
     else
     {
        not_admin();
     }

     break;


   ###############################################
   case "remove_mentor":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        if (remove_mentor($_POST['mentor_type'],$_POST['student_id'],$_POST['mentor_id']))
        {
          action_box("Mentor Removed",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['student_id']);
        }
        else
        {
          action_box("Nope.");
        }

      }
      else
      {
         not_admin();
      }

      break;


    ###############################################
    case "add_mentor":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        add_mentor($_POST['mentor_type'],$_POST['student_id'],$_POST['mentor_id']);

        action_box("Mentor Added",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['student_id']);

      }
      else
      {
        not_admin();
      }

      break;


     ###############################################
     case "add_student":

        if (is_admin()){

          $_POST = array_map('addslashes',$_POST);

          add_mentor($_POST['mentor_type'],$_POST['student_id'],$_POST['mentor_id']);

          action_box("Student Added",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['mentor_id']);

        }
        else
        {
           not_admin();
        }

        break;


    ###############################################
    case "add_name":

     if (is_admin()){

       $_POST = array_map('addslashes',$_POST);

        $person = find_person($_POST['id']);

        $query = "INSERT names
              SET
                firstname   = '".$_POST['firstname']."',
                middlename  = '".$_POST['middlename']."',
                lastname    = '".$_POST['lastname']."',
                suffix      = '".$_POST['suffix']."',
                person_id   = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        # get the just created name_id
        $query = "SELECT id as new_name_id FROM names
              WHERE
                firstname   = '".$_POST['firstname']."' AND
                middlename  = '".$_POST['middlename']."' AND
                lastname    = '".$_POST['lastname']."' AND
                suffix      = '".$_POST['suffix']."'
              ORDER BY id DESC
              LIMIT 1
            ";
        $line = $dbh->querySingle($query, true);
        extract($line);

        # find that full name from the DB
        $added = find_name($new_name_id);

        action_box("Name Added",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['id']);
        # log it
        mpact_logger("added name (".$added['fullname'].") for person[".$_POST['id']."] (".$person['fullname'].")");
     }
     else
     {
        not_admin();
     }

     break;

    ###############################################
    case "edit_name":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $before = find_name($_POST['name_id']);

        $query = "UPDATE names
              SET
                firstname   = '".$_POST['firstname']."',
                middlename  = '".$_POST['middlename']."',
                lastname    = '".$_POST['lastname']."',
                suffix      = '".$_POST['suffix']."'
              WHERE
                id = '".$_POST['name_id']."' AND
                person_id = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        $after = find_name($_POST['name_id']);

        # delete dotgraph for this person
        delete_associated_dotgraphs($_POST['id']);

        action_box("Name Saved",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['id']);
        # log it
        mpact_logger("edited name[".$_POST['name_id']."] for person[".$_POST['id']."] from (".$before['fullname'].") to (".$after['fullname'].")");

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "add_url":

     if (is_admin()){

       $_POST = array_map('addslashes',$_POST);

        $person = find_person(intval($_POST['person_id']));

        $query = "INSERT urls
              SET
                url         = '".$_POST['url']."',
                description = '".$_POST['description']."',
                updated_at  = datetime('now'),
                person_id   = '".$_POST['person_id']."'
            ";

        $dbh->exec($query);

        action_box("Reference URL Added",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['person_id']."#urls");
        # log it
        mpact_logger("added URL[".substr($_POST['url'], 0, 30)."] for person[".$_POST['person_id']."] (".$person['fullname'].")");
     }
     else
     {
        not_admin();
     }

     break;

    ###############################################
    case "edit_url":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $url = find_url(intval($_POST['id']));
        $person = find_person($url['person_id']);

        $query = "UPDATE urls
              SET
                url         = '".$_POST['url']."',
                description = '".$_POST['description']."',
                updated_at  = datetime('now')
              WHERE
                id = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        action_box("Reference URL Edited",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$url['person_id']."#urls");
        # log it
        mpact_logger("edited URL[".substr($_POST['url'], 0, 30)."] for person[".$url['person_id']."] (".$person['fullname'].")");

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "delete_url":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $url = find_url(intval($_POST['id']));
        $person = find_person($url['person_id']);

        $query = "DELETE FROM urls
              WHERE
                id = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        action_box("Reference URL Deleted",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$url['person_id']."#urls");
        # log it
        mpact_logger("deleted URL[".substr($url['url'], 0, 30)."] for person[".$url['person_id']."] (".$person['fullname'].")");

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "edit_degree":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $person = find_person(intval($_POST['id']));

        $query = "UPDATE people
              SET
                degree         = '".$_POST['degree']."'
              WHERE
                id = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        action_box("Degree Edited",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$person['id']);
        # log it
        mpact_logger("edited degree[".$_POST['degree']."] for person[".$person['id']."] (".$person['fullname'].")");

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "create_dissertation":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $schools = find_schools();

        $person = find_person($_POST['person_id']);

        $query = "INSERT INTO dissertations
              SET
                person_id           = '".$_POST['person_id']."',
                discipline_id       = '".$_POST['discipline_id']."',
                school_id           = '".$_POST['school_id']."',
                completedyear       = '".$_POST['completedyear']."',
                status              = '".$_POST['status']."',
                title               = '".$_POST['title']."',
                abstract            = '".$_POST['abstract']."'
            ";

        $dbh->exec($query);

        $query = "UPDATE people
                  SET degree = '".$_POST['degree']."'
                  WHERE
                    id = '".$_POST['person_id']."'";

        $dbh->exec($query);

        action_box("Dissertation Created",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['person_id']);
        # log it
        $dissertation = find_dissertation_by_person($_POST['person_id']);
        mpact_logger("create dissertation[".$dissertation['id']."] (".$person['fullname'].") with school[".$dissertation['school_id']."] (".$schools[$dissertation['school_id']].") in year(".$dissertation['completedyear'].") with status (".$statuscodes[$_POST['status']].")");

      }
      else
      {
         not_admin();
      }

      break;


    ###############################################
    case "edit_dissertation":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $disciplines = find_disciplines();
        $schools = find_schools();

        $dissertation = find_dissertation($_POST['id']);
        $person = find_person($dissertation['person_id']);

        $query = "UPDATE dissertations
              SET
                school_id           = '".$_POST['school_id']."',
                discipline_id       = '".$_POST['discipline_id']."',
                completedyear       = '".$_POST['completedyear']."',
                status              = '".$_POST['status']."',
                notes               = '".$_POST['notes']."',
                title               = '".$_POST['title']."',
                abstract            = '".$_POST['abstract']."'
              WHERE
                id = '".$_POST['id']."'
            ";

        $dbh->exec($query);

        $query = "UPDATE people
                  SET degree = '".$_POST['degree']."'
                  WHERE
                    id = '".$_POST['person_id']."'";

        $dbh->exec($query);

        action_box("Dissertation Saved",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['person_id']);
        # log it
        mpact_logger("edit dissertation[".$_POST['id']."] (".$person['fullname'].") from school[".$dissertation['school_id']."] (".$schools[$dissertation['school_id']].") in year(".$dissertation['completedyear'].") to school[".$_POST['school_id']."] (".$schools[$_POST['school_id']].") in year(".$_POST['completedyear'].")");
        # look for changes
          # degree
          if ($person['degree'] != $_POST['degree'])
          {
            mpact_logger($dissertation['id']." updated degree [".$_POST['degree']."]","dissertation");
          }
          # discipline
          if ($dissertation['discipline_id'] != $_POST['discipline_id'])
          {
            mpact_logger($dissertation['id']." updated discipline [".$_POST['discipline_id']." (".$disciplines[$_POST['discipline_id']].")]","dissertation");
          }
          # school
          if ($dissertation['school_id'] != $_POST['school_id'])
          {
            mpact_logger($dissertation['id']." updated school [".$_POST['school_id']." (".$schools[$_POST['school_id']].")]","dissertation");
          }
          # year
          if ($dissertation['completedyear'] != $_POST['completedyear'])
          {
            mpact_logger($dissertation['id']." updated completedyear [".$_POST['completedyear']."]","dissertation");
          }
          # title
          if ($dissertation['title'] != $_POST['title'])
          {
            mpact_logger($dissertation['id']." updated title [".$_POST['title']."]","dissertation");
          }
          # abstract
          if ($dissertation['abstract'] != $_POST['abstract'])
          {
            mpact_logger($dissertation['id']." updated abstract [".$_POST['abstract']."]","dissertation");
          }
          # status
          if ($dissertation['status'] != $_POST['status'])
          {
            mpact_logger($dissertation['id']." updated status [".$statuscodes[$_POST['status']]."]","dissertation");
          }
          # status
          if ($dissertation['notes'] != $_POST['notes'])
          {
            mpact_logger($dissertation['id']." updated notes [".$_POST['notes']."]","dissertation");
          }
      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "delete_person":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        if (delete_person($_POST['id']))
        {
          action_box("Person Deleted",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['id']);
        }

      }
      else
      {
         not_admin();
      }

      break;


    ###############################################
    case "delete_name":

      if (is_admin()){

        $_POST = array_map('addslashes',$_POST);

        $name = find_name($_POST['name_id']);
        $person = find_person($_POST['id']);

        $query = "DELETE FROM names
            WHERE
              id = '".$_POST['name_id']."' AND
              person_id = '".$_POST['id']."'
          ";

        $dbh->exec($query);

        action_box("Name Deleted",2,$_SERVER['SCRIPT_NAME']."?op=show_tree&id=".$_POST['id']);

        # log it
        mpact_logger("deleted name[".$_POST['name_id']."] (".$name['fullname'].") from person[".$person['id']."] (".$person['fullname'].")");

      }
      else
      {
         not_admin();
      }

      break;

    ###############################################
    case "edit_school":

      break;

    ###############################################
    default:
      // oops - not supposed to be here
      action_box("oops, back to the front page...",2);


  }


}

xhtml_web_footer();

?>
