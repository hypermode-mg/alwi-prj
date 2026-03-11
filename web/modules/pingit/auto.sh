#!/bin/bash

#переходим в каталог
cd /var/www/html/hle2/modules/pingit
#запускаем pingit.pl,fetch.pl

if [ -f "pngstat.log" ]; then

file="count.cnt"

if [ -e ${file} ]; then
    count=$(cat ${file})
else
    count=0
fi


((count++))

echo ${count} > ${file}

tries=5

if ((count > tries)); then

rm pngstat.log
rm count.cnt
fi

exit 1;

    else
    perl ./pingit.pl
    perl ./fetch.pl
    rm count.cnt
fi

