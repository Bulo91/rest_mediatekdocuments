#!/bin/sh

DATE=`date -I`

rm -f /srv/disk13/4748356/www/mediatekdocuments.myartsonline.com/savebdd/bdd*

mysqldump -h fdb1033.awardspace.net -u 4748356_mediatek86 -pMediatek2026API --databases 4748356_mediatek86 --single-transaction | gzip > /srv/disk13/4748356/www/mediatekdocuments.myartsonline.com/savebdd/bddbackup_${DATE}.sql.gz
