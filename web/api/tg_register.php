<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> tg_register.php </#FN>                                                 
*                         File Birth   > <!#FB> 2024/03/23 11:23:57.347 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/06/03 20:58:34.037 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/




include('../config.php');

if(isset($_GET['srv']))
  $srv=$_GET['srv'];
else
  $srv=0;



#узнаем кто шлёт данные
if(isset($_GET['chat_id']))  $chat_id=$_GET['chat_id']; else $chat_id="";
if(isset($_GET['useremail']))  $useremail=$_GET['useremail']; else $useremail="";


$sql="select count(1) from png_users where email='$useremail'";

$row=doFetchOneQuery($globalSS,$sql);

#echo $row[0];

$answer=array();

if($row[0]==1){
  $sql="update png_users set telegram_id=".$chat_id." where email='$useremail'";

doQuery($globalSS,$sql);
$status = "ok";  

}
else
{
$status = "error";  
}

#echo $path;

$answer['status']=$status;

$json_status=json_encode($answer);

echo $json_status;

$userdata=array();

$userdata['chat_id']=$chat_id;
$userdata['useremail']=$useremail;

file_put_contents('regdata',$userdata);




?>


