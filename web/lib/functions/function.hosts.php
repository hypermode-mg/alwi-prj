<?php
#Функции работы с хостами
function doPrintAllHosts($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];

       
    $queryAllHosts="
        SELECT 
          id,
          name,
          active,
          type,
          devname,
          vendor,
          location,
          model,
          '',
          comment,
          linktoadm,
          os,
          IFNULL((select name from png_groups pg,png_hostingroup phg where p.id=phg.host_id and phg.group_id=pg.id),'nosite') sitename,
          IFNULL((select pg.id from png_groups pg,png_hostingroup phg where p.id=phg.host_id and phg.group_id=pg.id),'nosite') siteid
          
          

       FROM png_hosts p
       ORDER BY sitename asc, INET_ATON(name) asc";

    $result = doFetchQuery($globalSS,$queryAllHosts);

    $numrow=1;
  

    echo 	"<h2>".$_lang['stHOSTS'].":</h2>";
    echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'hostsTable\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
    echo '<button id="ClearFilterBtn" type="button" onclick="ClearFilter(\'hostsTable\');">'.$_lang['stCLEARFILTER'].'</button>';
 echo "<br><br><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=1>".$_lang['stADD']."</a>";
    echo "<br /><br />";
    echo "<table class=\"datatable\" id='hostsTable'>
            <tr>
              <th class=unsortable>
              <button type=\"button\" id=\"tglAllRows\" aria-expanded=\"false\" onclick=\"toggleAllRows();\" >+</button>
              <b>#</b></th>
              <th><b>".$_lang['stADMINPANEL']."</b></th>
              <th><b>".$_lang['stIPADDRESS']."</b></th>
              <th ><b>".$_lang['stISPOLLACTIVE']."</b></th>
             <th><b>".$_lang['stHOSTTYPE']."</b></th>
             <th><b>".$_lang['stHOSTNAME']."</b></th>
             <th><b>".$_lang['stHOSTVENDOR']."</b></th>
             <th><b>".$_lang['stHOSTMODEL']."</b></th>
             <th><b>".$_lang['stHOSTOS']."</b></th>
             <th><b>".$_lang['stHOSTLOCATION']."</b></th>
             <th><b>".$_lang['stCOMMENT']."</b></th>
             <th><b>".$_lang['stDELETE']."</b></th>
             </tr>
    ";

$groupname="";

   foreach($result as $line) {
     if($line[2]=="1")
       $line[2]="<b><font color=green>".$_lang['stENABLED']."</font></b>";
     else
       $line[2]="<b><font color=red>".$_lang['stDISABLED']."</font></b>";
     
     if($groupname<>$line[12]){
      echo "<tr><td colspan=12> 
      <button type=\"button\" id=\"tgl".$line[13]." \" aria-expanded=\"false\" onclick=\"toggleRows(this.id,'siteid".$line[13]."');\" >+</button>
    ".$line[12]."</td></tr>";
      $groupname=$line[12];

     }
       echo "
     <tr class='hidden siteid".$line[13]."'>
       <td>".$numrow."</td>
       <td align=center><a href=\"".$line[10]."\" target=\"_blank\">".$_lang['stOPEN']."</a></td>
 
       <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=3&hostid=".$line[0].">".$line[1]."&nbsp;</a></td>
       <td align=center>".$line[2]."</td>
       <td>".$line[3]."&nbsp;</td>
       <td>".$line[4]."&nbsp;</td>
       <td>".$line[5]."&nbsp;</td>
       <td>".$line[7]."&nbsp;</td>
       <td>".$line[11]."&nbsp;</td>
       <td>".$line[6]."&nbsp;</td>
       <td>".$line[9]."&nbsp;</td>
      
       <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=5&hostid=".$line[0].">DELETE</a></td>

     </tr>
     ";
     $numrow++;
    }  //end while
        
echo "</table>";
   echo "<br />";
   echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=1>".$_lang['stADD']."</a>";
   echo "<br />";
    
    }


function doPrintFormAddHost($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');

  $m_host="";
  $m_hostname="";

    if(!isset($GET['modal'])) 
    {
      $m_host="value='".$_GET['m_host']."'";
      $m_hostname="value='".$_GET['m_hostname']."'";
    }

    doPrintAllItemsAsDatalist($globalSS,"model");
    doPrintAllItemsAsDatalist($globalSS,"vendor");
    doPrintAllItemsAsDatalist($globalSS,"devtype");
    doPrintAllItemsAsDatalist($globalSS,"os");
    doPrintAllItemsAsDatalist($globalSS,"groups");

    $_lang = $globalSS['lang'];

          echo "<h2>".$_lang['stADD']."</h2>";
         echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=1&actid=2" method="post">
       <table class=datatable>
       
       <tr><td><b>'.$_lang['stIPADDRESS'].'</b></td><td><input type="text" '.$m_host.' name="field_name"></td></tr>
       <tr><td><b>'.$_lang['stISPOLLACTIVE'].'</b></td><td><input type="checkbox" name="field_active"></td></tr>
       <tr><td><b>'.$_lang['stHOSTTYPE'].'</b></td><td><input type="text" name="field_type" list="png_listdevicetypes"></td></tr>
       <tr><td><b>'.$_lang['stHOSTNAME'].'</b></td><td><input type="text" '.$m_hostname.' name="field_devname" ></td></tr>
       <tr><td><b>'.$_lang['stHOSTVENDOR'].'</b></td><td><input type="text" name="field_vendor" list="png_listvendors"></td></tr>
       <tr><td><b>'.$_lang['stHOSTMODEL'].'</b></td><td><input type="text" name="field_model" list="png_listmodels" ></td></tr>
       <tr><td><b>'.$_lang['stHOSTOS'].'</b></td><td><input type="text" name="field_os" list="png_listos" ></td></tr>
       <tr><td><b>'.$_lang['stHOSTLOCATION'].'</b></td><td><input type="text" name="field_location"></td></tr>
       <tr><td><b>'.$_lang['stCOMMENT'].'</b></td><td><input type="text" name="field_comment"></td></tr>
       <tr><td><b>'.$_lang['stADMINPANEL'].'</b></td><td><input type="text" name="field_linktoadm"></td></tr>
       <tr><td><b>'.$_lang['stGROUP'].'</b></td><td><input type="text" name="field_group" list="png_groups"></td></tr>

       
       </table>
       <br />
       ';
       if(isset($_GET['modal'])) 
       {
        echo '<input type="hidden" name="field_ismodal" value="1">
        <script>
                  window.onunload = refreshParent;
              function refreshParent() {
                  window.opener.location.reload();
                  }
                </script>
        ';
       }
         echo '
           <input type="submit" value="'.$_lang['stHOSTADD'].'"><br />
           </form>
           <br />';
       
         }
       
       
       function doHostAdd($globalSS){
       
         include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
         
         $_lang = $globalSS['lang'];
       
  
       
         if(!isset($_POST['field_name'])) $field_name="";  else $field_name=$_POST['field_name'];
         if(!isset($_POST['field_active'])) $field_active="0";  else $field_active="1";
         if(!isset($_POST['field_type'])) $field_type="";  else $field_type=$_POST['field_type'];
         if(!isset($_POST['field_devname'])) $field_devname="";  else $field_devname=$_POST['field_devname'];
         if(!isset($_POST['field_vendor'])) $field_vendor="";  else $field_vendor=$_POST['field_vendor'];
         if(!isset($_POST['field_model'])) $field_model="";  else $field_model=$_POST['field_model'];
         if(!isset($_POST['field_os'])) $field_os="";  else $field_os=$_POST['field_os'];
         if(!isset($_POST['field_location'])) $field_location="";  else $field_location=$_POST['field_location'];
         if(!isset($_POST['field_comment'])) $field_comment="";  else $field_comment=$_POST['field_comment'];
         if(!isset($_POST['field_linktoadm'])) $field_linktoadm="";  else $field_linktoadm=$_POST['field_linktoadm'];
         if(!isset($_POST['field_group'])) $field_group="";  else $field_group=$_POST['field_group'];        
        
         $sql="INSERT INTO png_hosts (
          name,
          active,
          type,
          devname,
          vendor,
          model,
          os,
          location,
          comment,
          linktoadm
   
          ) VALUES (
           '$field_name',
           '$field_active',
           '$field_type',
           '$field_devname',
           '$field_vendor',
           '$field_model',
           '$field_os',
           '$field_location',
           '$field_comment',
           '$field_linktoadm'
          )";
  
       
        if (!doQuery($globalSS, $sql)) {
         die('Error: Can`t insert new host');
        }

        $queryGetGroupId="SELECT ifnull(id,0) FROM png_groups g where g.name='".$field_group."'";
  
        $line=doFetchOneQuery($globalSS,$queryGetGroupId);

        if($line[0]>0){


          $queryOne="select
          id 
          from png_hosts p
          where p.name='".$field_name."'";
          
          $hostid=doFetchOneQuery($globalSS,$queryOne);

          $queryInsertHostGroupId="INSERT INTO png_hostingroup (group_id, host_id) VALUES ($line[0],$hostid[0])";
    
          if (!doQuery($globalSS, $queryInsertHostGroupId)) {
            die('Error: Cant insert one host in group');
          }
         }

        $sql="INSERT INTO png_pingstat (date,host,result) VALUES (unix_timestamp(sysdate()),'$field_name',0)";
        if (!doQuery($globalSS, $sql)) {
          die('Error: Can`t insert new host');
         }
         echo "".$_lang['stADDED']."<br /><br />";

         if(isset($_POST['field_ismodal']))
         echo "
         ".$_lang['stCLOSEWINDOWMSG']." 
         <button type=\"button\" onclick=\"window.open('', '_self', ''); window.close();\"> ".$_lang['stCLOSEWINDOW']."</button>";
         else 

         echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1 target=right> ".$_lang['stBACK']."</a>";
 
       }
       
       
       
       
       
       function doPrintFormHostEdit($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
         
       $_lang = $globalSS['lang'];
       
       $hostid=$_GET['hostid'];
       
       $queryOne="select
       id, 
       name,
       active,
       type,
       devname,
       vendor,
       location,
       model,
       '',
       comment,
       linktoadm,
       (select g.name from png_hostingroup hp,png_groups g where hp.group_id=g.id and hp.host_id=".$hostid.") group_id,
       os
       from png_hosts p
       where p.id=".$hostid."";
       
       
       

       $line=doFetchOneQuery($globalSS,$queryOne);
       
       if($line[2] == 1) $line[2]="checked"; else $line[2]="";
       
       doPrintAllItemsAsDatalist($globalSS,"model");
       doPrintAllItemsAsDatalist($globalSS,"vendor");
       doPrintAllItemsAsDatalist($globalSS,"devtype");
       doPrintAllItemsAsDatalist($globalSS,"groups");
       doPrintAllItemsAsDatalist($globalSS,"os");

       echo "<h2>".$_lang['stEDIT']."</h2>";
       echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=1&actid=4&hostid='.$hostid.'" method="post">
       <table class=datatable>
         <tr><td><b>'.$_lang['stIPADDRESS'].'</b></td><td><input type="text"                   value=\''.$line[1].'\' name="field_name"></td></tr>
         <tr><td><b>'.$_lang['stISPOLLACTIVE'].'</b></td><td><input type="checkbox"       '.$line[2].'         name="field_active"></td></tr>
         <tr><td><b>'.$_lang['stHOSTTYPE'].'</b></td><td><input type="text"                  value=\''.$line[3].'\' name="field_type" list="png_listdevicetypes"></td></tr>
         <tr><td><b>'.$_lang['stHOSTNAME'].'</b></td><td><input type="text"       value=\''.$line[4].'\' name="field_devname"></td></tr>
         <tr><td><b>'.$_lang['stHOSTVENDOR'].'</b></td><td><input type="text"        value=\''.$line[5].'\' name="field_vendor" list="png_listvendors"></td></tr>
         <tr><td><b>'.$_lang['stHOSTMODEL'].'</b></td><td><input type="text"                value=\''.$line[7].'\' name="field_model" list="png_listmodels"></td></tr>
         <tr><td><b>'.$_lang['stHOSTOS'].'</b></td><td><input type="text" value=\''.$line[12].'\' name="field_os" list="png_listos"></td></tr>
         <tr><td><b>'.$_lang['stHOSTLOCATION'].'</b></td><td><input type="text"      value=\''.$line[6].'\' name="field_location"></td></tr>
         <tr><td><b>'.$_lang['stCOMMENT'].'</b></td><td><input type="text"          value=\''.$line[9].'\' name="field_comment"></td></tr>
         <tr><td><b>'.$_lang['stADMINPANEL'].'</b></td><td><input type="text"    value=\''.$line[10].'\' name="field_linktoadm"></td></tr>
         <tr><td><b>'.$_lang['stGROUP'].'</b></td><td><input type="text"                 value=\''.$line[11].'\' name="field_group" list="png_groups"></td></tr>
              
       </table>
       <br />
       ';
       


       echo '
       <input type="submit" value="'.$_lang['stHOSTSAVE'].'"><br />
       </form>
       <br />';
       
       echo '
          <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=1&actid=5&hostid='.$hostid.'" method="post">
          <input type="submit" value="'.$_lang['stHOSTDELETE'].'" ><br />
          </form>
          <br />';
       
          doPrintAllHostServices($globalSS,$hostid);

          doPrintAllHostSensors($globalSS,$hostid);
       
       }
       
       function doHostSave($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       
       
       
       $_lang = $globalSS['lang'];
       
       $hostid = $_GET['hostid'];
       
       if(!isset($_POST['field_name'])) $field_name="";  else $field_name=$_POST['field_name'];
       if(!isset($_POST['field_active'])) $field_active="0";  else $field_active="1";
       if(!isset($_POST['field_type'])) $field_type="";  else $field_type=$_POST['field_type'];
       if(!isset($_POST['field_devname'])) $field_devname="";  else $field_devname=$_POST['field_devname'];
       if(!isset($_POST['field_vendor'])) $field_vendor="";  else $field_vendor=$_POST['field_vendor'];
       if(!isset($_POST['field_model'])) $field_model="";  else $field_model=$_POST['field_model'];
       if(!isset($_POST['field_os'])) $field_os="";  else $field_os=$_POST['field_os'];
       if(!isset($_POST['field_location'])) $field_location="";  else $field_location=$_POST['field_location'];
       if(!isset($_POST['field_comment'])) $field_comment="";  else $field_comment=$_POST['field_comment'];
       if(!isset($_POST['field_linktoadm'])) $field_linktoadm="";  else $field_linktoadm=$_POST['field_linktoadm'];
       if(!isset($_POST['field_group'])) $field_group="";  else $field_group=$_POST['field_group'];


       $queryGetGroupId="SELECT ifnull(id,0) FROM png_groups g where g.name='".$field_group."'";

       $line=doFetchOneQuery($globalSS,$queryGetGroupId);
       
       if($line[0]>0){
$queryDeleteHostGroupId="delete from png_hostingroup where host_id=".$hostid."";
doQuery($globalSS,$queryDeleteHostGroupId);

}

if($field_group!=""){
        $queryInsertHostGroupId="INSERT INTO png_hostingroup (group_id, host_id) VALUES ($line[0],$hostid)";

  
        if (!doQuery($globalSS, $queryInsertHostGroupId)) {
          die('Error: Cant update one host in group');
        }
      }

       $queryUpdateOne="UPDATE  png_hosts p
         set 
         name ='$field_name',
         active ='$field_active',
         type ='$field_type',
         devname ='$field_devname',
         vendor ='$field_vendor',
         model ='$field_model',
         os ='$field_os',
         location ='$field_location',
         comment ='$field_comment',
         linktoadm ='$field_linktoadm'
         
         
     
         where p.id = ".$hostid."  
           ";
       
    
       
       
       
       if (!doQuery($globalSS, $queryUpdateOne)) {
         die('Error: Cant update one host');
       }
       
       echo "".$_lang['stUPDATED']."<br /><br />";
       echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1 target=right>".$_lang['stBACK']."</a>";
       echo "&nbsp;|&nbsp;<a href=reports/reports.php?id=5 target=right>Dashboard</a>";
       
       
       
       }
       
       function doHostDelete($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       
       $_lang = $globalSS['lang'];
       
       $hostid = $_GET['hostid'];
       
       $host = doGetHostnameByHostId($globalSS,$hostid);
       
       #удаляем хост
       $queryDeleteOne="delete from png_hosts where id='".$hostid."'";
       
       if (!doQuery($globalSS, $queryDeleteOne)) {
       die('Error: Cant delete one host');
       }

       #удаляем хост из групп (если такие были)
       $queryDeleteOneFromGroup="delete from png_hostingroup where host_id='".$hostid."'";
       
       if (!doQuery($globalSS, $queryDeleteOneFromGroup)) {
       die('Error: Cant delete one host from group');
       }

       #удаляем сервисы хоста (если такие были)
       $queryDeleteOneFromServices="delete from png_hosts_services where host='".$host."'";
       
       if (!doQuery($globalSS, $queryDeleteOneFromServices)) {
       die('Error: Cant delete host services ');
       }
       
       #удаляем сенсоры хоста (если такие были)
       $queryDeleteOneFromSensors="delete from png_hosts_sensors where host='".$host."'";
       
       if (!doQuery($globalSS, $queryDeleteOneFromSensors)) {
       die('Error: Cant delete host sensors ');
       }
       
       
       echo "".$_lang['stDELETED']."<br /><br />";
       echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=1 target=right>".$_lang['stBACK']."</a><br />";
       
       
       }


       function doGetHostnameByHostId($globalSS,$host_id){
       
        include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
        
        $_lang = $globalSS['lang'];
        
       
        
        $queryGetOne="select name from png_hosts where id=".$host_id."";
      
      
      $row=doFetchOneQuery($globalSS,$queryGetOne);

      return $row[0];
        
        }


?>