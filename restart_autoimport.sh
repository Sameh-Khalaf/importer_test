#!/bin/bash


pid=$(pgrep -f "php /traveloffice/var/www/importer/artisan autoimport")


if [[ -n "$pid" ]]; then

  kill $pid
else
  echo "autoumport is not running"
fi