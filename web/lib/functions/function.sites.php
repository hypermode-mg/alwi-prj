<?php

#Функции работы с группами
function doPrintAllGroups($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];
  
    $queryAllGroups="SELECT 
    png_groups.name,
    png_groups.addr,
    png_groups.id,
    
    count(png_hostingroup.host_id)
       FROM png_hostingroup 
       RIGHT OUTER JOIN png_groups ON png_groups.id=png_hostingroup.group_id
       GROUP BY png_hostingroup.group_id, png_groups.name, png_groups.id, png_groups.addr
       ORDER BY name asc;";
  
          $result=doFetchQuery($globalSS, $queryAllGroups);
          $numrow=1;
          echo 	"<h2>".$_lang['stGROUPS'].":</h2>";
          echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3&actid=1>".$_lang['stADDGROUP']."</a>";
          echo "<br /><br />";
          echo "<table id=report_table_id_group class=datatable>
          <tr>
            <th class=unsortable><b>#</b></th>
            <th><b>".$_lang['stGROUPNAME']."</b></th>
            <th class=unsortable><b>".$_lang['stADDRESS']."</b></th>
            <th><b>".$_lang['stQUANTITYHOSTS']."</b></th>
            <th><b>".$_lang['stEDIT']."</b></th>
  
          </tr>";
  
          foreach($result as $line) {
   
            echo "
              <tr>
                <td>".$numrow."</td>
                <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3&actid=6&groupid=".$line[2].">".$line[0]."&nbsp;</a></td>
                <td align=center>".$line[1]."&nbsp;</td>
                <td align=center>".$line[3]."&nbsp;</a></td>
                <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3&actid=3&groupid=".$line[2].">EDIT</a></td>
  
                </tr>";
            $numrow++;
          }
          echo "</table>";
          echo "<br />";
          echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3&actid=1>".$_lang['stADDGROUP']."</a>";
          echo "<br />";
  
        
    }
  
  
  function doPrintFormAddGroup($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];
  
  
    $queryAllHosts="select id, name, devname from png_hosts order by name asc;";
  
  
    echo "<h2>".$_lang['stFORMADDGROUP']."</h2>";
    echo '
  <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=3&actid=2" method="post">
  <table class=datatable>
   <tr><td>'.$_lang['stGROUPNAME'].':</td> <td><input type="text" name="name"></td></tr>
   <tr><td>'.$_lang['stADDRESS'].':</td> <td> <input type="text" name="address"></td></tr>
  </table>
  <br />
  <br />
      '.$_lang['stVALUE'].':<br />';
  
      $result=doFetchQuery($globalSS, $queryAllHosts);
      $numrow=1;
  
  
      echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'hostTable\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
      echo '<button id="ClearFilterBtn" type="button" onclick="ClearFilter(this);">'.$_lang['stCLEARFILTER'].'</button>';
  
    echo "<table id='hostTable' class=datatable>";
    echo "<tr>
  <th >#</th>
  <th>".$_lang['stHOST']."</th>
  <th>".$_lang['stHOSTNAME']."</th>
  <th >".$_lang['stINCLUDE']."</th>
  </tr>";
  
    foreach($result as $line) {
      echo "
        <tr>
          <td >".$numrow."</td>
          <td >".$line[1]."</td>
          <td >".$line[2]."</td>
          <td><input type='checkbox' name='hostid[]' value='".$line[0]."';</td>
        </tr>";
      $numrow++;
    }
    echo "</table>";
  
  
    echo '
      <input type="submit" value="'.$_lang['stSITEADD'].'"><br />
      </form>
      <br />';
  
    }
  
  function doAddGroup($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];
  
    if(!isset($_POST['name'])) $name="";  else $name=$_POST['name'];
  
    if(!isset($_POST['address'])) $address="";  else $address=$_POST['address'];
  
  
    $sql="INSERT INTO png_groups (name, addr) VALUES ('$name', '$address')";
  
    
    if (!doQuery($globalSS, $sql)) {
      die('Error: Can`t insert new group');
    }
  
    $sql="select id from png_groups where name='".$name."';";
    $newid=doFetchOneQuery($globalSS, $sql);
    
    
    $sql="INSERT INTO png_hostingroup (group_id, host_id) VALUES  ";
  
      foreach($_POST['hostid'] as $key=>$value)
        $sql = $sql." ('".$newid[0]."','". $value."'),";
  
      $sql=substr($sql,0,strlen($sql)-1);
      $sql=$sql.";";
   
    doQuery($globalSS, $sql);
  
    echo "".$_lang['stGROUPADDED']."<br /><br />";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3 target=right>".$_lang['stBACK']."</a>";
  
  
  
    
  }
  
  function doPrintFormEditGroup($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
      
    $_lang = $globalSS['lang'];
    $groupid=$_GET['groupid'];
  
    $queryOneGroup="select name,addr from png_groups where id='".$groupid."';";
    $queryGroupMembers="select host_id from png_hostingroup where group_id='".$groupid."'";
   
    $queryAllHosts="select id,name, devname from png_hosts  order by name asc;";
  
  
    $line=doFetchOneQuery($globalSS,$queryOneGroup);
  
  
  echo "<h2>".$_lang['stFORMEDITGROUP']."</h2>";
  echo '
  <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=3&actid=4&groupid='.$groupid.'" method="post">
  <table class=datatable>
   <tr><td>'.$_lang['stGROUPNAME'].':</td> <td><input type="text" name="name" value=\''.$line[0].'\'></td></tr>
   <tr><td>'.$_lang['stADDRESS'].':</td> <td> <input type="text" name="address" value=\''.$line[1].'\'></td></tr>
   </table>
  <br />			  
  <br />
     '.$_lang['stVALUE'].':<br />';
  
     $result=doFetchQuery($globalSS, $queryAllHosts);
     $result1=doFetchQuery($globalSS, $queryGroupMembers);
  
     $numrow=1;
     echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'hostTable\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
     echo '<button id="clearButton" type="button" onclick="ClearFilter(this);">'.$_lang['stCLEARFILTER'].'</button>';
  
  
   
       echo "<table id='hostTable' class=datatable style='display:table;'>";
     echo "<tr>
  <th >#</th>
  <th>".$_lang['stHOST']."</th>
  <th>".$_lang['stHOSTNAME']."</th>
  <th >".$_lang['stINCLUDE']."</th>
  </tr>";
  $isChecked="";
     $groupmembers=array();
     foreach($result1 as $line)
       $groupmembers[]= $line[0];
  
       foreach($result as $line) {
       echo "<tr>";
  echo "<td >".$numrow."</td>";
  echo "<td >".$line[1]."</td>";
  echo "<td >".$line[2]."</td>";
       if((in_array($line[0],$groupmembers))&&($isChecked==""))
         echo "<td><input type='checkbox' name='hostid[]' checked value='".$line[0]."'></td>";
       else
         echo "<td><input type='checkbox' name='hostid[]' value='".$line[0]."'></td>";
  echo "</tr>";
         ;
       $numrow++;
     }
     echo "</table>";
  
  
     echo '
       <input type="submit" value="'.$_lang['stSITESAVE'].'"><br />
       </form>
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=3&actid=5&groupid='.$groupid.'" method="post">
       <input type="submit" value="'.$_lang['stSITEDELETE'].'"><br />
       </form>
       <br />';
  
  
  }
  
  function doSaveGroup($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
      
    $_lang = $globalSS['lang'];
  
    $groupid = $_GET['groupid'];
  
    $name=$_POST['name'];
  
    
    $address=$_POST['address'];
    
    
              $queryUpdateOneGroup="update png_groups set name='".$name."',addr='".$address."' where id='".$groupid."'";
      
  
    if (!doQuery($globalSS, $queryUpdateOneGroup)) {
      die('Error: Cant update one group');
    }
  
    $sql="delete from png_hostingroup where group_id='".$groupid."';";
  
    doQuery($globalSS, $sql) or die();
  
    $sql="INSERT INTO png_hostingroup (group_id, host_id) VALUES  ";
  
      foreach($_POST['hostid'] as $key=>$value)
        $sql = $sql." ('".$groupid."','". $value."'),";
  
      $sql=substr($sql,0,strlen($sql)-1);
      $sql=$sql.";";
    
  
    doQuery($globalSS, $sql);
  
    echo "".$_lang['stGROUPUPDATED']."<br /><br />";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3 target=right>".$_lang['stBACK']."</a>";
  
  
  
  }
  
  function doDeleteGroup($globalSS){
  
  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
  $_lang = $globalSS['lang'];
  
  $groupid = $_GET['groupid'];
  
  $queryDeleteOneGroup="delete from png_groups where id='".$groupid."'";
  
  if (!doQuery($globalSS, $queryDeleteOneGroup)) {
    die('Error: Cant delete one group');
  }
  $sql="delete from png_hostingroup where group_id='".$groupid."';";
  
  doQuery($globalSS, $sql) or die();
  
  echo "".$_lang['stGROUPDELETED']."<br /><br />";
  echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3 target=right>".$_lang['stBACK']."</a><br />";
  
  
  }
  
  function doListGroup($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
      
    $_lang = $globalSS['lang'];
  
    $groupid = $_GET['groupid'];
  
    $queryOneGroupList="SELECT
                        png_groups.name, 
                        png_hosts.name,
                        png_hosts.devname,
                        png_hosts.linktoadm
                      FROM png_hostingroup 
                      RIGHT JOIN png_hosts ON png_hosts.id=png_hostingroup.host_id
                      RIGHT JOIN png_groups ON png_groups.id=png_hostingroup.group_id
                      WHERE group_id='".$groupid."'
                      ORDER BY INET_ATON(png_hosts.name) asc;";
  
  $result=doFetchQuery($globalSS, $queryOneGroupList);
  
  $numrow=1;
  
         foreach($result as $line) {
  if($numrow==1) {
  echo "".$_lang['stGROUPNAME']." : <b>".$line[0]."</b><br /><br />";
             echo "<table id='OneGroupList' class=datatable >";
             echo "<tr>
    <th >#</th>
    <th>".$_lang['stHOST']."</th>
    <th>".$_lang['stHOSTNAME']."</th>
    
    </tr>";
  }
           echo "<tr>";
  echo "<td >".$numrow."</td>";
  echo "<td >".$line[1]."</td>";
  echo "<td ><a href='".$line[3]."' target=_blank>".$line[2]."</a></td>";
  
  echo "</tr>";
           $numrow++;
         }
   
         echo "</table>";
   echo "<br />";
         echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=3>".$_lang['stBACKTOGROUPLIST']."</a>";
        }
  

?>