<?php

$cfg_db_server   = 'localhost';
$cfg_db_user     = 'root';
$cfg_db_password = '123';
$cfg_db_name     = 'sitedof';

$begin = time();
echo 'Begin '.$begin.'<br>';
echo 'Start '.(time() - $begin).'<br>';
flush();
sleep(rand(3,6));
echo 'Afret all '.(time() - $begin).'<br>';
flush();
die;

mysql_connect($cfg_db_server, $cfg_db_user, $cfg_db_password) or die('Cannot connect to MySQL');
mysql_select_db($cfg_db_name);
mysql_query('SET character_set_results="utf8"');
mysql_query('SET CHARACTER SET "utf8"');
mysql_query('SET NAMES "utf8"');


echo 'After connect '.(time() - $begin).'<br>';
flush();


$q = mysql_query("SELECT GET_LOCK('task_lock4', 1) AS l;");


echo 'Afret query '.(time() - $begin).'<br>';
flush();


while ($row = mysql_fetch_array($q)) {
 var_dump($row);
 echo '<br>';
 flush();
 if ($row['l'] == 1) {
     sleep(10);
     mysql_query('SELECT RELEASE_LOCK();');
 }
}

mysql_close();

echo 'Afret all '.(time() - $begin).'<br>';
flush();
