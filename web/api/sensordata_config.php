<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> index.php </#FN>                                                       
*                         File Birth   > <!#FB> 2022/10/11 21:08:24.942 </#FB>                                         *
*                         File Mod     > <!#FT> 2022/10/11 21:10:15.548 </#FT>                                         *
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



#узнаем кто шлёт данные
if(isset($_GET['host']))  $host=$_GET['host']; else $host="";
if(isset($_GET['sensor_version']))  $sensor_version=$_GET['sensor_version']; else $sensor_version="";

#сообщим версию сенсора
$sql="update png_hosts_sensors set sensor_version='$sensor_version' where host='$host';";

doQuery($globalSS,$sql);

$sql="select id, sensor_type, sensor_cmddata,ifnull(sensor_pollperiod,''), sensor_polltime,lastpolldate from png_hosts_sensors t
where t.host='$host';";

#echo $sql;

$line=doFetchQuery($globalSS,$sql);

$msg_to_sensor=array();


$i=0;
foreach ($line as $row){

  $msg_to_sensor[$i]['sensor_id'] = $row[0];
  $msg_to_sensor[$i]['sensor_type'] = $row[1];
  $msg_to_sensor[$i]['sensor_cmddata'] = $row[2];
  $msg_to_sensor[$i]['sensor_pollperiod'] = $row[3];
  $msg_to_sensor[$i]['sensor_polltime'] = $row[4];
  $msg_to_sensor[$i]['sensor_lastpolldate'] = $row[5];
  $msg_to_sensor[$i]['sensor_value'] = 0;



$i++;

}


$sensors=array();

$sensors['sensors']=$msg_to_sensor;

$json_msg = json_encode($sensors,1);

echo $json_msg;




?>


