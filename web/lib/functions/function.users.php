<?php
#Функции работы с группами
function doPrintAllUsers($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  $queryAllUsers="SELECT
  
  png_users.login,
  png_users.comment,
  png_users.id, 
  png_users.active,
  png_users.email
  
     FROM png_users 
     ORDER BY login asc;";

        $result=doFetchQuery($globalSS, $queryAllUsers);
        $numrow=1;
        echo 	"<h2>".$_lang['stUSERS'].":</h2>";
        echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6&actid=1>".$_lang['stADDUSER']."</a>";
        echo "<br /><br />";
        echo "<table id=report_table_id_group class=datatable>
        <tr>
          <th class=unsortable><b>#</b></th>
          <th><b>".$_lang['stUSERLOGIN']."</b></th>
          <th class=unsortable><b>".$_lang['stCOMMENT']."</b></th>
          <th class=unsortable><b>".$_lang['stEMAIL']."</b></th>
          <th class=unsortable><b>".$_lang['stACTIVE']."</b></th>
          <th class=unsortable><b>".$_lang['stEDIT']."</b></th>
          <th class=unsortable><b>".$_lang['stLINKTOCABINET']."</b></th>

        </tr>";
 
        foreach($result as $line) {
 

            $line[3]= ($line[3]==1? "<font color=green>".$_lang['stYES']."</font>":"<font color=red>".$_lang['stNO']."</font>");
          echo "
            <tr>
              <td>".$numrow."</td>
              <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6&actid=6&userid=".$line[2].">".$line[0]."&nbsp;</a></td>
              <td align=center>".$line[1]."&nbsp;</td>
              <td align=center>".$line[4]."&nbsp;</a></td>
              <td align=center>".$line[3]."&nbsp;</a></td>
              <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6&actid=3&userid=".$line[2].">".$_lang['stEDIT']."</a></td>
              <td align=center><a href=cab/index.php target='_blank'>".$_lang['stLINK']."</a></td>

              </tr>";
          $numrow++;
        }
        echo "</table>";
        echo "<br />";
        echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6&actid=1>".$_lang['stADDUSER']."</a>";
        echo "<br />";

      
  }


function doPrintFormAddUser($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];


  $queryAllGroups="select id, name from png_groups order by name asc;";


  echo "<h2>".$_lang['stFORMADDUSER']."</h2>";
  echo '
<form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=6&actid=2" method="post">
<table class=datatable>
 <tr><td>'.$_lang['stUSERLOGIN'].':</td> <td><input type="text" name="fld_login"></td></tr>
 <tr><td>'.$_lang['stUSERPASSWORD'].':</td> <td><input type="text" name="fld_password"></td></tr>
 <tr><td>'.$_lang['stEMAIL'].':</td> <td> <input type="text" name="fld_email"></td></tr>
 <tr><td>'.$_lang['stCOMMENT'].':</td> <td> <input type="text" name="fld_comment"></td></tr>
 <tr><td>'.$_lang['stACTIVE'].':</td> <td> <input type="checkbox" name="fld_active"></td></tr>

 </table>
<br />
<br />
    '.$_lang['stVALUE'].':<br />';

    $result=doFetchQuery($globalSS, $queryAllGroups);
    $numrow=1;


    echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'hostTable\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
    echo '<button id="ClearFilterBtn" type="button" onclick="ClearFilter(this);">'.$_lang['stCLEARFILTER'].'</button>';

  echo "<table id='hostTable' class=datatable>";
  echo "<tr>
<th >#</th>
<th>".$_lang['stGROUPNAME']."</th>
<th >".$_lang['stINCLUDE']."</th>
</tr>";

  foreach($result as $line) {
    echo "
      <tr>
        <td >".$numrow."</td>
        <td >".$line[1]."</td>
        <td><input type='checkbox' name='groupid[]' value='".$line[0]."';</td>
      </tr>";
    $numrow++;
  }
  echo "</table>";


  echo '
    <input type="submit" value="'.$_lang['stADDUSER'].'"><br />
    </form>
    <br />';

  }

function doAddUser($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
  $_lang = $globalSS['lang'];

  if(!isset($_POST['fld_login'])) $fld_login="";  else $fld_login=$_POST['fld_login'];

  if(!isset($_POST['fld_password'])) $fld_password=md5(md5(""));  else $fld_password=md5(md5($_POST['fld_password']));

  if(!isset($_POST['fld_email'])) $fld_email="";  else $fld_email=$_POST['fld_email'];

  if(!isset($_POST['fld_comment'])) $fld_comment="";  else $fld_comment=$_POST['fld_comment'];

  if(!isset($_POST['fld_active'])) $fld_active="0";  else $fld_active="1";


  $sql="INSERT INTO png_users (login, pass, comment, active, email) VALUES ('$fld_login', '$fld_password','$fld_comment', '$fld_active','$fld_email')";

  
  if (!doQuery($globalSS, $sql)) {
    die('Error: Can`t insert new user');
  }

  $sql="select id from png_users where login='".$fld_login."';";
  $newid=doFetchOneQuery($globalSS, $sql);
  
  
  $sql="INSERT INTO png_groupinuser (user_id, group_id) VALUES  ";

    foreach($_POST['groupid'] as $key=>$value)
      $sql = $sql." ('".$newid[0]."','". $value."'),";

    $sql=substr($sql,0,strlen($sql)-1);
    $sql=$sql.";";
 
  doQuery($globalSS, $sql);

  echo "".$_lang['stUSERADDED']."<br /><br />";
  echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6 target=right>".$_lang['stBACK']."</a>";



  
}

function doPrintFormEditUser($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
  $_lang = $globalSS['lang'];
  $userid=$_GET['userid'];

  $queryOneUser="select login,comment,active, email from png_users where id='".$userid."';";
  $queryUserMembers="select group_id from png_groupinuser where user_id='".$userid."'";
 
  $queryAllGroups="select id, name from png_groups order by name asc;";


  $line=doFetchOneQuery($globalSS,$queryOneUser);

  $isActiveChecked = ($line[2]==1 ? "checked":"");

echo "<h2>".$_lang['stFORMEDITUSER']."</h2>";
echo '
<form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=6&actid=4&userid='.$userid.'" method="post">
<table class=datatable>
 <tr><td>'.$_lang['stUSER'].':</td> <td><input type="text" name="fld_login" value=\''.$line[0].'\'></td></tr>
 <tr><td>'.$_lang['stUSERPASSWORD'].':</td> <td> <input type="text" name="fld_password" value=""></td></tr>
 <tr><td>'.$_lang['stCHANGEPASSWORD'].':</td> <td> <input type="checkbox" name="fld_changepass"></td></tr>
 <tr><td>'.$_lang['stEMAIL'].':</td> <td> <input type="text" name="fld_email" value=\''.$line[3].'\'></td></tr>
  <tr><td>'.$_lang['stCOMMENT'].':</td> <td> <input type="text" name="fld_comment" value=\''.$line[1].'\'></td></tr>
 <tr><td>'.$_lang['stACTIVE'].':</td> <td> <input type="checkbox" '.$isActiveChecked.'  name="fld_active"></td></tr>
 
 
 </table>
<br />			  
<br />
   '.$_lang['stVALUE'].':<br />';

   $result=doFetchQuery($globalSS, $queryAllGroups);
   $result1=doFetchQuery($globalSS, $queryUserMembers);

   $numrow=1;
   echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'hostTable\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
   echo '<button id="clearButton" type="button" onclick="ClearFilter(this);">'.$_lang['stCLEARFILTER'].'</button>';


 
     echo "<table id='hostTable' class=datatable style='display:table;'>";
   echo "<tr>
<th >#</th>
<th>".$_lang['stGROUP']."</th>
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
     if((in_array($line[0],$groupmembers))&&($isChecked==""))
       echo "<td><input type='checkbox' name='groupid[]' checked value='".$line[0]."'></td>";
     else
       echo "<td><input type='checkbox' name='groupid[]' value='".$line[0]."'></td>";
echo "</tr>";
       ;
     $numrow++;
   }
   echo "</table>";


   echo '
     <input type="submit" value="'.$_lang['stSAVEUSER'].'"><br />
     </form>
     <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=6&actid=5&userid='.$userid.'" method="post">
     <input type="submit" value="'.$_lang['stDELETEUSER'].'"><br />
     </form>
     <br />';


}

function doSaveUser($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
  $_lang = $globalSS['lang'];

  $userid=$_GET['userid'];

  if(!isset($_POST['fld_login'])) $fld_login="";  else $fld_login=$_POST['fld_login'];

  if(isset($_POST['fld_changepass'])) 
    if(!isset($_POST['fld_password'])) $fld_password=md5(md5(""));  else $fld_password=md5(md5($_POST['fld_password']));

  if(!isset($_POST['fld_comment'])) $fld_comment="";  else $fld_comment=$_POST['fld_comment'];
  if(!isset($_POST['fld_email'])) $fld_email="";  else $fld_email=$_POST['fld_email'];

  if(!isset($_POST['fld_active'])) $fld_active="0";  else $fld_active="1";


   
  if(!isset($_POST['fld_changepass'])) 
            $queryUpdateOneUser="update png_users set login='".$fld_login."',comment='".$fld_comment."', active='".$fld_active."', email='".$fld_email."' where id='".$userid."'";
  if(isset($_POST['fld_changepass'])) 
        $queryUpdateOneUser="update png_users set login='".$fld_login."', pass='".$fld_password."',comment='".$fld_comment."', active='".$fld_active."',email='".$fld_email."' where id='".$userid."'";
  	

  if (!doQuery($globalSS, $queryUpdateOneUser)) {
    die('Error: Cant update one user');
  }

  $sql="delete from png_groupinuser where user_id='".$userid."';";

  doQuery($globalSS, $sql) or die();

  $sql="INSERT INTO png_groupinuser (user_id, group_id) VALUES  ";


if(isset($_POST['groupid'])){
  
    foreach($_POST['groupid'] as $key=>$value) {
      $sql = $sql." ('".$userid."','". $value."'),";
      
    }
      
    $sql=substr($sql,0,strlen($sql)-1);
    $sql=$sql.";";
  
  doQuery($globalSS, $sql);
}
  echo "".$_lang['stUSERUPDATED']."<br /><br />";
  echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6 target=right>".$_lang['stBACK']."</a>";



}

function doDeleteUser($globalSS){

include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
$_lang = $globalSS['lang'];

$userid = $_GET['userid'];

$queryDeleteOneUser="delete from png_users where id='".$userid."'";

if (!doQuery($globalSS, $queryDeleteOneUser)) {
  die('Error: Cant delete one user');
}
$sql="delete from png_groupinuser where user_id='".$userid."';";

doQuery($globalSS, $sql) or die();

echo "".$_lang['stUSERDELETED']."<br /><br />";
echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6 target=right>".$_lang['stBACK']."</a><br />";


}


      function doListUser($globalSS){

        include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
          
        $_lang = $globalSS['lang'];
      
        $userid = $_GET['userid'];
      
        $queryOneUserList="SELECT
                            png_users.login,
                            png_groups.name, 
                            png_groups.addr
                          FROM png_groupinuser 
                          RIGHT JOIN png_users ON png_users.id=png_groupinuser.user_id
                          RIGHT JOIN png_groups ON png_groups.id=png_groupinuser.group_id
                          WHERE png_groupinuser.user_id='".$userid."'
                          ORDER BY png_groups.name asc;";
      
      $result=doFetchQuery($globalSS, $queryOneUserList);
      
      $numrow=1;
      
             foreach($result as $line) {
      if($numrow==1) {
      echo "".$_lang['stUSER']." : <b>".$line[0]."</b><br /><br />";
                 echo "<table id='OneGroupList' class=datatable >";
                 echo "<tr>
        <th >#</th>
        <th>".$_lang['stGROUPNAME']."</th>
        <th>".$_lang['stADDRESS']."</th>
        
        </tr>";
      }
               echo "<tr>";
      echo "<td >".$numrow."</td>";
      echo "<td >".$line[1]."</td>";
      echo "<td >".$line[2]."</td>";
      
      echo "</tr>";
               $numrow++;
             }
       
             echo "</table>";
       echo "<br />";
             echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=6>".$_lang['stBACK']."</a>";
            }

    
?>