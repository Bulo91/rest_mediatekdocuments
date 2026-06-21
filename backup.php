<?php
exec("sh /srv/disk13/4748356/www/mediatekdocuments.myartsonline.com/savebdd/backup.sh 2>&1", $output, $returnCode);

echo "Code retour : " . $returnCode . "<br>";
echo "<pre>";
print_r($output);
echo "</pre>";
?>
