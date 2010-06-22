<html>
<body>

<?php

# -------------------------------------------------------------------------------
function get_environment_info()
{
  $host_info = array();
  $hostname = $_SERVER['SERVER_NAME'];
  if ($hostname == ""){$hostname = exec(hostname);}
  echo "hostname = [$hostname]<br />";
  if ($hostname == "tribal" || $hostname == "www.ibiblio.org")
  {
    # army's install on ibiblio
    $host_info['hostname']    = "ibiblio";
    $host_info['ownername']   = "tgr";
    $host_info['dotlocation'] = "/export/sunsite/users/mpact/terrelllocal/bin/dot";
    $host_info['appdir']      = "/export/sunsite/users/mpact/public_html";
    $host_info['webdir']      = "http://www.ibiblio.org/mpact";
    $host_info['dotcachedir'] = "dotgraphs";
    $host_info['dotfiletype'] = "png";
    $host_info['dotfontface'] = "cour";
  }
  else
  {
    # unknown host
    exit;
  }

  return $host_info;
}
# -------------------------------------------------------------------------------

$host_info = get_environment_info();
$jesse_h_shera = 11;   # Jesse H. Shera is the default
if (isset($_GET['id'])){
  $person = ((int)$_GET['id'] != 0) ? (int)$_GET['id'] : $jesse_h_shera;
}
else
{
  $person = $jesse_h_shera;
}
echo "person = $person<br />";
$appcache = $host_info['appdir']."/".$host_info['dotcachedir'];
$appcache = $host_info['dotcachedir'];
$webcache = $host_info['webdir']."/".$host_info['dotcachedir'];
$appfilename = "$appcache/$person.".$host_info['dotfiletype'];
$webfilename = "$webcache/$person.".$host_info['dotfiletype'];
$appimagemap = "$appcache/$person.map";

echo "appfilename = $appfilename<br />";
if (file_exists($appfilename)) {
  echo "SHOWING CACHED COPY<br />";
}
else
{
  echo "BUILDING NEW DOTFILE AND IMAGEMAP<br />";
  # make sure cachedir exists
  if (file_exists($appcache))
  {
    echo "cachedir - ALREADY EXISTS<br />";
  }
  else
  {
    $mkdircmd = "mkdir -p $appcache";
    echo "mkdircmd = $mkdircmd<br />";
    exec($mkdircmd);
    $chowncmd = "chown ".$host_info['ownername']." $appcache";
    echo "chowncmd = $chowncmd<br />";
    exec($chowncmd);
    echo "cachedir - CREATED<br />";
  }
  # retrieve dotfile and generate graph
  $dotfileurl = $host_info['webdir']."/dotfile.php?id=$person";
  echo "dotfileurl = $dotfileurl<br />";
  $authforcurl = ($host_info['hostname'] == "ibiblio" ? "-u army:army" : "");
  $getandgenerategraph = "curl -s $authforcurl \"$dotfileurl\" | ".$host_info['dotlocation']." -Nfontname=".$host_info['dotfontface']." -Gcharset=latin1 -Tcmapx -o$appimagemap -T".$host_info['dotfiletype']." -o$appfilename";
  echo "getandgenerategraph = [$getandgenerategraph]<br />";
  exec($getandgenerategraph);
}
?>
<br />
<img src="<?php echo $webfilename ?>" usemap="#familytree"  border="0" />
<?php echo file_get_contents($appimagemap) ?>
<br /><br />
--
<br /><br />
<a href="<?php echo $webfilename ?>"><img src="<?php echo $webfilename ?>" width="300" border="0"></a> 

</body>
</html>
