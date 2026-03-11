<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.hostsservices.php </#FN>                                      
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






#Функции работы с портами хостов

function doPrintAllHostServices($globalSS,$host_id){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  $host =doGetHostnameByHostId($globalSS,$host_id);

     
  $queryAll="
      SELECT
        phs.id,
        phs.host,
        phs.port,
        phs.comment
      FROM
        png_hosts_services  phs

      WHERE phs.host='".$host."'
 
      ORDER BY port asc";

  $result = doFetchQuery($globalSS,$queryAll);

  $numrow=1;
  echo 	"<h2>".$_lang['stSERVICES'].":</h2>";
  echo "<br><br><a href='javascript:showModalPopUp(".$globalSS['connectionParams']['srv'].",".$host_id.");'>".$_lang['stADD']."</a>";
  echo "<br /><br />";


  echo "<table class=\"datatable\" id='ServiceTable'>
          <tr>
            <th ><b>#</b></th>
            <th><b>".$_lang['stPORT']."</b></th>
            <th><b>".$_lang['stCOMMENT']."</b></th>
            <th><b>".$_lang['stACTION']."</b></th>
           </tr>
  ";

 foreach($result as $line) {

  

   echo "
   <tr   >
   
     <td>".$numrow."</td>
     <td align=left>".$line[2]."&nbsp;</td>
     <td align=left>".$line[3]."&nbsp;</td>
     <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=11&actid=5&valid=".$line[0]."&host_id=".$host_id.">".$_lang['stDELETE']."</a></td>

   </tr>
   ";
   $numrow++;
  }  //end while
      
echo "</table>";
 echo "<br />";
 echo "<br><br><a href='javascript:showModalPopUp(".$globalSS['connectionParams']['srv'].",".$host_id.");'>".$_lang['stADD']."</a>";
 echo "<br />";
  
  }


 




  function doPrintFormAddHostService($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
    $host_id=$_GET['host_id'];
    
  
  
    $_lang = $globalSS['lang'];
  
          echo "<h2>".$_lang['stADD']."</h2>";
         echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=11&actid=2&host_id='.$host_id.'" method="post">
       <table class=datatable>
       <tr><td><b>'.$_lang['stPORT'].'</b></td><td>       <input type="text" name="field_port" >
       <tr><td><b>'.$_lang['stCOMMENT'].'</b></td><td><input type="text" name="field_comment" ></td></tr>
   
       
       </table>
       <br />
       ';
  
       echo '
       <input type="submit" value="'.$_lang['stADD'].'" name="btnsaveandexit"><br />
       </form>
       <br />';
       
         }
       

 
 function doHostAddService($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  $host_id=$_GET['host_id'];

  $host =doGetHostnameByHostId($globalSS,$host_id);

  if(!isset($_POST['field_port'])) $field_port="";  else $field_port=$_POST['field_port'];
  if(!isset($_POST['field_comment'])) $field_comment="";  else $field_comment=$_POST['field_comment'];



  $sql="INSERT INTO png_hosts_services (
    host,
    port,
    comment
          

    ) 
    VALUES
    (
      '".$host."',
      '".$field_port."',
      '".$field_comment."'
      
    )";



    if (!doQuery($globalSS, $sql)) {
      die('Error: Cant add one host service ');
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
  function doHostDeleteService($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
 
    $_lang = $globalSS['lang'];
    
    $valid = $_GET['valid'];
    
    $host_id = $_GET['host_id'];
    
    #удаляем сервис
    $queryDeleteOne="delete from png_hosts_services where id='".$valid."'";
    
    if (!doQuery($globalSS, $queryDeleteOne)) {
    die('Error: Cant delete one host service');
    }

    
    echo "".$_lang['stDELETE']."<br /><br />";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=3&hostid=".$host_id." target=right>К списку</a><br />";
    
    
    }

?>