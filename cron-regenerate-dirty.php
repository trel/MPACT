<?php

require 'envsetup.php';

# get dirty records
$dirtyrecords = array();
$query = "SELECT id from people where regenerate_dotgraph = '1'";
$result = mysql_query($query) or die(mysql_error());
while ( $line = mysql_fetch_array($result)) {
  array_push($dirtyrecords,$line['id']);
}

echo date("Y-m-d\TH:i:s")." found ".count($dirtyrecords)." record(s)\n";
# generate graphs and clean the records
foreach ($dirtyrecords as $person){
  print "- regenerating for [$person]\n";
  generate_dotgraph($person,"force");
  $query = "UPDATE people SET regenerate_dotgraph = '0' WHERE id = '".$person."'";
  $result = mysql_query($query) or die(mysql_error());
}

?>
