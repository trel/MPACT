<?php

require 'envsetup.php';

# get dirty records
$dirtyrecords = array();
$query = "SELECT id from people where regenerate_dotgraph = '1'";
$result = mysql_query($query) or die(mysql_error());
while ( $line = mysql_fetch_array($result)) {
  array_push($dirtyrecords,$line['id']);
}

# generate graphs and clean the records
foreach ($dirtyrecords as $person){
  print "dirty found - regenerating for [$person]...\n";
  generate_dotgraph($person,"force");
  print " -- set to clean\n";
  $query = "UPDATE people SET regenerate_dotgraph = '0' WHERE id = '".$person."'";
  $result = mysql_query($query) or die(mysql_error());
}

?>
