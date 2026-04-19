<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.hostsSensors.php </#FN>                                      
*                         File Birth   > <!#FB> 2023/11/06 18:11:59.522 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/11/06 20:39:41.618 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD>                                                             
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/






#Функции работы с датчиками

function doPrintAllHostSensors($globalSS,$host_id){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  $host =doGetHostnameByHostId($globalSS,$host_id);

     
  $queryAll="
      SELECT
        phs.id,
        phs.sensor_type,
        phs.sensor_cmddata,
        phs.sensor_pollperiod,
        phs.sensor_polltime,
        phs.comment
      FROM
        png_hosts_sensors  phs

      WHERE phs.host='".$host."'
 
      ORDER BY sensor_type asc";

  $result = doFetchQuery($globalSS,$queryAll);

  $numrow=1;
  echo 	"<h2>".$_lang['stSENSORS'].":</h2>";
  echo "<br><br><a href='javascript:showModalPopUp2(".$globalSS['connectionParams']['srv'].",".$host_id.");'>".$_lang['stADD']."</a>";
  echo "<br /><br />";


  echo "<table class=\"datatable\" id='hostsTable'>
          <tr>
            <th ><b>#</b></th>
            <th><b>".$_lang['stSENSORTYPE']."</b></th>
            <th><b>".$_lang['stSENSORCMDDATA']."</b></th>
            <th><b>".$_lang['stSENSORPOLLPERIOD']."</b></th>
            <th><b>".$_lang['stSENSORPOLLTIME']."</b></th>
            <th><b>".$_lang['stCOMMENT']."</b></th>
            <th><b>".$_lang['stEDIT']."</b></th>
            <th><b>".$_lang['stACTION']."</b></th>
           </tr>
  ";

 foreach($result as $line) {

  

   echo "
   <tr   >
   
     <td>".$numrow."</td>
     <td align=left>".$line[1]."&nbsp;</td>
     <td align=left>".$line[2]."&nbsp;</td>
     <td align=left>".$line[3]."&nbsp;</td>
     <td align=left>".$line[4]."&nbsp;</td>
     <td align=left>".$line[5]."&nbsp;</td>
     <td align=center><a href='javascript:showModalPopUp3(".$globalSS['connectionParams']['srv'].",".$line[0].");'>".$_lang['stEDIT']."</a></td>
     <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=12&actid=5&valid=".$line[0]."&host_id=".$host_id.">".$_lang['stDELETE']."</a></td>

   </tr>
   ";
   $numrow++;
  }  //end while
      
echo "</table>";
 echo "<br />";
 echo "<br><br><a href='javascript:showModalPopUp2(".$globalSS['connectionParams']['srv'].",".$host_id.");'>".$_lang['stADD']."</a>";
 echo "<br />";
  
  }


 




  function doPrintFormAddHostSensor($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
    $host_id=$_GET['host_id'];
    
  
  
    $_lang = $globalSS['lang'];
  
          echo "<h2>".$_lang['stADD']."</h2>";
         echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=12&actid=2&host_id='.$host_id.'" method="post">
       <table class=datatable>
       <tr><td><b>'.$_lang['stSENSORTYPE'].'</b></td><td>       <input type="text" name="field_sensortype" >
       <tr><td><b>'.$_lang['stSENSORCMDDATA'].'</b></td><td>       <input type="text" name="field_sensorcmddata" >
       <tr><td><b>'.$_lang['stSENSORPOLLPERIOD'].'</b></td><td>       <input type="text" name="field_sensorpollperiod" >
       <tr><td><b>'.$_lang['stSENSORPOLLTIME'].'</b></td><td>       <input type="text" name="field_sensorpolltime" >
       <tr><td><b>'.$_lang['stCOMMENT'].'</b></td><td><input type="text" name="field_comment" ></td></tr>
   
       
       </table>
       <br />
       ';
  
       echo '
       <input type="submit" value="'.$_lang['stADD'].'" name="btnsaveandexit"><br />
       </form>
       <br />';
       
         }
       

 
 function doHostAddSensor($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  $host_id=$_GET['host_id'];

  $host =doGetHostnameByHostId($globalSS,$host_id);

  if(!isset($_POST['field_sensortype'])) $field_sensortype="";  else $field_sensortype=$_POST['field_sensortype'];
  if(!isset($_POST['field_sensorcmddata'])) $field_sensorcmddata="";  else $field_sensorcmddata=$_POST['field_sensorcmddata'];
  if(!isset($_POST['field_sensorpollperiod'])) $field_sensorpollperiod="";  else $field_sensorpollperiod=$_POST['field_sensorpollperiod'];
  if(!isset($_POST['field_sensorpolltime'])) $field_sensorpolltime="";  else $field_sensorpolltime=$_POST['field_sensorpolltime'];
  if(!isset($_POST['field_comment'])) $field_comment="";  else $field_comment=$_POST['field_comment'];

if($field_sensorpollperiod=="") $field_sensorpollperiod = "null";

  $sql="INSERT INTO png_hosts_sensors (
    host,
    sensor_type,
    sensor_cmddata,
    sensor_pollperiod,
    sensor_polltime,
    comment
          

    ) 
    VALUES
    (
      '".$host."',
      '".$field_sensortype."',
      '".$field_sensorcmddata."',
      ".$field_sensorpollperiod.",
      '".$field_sensorpolltime."',
      '".$field_comment."'
      
    )";



    if (!doQuery($globalSS, $sql)) {
      die('Error: Cant add one host sensor ');
      }
  
      echo "
    
      ".$_lang['stCLOSEWINDOWMSG']." 
      <button type=\"button\" onclick=\"window.close();\">".$_lang['stCLOSEWINDOW']."</button>";

      echo '
      <script>
                window.onunload = refreshParent;
            function refreshParent() {
                window.opener.location.reload();
                }
              </script>
      ';

  }




  function doPrintFormHostEditSensor($globalSS){
       
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
      
    $_lang = $globalSS['lang'];
    
    $sensor_id=$_GET['sensor_id'];
   


    

    $queryOne="select
    id, 
    sensor_type,
    sensor_cmddata,
    sensor_pollperiod,
    sensor_polltime,
    comment
    from png_hosts_sensors p
    where p.id=".$sensor_id."";
    
    

    $line=doFetchOneQuery($globalSS,$queryOne);
       
    
    echo "<h2>".$_lang['stEDIT']."</h2>";
    echo '
    <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=12&actid=4&sensor_id='.$sensor_id.'" method="post">
    <table class=datatable>
      <tr><td><b>'.$_lang['stSENSORTYPE'].'</b></td><td><input type="text"                   value=\''.$line[1].'\' name="field_sensortype"></td></tr>
      <tr><td><b>'.$_lang['stSENSORCMDDATA'].'</b></td><td><input type="text"       value=\''.$line[2].'\'         name="field_sensorcmddata"></td></tr>
      <tr><td><b>'.$_lang['stSENSORPOLLPERIOD'].'</b></td><td><input type="text"                  value=\''.$line[3].'\' name="field_sensorpollperiod" ></td></tr>
      <tr><td><b>'.$_lang['stSENSORPOLLTIME'].'</b></td><td><input type="text"       value=\''.$line[4].'\' name="field_sensorpolltime"></td></tr>
      <tr><td><b>'.$_lang['stCOMMENT'].'</b></td><td><input type="text"        value=\''.$line[5].'\' name="field_comment" ></td></tr>
          
    </table>
    <br />
    ';
    


    echo '
    <input type="submit" value="'.$_lang['stSAVE'].'"><br />
    </form>
    <br />';
    
 
     
    }
    
    function doHostSaveSensor($globalSS){
    
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    

    
    $_lang = $globalSS['lang'];
    
    $sensor_id = $_GET['sensor_id'];
    
    if(!isset($_POST['field_sensortype'])) $field_sensortype="";  else $field_sensortype=$_POST['field_sensortype'];
    if(!isset($_POST['field_sensorcmddata'])) $field_sensorcmddata="";  else $field_sensorcmddata=$_POST['field_sensorcmddata'];
    if(!isset($_POST['field_sensorpollperiod'])) $field_sensorpollperiod="";  else $field_sensorpollperiod=$_POST['field_sensorpollperiod'];
    if(!isset($_POST['field_sensorpolltime'])) $field_sensorpolltime="";  else $field_sensorpolltime=$_POST['field_sensorpolltime'];
    if(!isset($_POST['field_comment'])) $field_comment="";  else $field_comment=$_POST['field_comment'];
 


    if(strlen($field_sensorpollperiod)==0) $field_sensorpollperiod='null';

    $queryUpdateOne="UPDATE  png_hosts_sensors 
      set 
      sensor_type ='$field_sensortype',
      sensor_cmddata ='$field_sensorcmddata',
      sensor_pollperiod =$field_sensorpollperiod,
      sensor_polltime ='$field_sensorpolltime',
      comment ='$field_comment'
      
      where id = ".$sensor_id."  
        ";
    
 
    
    echo $queryUpdateOne;
    
    if (!doQuery($globalSS, $queryUpdateOne)) {
      die('Error: Cant update one sensor');
    }
    
    echo "
    
      ".$_lang['stCLOSEWINDOWMSG']." 
      <button type=\"button\" onclick=\"window.close();\">".$_lang['stCLOSEWINDOW']."</button>";

      echo '
      <script>
                window.onunload = refreshParent;
            function refreshParent() {
                window.opener.location.reload();
                }
              </script>
      ';
    
    
    
    }
  
  #удаление
  function doHostDeleteSensor($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
 
    $_lang = $globalSS['lang'];
    
    $valid = $_GET['valid'];
    
    $host_id = $_GET['host_id'];
    
    #удаляем сервис
    $queryDeleteOne="delete from png_hosts_sensors where id='".$valid."'";
    
    if (!doQuery($globalSS, $queryDeleteOne)) {
    die('Error: Cant delete one host sensor');
    }

    
    echo "".$_lang['stDELETE']."<br /><br />";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=3&hostid=".$host_id." target=right>К списку</a><br />";
    
    
    }

?>