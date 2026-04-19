#!/bin/bash

#переходим в каталог
cd /var/www/html/hle2/modules/nmapit
#запускаем nmapit.pl,fetchnmap.pl
perl ./nmapit.pl
perl ./fetchnmap.pl
