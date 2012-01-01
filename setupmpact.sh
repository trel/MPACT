#!/bin/sh
echo "setting up permissions..."
chmod 777 dotgraphs
chmod 600 datadump.sql
chmod 600 droptables.sql
chmod 644 mpact_db.php
chmod 600 mpact_db.php.sample
chmod 700 setupmpact.sh
chmod 700 cron-manual.php
chmod 700 cron-regenerate-dirty.php
echo "removing cached dotgraphs..."
for a in 1 2 3 4 5 6 7 8 9
do
  rm -f dotgraphs/$a*
done
echo "done."
