<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> tg_sendman.php </#FN>                                                  
*                         File Birth   > <!#FB> 2024/06/03 20:54:27.198 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/06/03 20:59:05.261 </#FT>                                         *
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



#узнаем текущий счетчик
$count_msg=file_get_contents('count_msg');

#узнаем кто шлёт данные
if(isset($_GET['chat_id']))  $chat_id=$_GET['chat_id']; else $chat_id="";

$sql="select max(id) from png_mod_mailman_users;";

#echo $sql;

$row_max=doFetchOneQuery($globalSS,$sql);

if($count_msg==$row_max[0]) die();


$sql="select message, u.telegram_id,t.id from png_mod_mailman_users t
INNER JOIN png_users u ON u.email=t.useremail
where t.id > $count_msg and t.id <= $row_max[0] and length(u.telegram_id)>0;";

#echo $sql;

$line=doFetchQuery($globalSS,$sql);

$msg_to_tg=array();

$msg_id=$count_msg;

$i=0;
foreach ($line as $row){

  $msg_to_tg[$i]['message'] = strip_tags($row[0],'<a>');
 # $msg_to_tg[$i]['message']=str_replace('Открыть карточку','',$msg_to_tg[$i]['message']);
  $msg_to_tg[$i]['chat_id'] = $row[1];

$i++;

$msg_id=$row[2]+1;
if($i==30)
break;
}


$messages=array();

$messages['messages']=$msg_to_tg;

$json_msg = json_encode($messages,1);

echo $json_msg;


$msg_id = $msg_id - 1;

file_put_contents('count_msg',$msg_id);




?>


