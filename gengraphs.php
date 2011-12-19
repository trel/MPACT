<?php

$dotcachedir = "dotgraphs";
$dotfiletype = "png";
$dotlocation = "/export/sunsite/users/mpact/terrelllocal/bin/dot";
$dotfontface = "Times-Roman";

$generated = $exists = 0;

exec("ls $dotcachedir/*.dot",$dotfilelisting,$retval);
foreach ($dotfilelisting as $dotfile){
  $pid = substr($dotfile,0,-4);
  $mapfile = "$pid.map";
  $imagefile = "$pid.$dotfiletype";
  if (!file_exists($imagefile)){
    $getandgenerategraph = "cat $dotfile | $dotlocation -Nfontname=$dotfontface -Gcharset=latin1 -Tcmapx -o$mapfile -T$dotfiletype -o$imagefile";
#    print "[$getandgenerategraph]<br />";
    exec($getandgenerategraph,$output,$retval);
    $generated = $generated + 1;
  }
  else{
    $exists = $exists + 1;
  }
}

exec("chmod 666 $dotcachedir/*.map");
exec("chmod 666 $dotcachedir/*.$dotfiletype");

print "exists    = $exists\n";
print "generated = $generated\n";
print "total     = ".count($dotfilelisting)."\n";

?>

