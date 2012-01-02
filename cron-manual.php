<?php

require 'envsetup.php';

#mark_all_graphs_as_dirty();
gen_all_profs_at_dept();
#gen_all_mpact_scores();

function mark_all_graphs_as_dirty()
{
  $query = "UPDATE people SET regenerate_dotgraph = '1'";
  $result = mysql_query($query) or die(mysql_error());
}

function gen_all_profs_at_dept()
{
  # calculate listing of professors in each discipline
  $query = "DELETE FROM profs_at_dept";
  $result = mysql_query($query) or die(mysql_error());

  $schools = array();
  $query = "SELECT id FROM schools";
  $result = mysql_query($query) or die(mysql_error());
  while ($line = mysql_fetch_array($result)){$schools[] = $line['id'];}

  $disciplines = array();
  $query = "SELECT id FROM disciplines";
  $result = mysql_query($query) or die(mysql_error());
  while ($line = mysql_fetch_array($result)){$disciplines[] = $line['id'];}

  $totalprofcount = count($schools) * count($disciplines);
  $count = 0;
  $prevdone = 0;
  print "profs  ";
  foreach ($schools as $school_id)
  {
    foreach ($disciplines as $discipline_id)
    {
      $prof_list = serialize(generate_profs_at_dept($school_id,$discipline_id));
      $query = "INSERT INTO profs_at_dept
                  SET
                    school_id     = '$school_id',
                    discipline_id = '$discipline_id',
                    professors    = '$prof_list'
                ";
      $result = mysql_query($query) or die(mysql_error());
      $count++;
      $done = floor(100*$count/$totalprofcount);
      if ($done>$prevdone) {
        if ($done%10==0) { print $done; }
        else { print "."; }
      }
      $prevdone = $done;
    }
  }
  print "\n";
}

function gen_all_mpact_scores()
{
  # calculate all mpact scores for all people in database
  $people = find_all_people();
  $totalpeople = count($people);
  $count = 0;
  $prevdone = 0;
  print "scores ";
  foreach ($people as $p)
  {
    calculate_scores($p);
    $count++;
    $done = floor(100*$count/$totalpeople);
    if ($done>$prevdone) {
      if ($done%10==0) { print $done; }
      else { print "."; }
    }
    $prevdone = $done;
  }
  print "\n";
}

?>
