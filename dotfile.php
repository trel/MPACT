<?php

require 'envsetup.php';

if (!$_GET['id'])
{
  echo "NO ID";
}
else
{
  $a = find_person($_GET['id']);
  if ($a == null)
  {
    echo "BAD ID";
  }
  else
  {
    echo "digraph familytree\n";
    echo "{\n";
    echo "rankdir=\"LR\"\n";
    echo "node [fontname = Times, fontsize=10, shape = rect, height=.15]\n";
    # ancestors
    $upgroup = array();
    $upgroup[] = $_GET['id'];
    $ancestors = find_ancestors_for_group($upgroup);
    foreach ($ancestors as $one)
    {
      $person = find_person($one);
      echo "$one [label = \"".$person['fullname']."\" URL=\"mpact.php?op=show_tree&id=".$one."\"];\n";
      $advisors = find_advisors_for_person($one);
      foreach ($advisors as $adv)
      {
        echo "$adv -> $one;\n";
      }
    }
    # descendents
    $downgroup = array();
    $downgroup[] = $_GET['id'];
    $descendents = find_descendents_for_group($downgroup);
    foreach ($descendents as $one)
    {
      $person = find_person($one);
      echo "$one [label = \"".$person['fullname']."\" URL=\"mpact.php?op=show_tree&id=".$one."\"";
      if ($one == $_GET['id']) echo " color=\"red\" style=\"filled\" fillcolor=\"grey\"";
      echo "];\n";
      $advisees = find_advisorships_under_person($one);
      foreach ($advisees as $adv)
      {
        echo "$one -> $adv;\n";
      }
    }
    echo "}\n";
  }
}
?>
