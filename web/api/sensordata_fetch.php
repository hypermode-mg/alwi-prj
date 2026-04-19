<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> sensordata_fetch.php </#FN>                                            
*                         File Birth   > <!#FB> 2024/06/03 20:54:00.844 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/06/03 20:58:04.802 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/






include('apikey.php');



if(!isset($_GET['authkey']))
  exit;

if(isset($_GET['authkey']) and $_GET['authkey']!=$apikey)
  exit;


include('../config.php');

if(isset($_GET['srv']))
  $srv=$_GET['srv'];
else
  $srv=0;

#если нет sensor_id нечего исполнять
if(!isset($_GET['sensor_id'])) die();

if(!isset($_GET['sensor_value'])) die();

#узнаем кто шлёт данные
if(isset($_GET['sensor_id']))  $sensor_id=$_GET['sensor_id']; else $sensor_id="";
if(isset($_GET['sensor_value']))  $sensor_value=$_GET['sensor_value']; else $sensor_value="";



$sql="select count(1) from png_hosts_sensors t
where t.id='$sensor_id';";

#echo $sql;

$line=doFetchOneQuery($globalSS,$sql);

#если датчика с таким номером нет, то нечего исполнять
if($line[0]==0) die();

else

$curtimestamp=microtime(true);

$sql="UPDATE  png_hosts_sensors  set prevvalue=curvalue
where id='$sensor_id';";

doQuery($globalSS,$sql);


$sql="UPDATE  png_hosts_sensors  set curvalue='".$sensor_value."', lastpolldate='".$curtimestamp."'
where id='$sensor_id';";

doQuery($globalSS,$sql);
#echo $sql;



?>


