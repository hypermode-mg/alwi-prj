<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> right.php </#FN>                                                       
*                         File Birth   > <!#FB> 2021/10/19 22:32:00.052 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/06/03 20:56:30.833 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.4.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/

  
  include_once("config.php");
 
	#если нет авторизации, сразу выходим
  if (!isAuthAdmin())
  {
    header("Location: ".$globalSS['root_http']."/modules/PrivateAuth/login.php"); exit();
  }
 
  $dbtype = $globalSS['connectionParams']['dbtype'];
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- The themes file -->
<link rel="stylesheet" type="text/css" href="<?php echo $globalSS['root_http']; ?>/themes/<?php echo $globalSS['globaltheme']; ?>/global.css"/>



</head>
<body>
<?php if(!isset($_GET['is_modal'])){ ?>
<input id="b1" type="button" value="Show/Hide menu" onclick="doit(this)">
<?php } ?>

<script type="text/javascript" src="javascript/sortable.js"></script>
<script src="javascript/misc.js" type="text/javascript"></script>
<script type="text/javascript">
function doit(btn) {
 xx = window.top.document.getElementsByTagName("frameset")[0];
 if (xx.cols == "170,*")
 {xx.cols = "0,*";
 btn.value = "Show menu";}
 else
 {xx.cols = "170,*";
 btn.value = "Hide menu";}
 }





function showModalPopUp(srv, host_id) {

popUpObj=window.open("right.php?srv="+srv+"&id=11&actid=1&is_modal=1&host_id="+host_id,"ModalPopUp","width=500, + height=500");

popUpObj.focus();

LoadModalDiv();

}

function showModalPopUp2(srv, host_id) {

popUpObj=window.open("right.php?srv="+srv+"&id=12&actid=1&is_modal=1&host_id="+host_id,"ModalPopUp","width=500, + height=500");

popUpObj.focus();

LoadModalDiv();

}

function showModalPopUp3(srv, sensor_id) {

popUpObj=window.open("right.php?srv="+srv+"&id=12&actid=3&is_modal=1&sensor_id="+sensor_id,"ModalPopUp","width=500, + height=500");

popUpObj.focus();

LoadModalDiv();

}

</script>

<?php



if(!isset($_GET['id'])) {
echo "<h2>".$_lang['stWELCOME']."".$vers."</h2>";

}

$start=microtime(true);



 

if(!isset($_GET['id'])) {echo "OK";  $_GET['id'] = 0;}//удалить надо
			//echo $_lang['stALLISOK'];

  if(1==null) { 
  //  echo $_lang['stDONTFORGET']; //и это тоже удалить
	echo ""; 
  }
  else
  {
	  
	
	  
	if(isset($_GET['id'])) {



  //end GET[id]=1
//    else

      if($_GET['id']==1) {  //hosts

        if(isset($_GET['actid'])) //action ID.
          $actid=$_GET['actid'];
        else
          $actid=0;



///надо добавить обработку ошибки подключения к БД

        if(!isset($_GET['actid'])) {
          doPrintAllHosts($globalSS);

        } // end if(!isset...
    

        if($actid==1) {
          doPrintFormAddHost($globalSS);
        }  //end if($actid==1..

        if($actid==2) {  //добавление 
          doHostAdd($globalSS);
        }

        if($actid==3) { ///Редактирование 
          doPrintFormHostEdit($globalSS);

        }
        if($actid==4) { //сохранение изменений UPDATE
          doHostSave($globalSS);
 
        }

        if($actid==5) { //удаление DELETE
          doHostDelete($globalSS);

        } //удаление
      } ///end if($_GET['id']==2



      if($_GET['id']==2) {  //nmapcmd

        if(isset($_GET['actid'])) //action ID.
          $actid=$_GET['actid'];
        else
          $actid=0;



///надо добавить обработку ошибки подключения к БД

        if(!isset($_GET['actid'])) {
          doPrintAllCmd($globalSS);

        } // end if(!isset...
    

        if($actid==1) {
          doPrintFormAddCmd($globalSS);
        }  //end if($actid==1..

        if($actid==2) {  //добавление 
          doAddCmd($globalSS);
        }

        if($actid==5) { //удаление DELETE
          doDeleteCmd($globalSS);

        } //удаление

        if($actid==7) { //Повторить
          doRepeatCmd($globalSS);

        } //удаление

      } ///end if($_GET['id']==2
//else

   
if($_GET['id']==3) {  //groups

  if(isset($_GET['actid'])) //action ID.
    $actid=$_GET['actid'];
  else
    $actid=0;



///надо добавить обработку ошибки подключения к БД

  if(!isset($_GET['actid'])) {
    doPrintAllGroups($globalSS);

  } // end if(!isset...


  if($actid==1) {
    doPrintFormAddGroup($globalSS);
  }  //end if($actid==1..

  if($actid==2) {  //добавление 
    doAddGroup($globalSS);
  }

  if($actid==3) { ///Редактирование 
    doPrintFormEditGroup($globalSS);

  }
  if($actid==4) { //сохранение изменений UPDATE
    doSaveGroup($globalSS);

  }

  if($actid==5) { //удаление DELETE
    doDeleteGroup($globalSS);

  } //удаление


  if($actid==6) { //Список членов группы
    doListGroup($globalSS);

  } //удаление
} ///end if($_GET['id']==2

	

   if($_GET['id']==5) {


  
      echo "
      <h2>".$_lang['stNOTIFICATON'].":</h2>";
  	  echo '<h3><a href="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=30">1H</a>&emsp;<a href="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=31">1D</a>&emsp;<a href="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=32">1M</a></h3>';
  
    $notifLimit="";

    #если неуказано, берем за день
      if(!isset($_GET['actid'])){
         $notifLimit="and p.date > unix_timestamp(sysdate()) - 86400";
      }
      else
      {
        if($_GET['actid']==30) {
          $notifLimit="and p.date > unix_timestamp(sysdate()) - 3600";
        }
        if($_GET['actid']==31) {
          $notifLimit="and p.date > unix_timestamp(sysdate()) - 86400";
        }
        if($_GET['actid']==32) {
          $notifLimit="and p.date > unix_timestamp(sysdate()) - 2592000";
        }

      }


  		if($dbtype==0)
  		#mysql
      $queryLogTable="SELECT
      FROM_UNIXTIME(p.date,'%H:%i:%s %Y-%m-%d') as d1,
      h.devname,
      p.alarm,
      h.id,
      FROM_UNIXTIME(p.date,'%Y-%m-%d %H:%i:%s') as d2
      FROM png_messages p, png_hosts h WHERE h.name=p.host ".$notifLimit." order by d2 desc";


        $json_result=doGetReportData($globalSS,$queryLogTable,'template4.php');
        doPrintTable($globalSS,$json_result);

    }  //end GET[id]=5

//id==6 перенесен ниже

if($_GET['id']==6) {  //users

  if(isset($_GET['actid'])) //action ID.
    $actid=$_GET['actid'];
  else
    $actid=0;



///надо добавить обработку ошибки подключения к БД

  if(!isset($_GET['actid'])) {
    doPrintAllUsers($globalSS);

  } // end if(!isset...


  if($actid==1) {
    doPrintFormAddUser($globalSS);
  }  //end if($actid==1..

  if($actid==2) {  //добавление 
    doAddUser($globalSS);
  }

  if($actid==3) { ///Редактирование 
    doPrintFormEditUser($globalSS);

  }
  if($actid==4) { //сохранение изменений UPDATE
    doSaveUser($globalSS);

  }

  if($actid==5) { //удаление DELETE
    doDeleteUser($globalSS);

  } //удаление


  if($actid==6) { //Список сайтов юзера
    doListUser($globalSS);

  } //удаление
} ///end if($_GET['id']==2

    if($_GET['id']==999) {
   	//тестовая страница
     echo "Test page<br /><br />";

 
     //вывод на экран диагностической информации
     
     echo "<b>Check configuration settings, server ".$srvname[$srv]."</b><br /><br />";
     
     if($dbtype==0)
       echo "Type DB MySQL<br />";	
     if($dbtype==1)
       echo "Type DB PostGRESQL<br />";	
     
     echo "Step1. Trying connect to DB.<br /><br />";
     
     echo "Connection parameters:<br /><br />";
     echo "Host: ".$globalSS['connectionParams']['addr']."<br />";
     echo "Database name: ".$globalSS['connectionParams']['dbase']."<br />";
     echo "Username: ".$globalSS['connectionParams']['usr']."<br />";
     echo "Password: ".$globalSS['connectionParams']['psw']."<br /><br />";

     
     

     if(doConnectToDatabase($globalSS['connectionParams'])!="ErrorConnection")
     echo "Result: <b>Ok!</b>";
     else {
     echo "Result: <b>Error!</b>";
     echo "<br><br>";
     echo "
     Some solutions:<br>
     1. Check that DB server is ONLINE.<br>
     2. Check for connection settings (login, pass,db, host).<br>
     3. Check that you can connect from your system to database server on default DB port (3306 to MySQL or 5432 to PostGRESQL).<br>
     4. Check that user <b>".$usr."</b> have rights to connect to DB.<br>
     5. If you have no idea about problem, post ticket on sourceforge for  project. We try to help you.
     ";
     }
     
     echo "<br /><br />";
                        
      
          }  //end GET[id]=999


///            else


 } //if(isset($_GET['id'])) {   

            

 } /// else { 

//if($connectionStatus!="error")


if($_GET['id']==8) {
	
	
   	
   	if(!isset($_GET['actid'])){
		echo "<h2>".$_lang['stEDITCONN']." DB ".$softname.":</h2>";

              echo "<br /><br />";
              echo "<table id=report_table_id_group class=datatable>
              <tr>
                <th class=unsortable><b>#</b></th>
                <th><b>".$_lang['stSERVERNAME']."</b></th>
                <th class=unsortable><b>".$_lang['stCONFIGFILE']."</b></th>
                <th class=unsortable><b>".$_lang['stACTION']."</b></th>

              </tr>";

#try to get conf
$path    = 'conf/';
$files = array_diff(scandir($path), array('.', '..','.gitignore'));
$numrow=1;
			foreach($files as $file) {
			 $config= include 'conf/'.$file;

                echo "
                  <tr>
                    <td>".$numrow."</td>
                    <td align=center><a href=right.php?srv=".$srv."&id=8&actid=2&filename=".$file.">".$config['srvname']."</a></td>
                    <td align=center>".$file."</td>
                    <td align=center><a href=right.php?srv=".$srv."&id=8&actid=5&filename=".$file.">".$_lang['stDELETE']."</a></td>
                 </tr>";
                $numrow++;
              }
              echo "</table>";
        
   	
   	
 } // 	if(!isset($_GET['actid'])){

if(isset($_GET['actid']))
		{
			
	
			
			if($_GET['actid'] == 2) ///редактировать
			{
				
				$config = include("conf/".$_GET['filename']);
        
        $dbEngine = array();
        $dbEngine[0] = "MySQL (MariaDB)";
        
        

			   echo "<h2>Edit DB:</h2>";
			   echo '
                  <form action="right.php?srv='.$srv.'&id=8&actid=3&filename='.$_GET['filename'].'" method="post">
                 			<table class=datatable>
                 		<tr>
						<td>Database Type:</td>
						<td>
              <select name="srvdbtype">
              ';
            $numEngine =0;
            foreach($dbEngine as $engine) {
 
              if($numEngine == $config['srvdbtype']) $selected = "selected"; else $selected="";
 
              echo  '<option value="'.$numEngine.'" '.$selected.'/>'.$engine.'</option>';
            $numEngine++;
            }
						echo '</select>
						</td>
						</tr>
					   <tr><td>Pinger server name:</td> <td><input type="text" name="srvname" value="'.$config['srvname'].'"></td></tr>
					   <tr><td>Database name:</td> <td><input type="text" name="db" value="'.$config['db'].'"></td></tr>
					   <tr><td>Username:</td> <td><input type="text" name="user" value="'.$config['user'].'"></td></tr>
					   <tr><td>Password:</td> <td><input type="text" name="pass" value="'.$config['pass'].'"></td></tr>
					   <tr><td>Database host address:</td> <td><input type="text" name="address" value="'.$config['address'].'"></td></tr>
					   	<tr class="row2">
							<td>Enabled</td>
							<td><input type="checkbox" name="enabled" ';if($config['enabled']==1) echo "checked=checked"; echo ' />
							<br/>Normally this field should be checked at all times.  Use caution when disabling this feature</td>
						</tr>
				   		
						</table>
                 		
  
                 <br />
                  <input type="submit" name=submit value="'.$_lang['stSAVE'].'"><br />
                  </form>
                  ';
			
				

			} //if($_GET['actid'] == 2) 
			
			if($_GET['actid'] == 3) ///сохранить
			{
				if(isset($_POST['enabled'])) $config['enabled']=1; else $config['enabled']=0;
				
				$config['srvname']=$_POST['srvname'];
				$config['db']=$_POST['db'];
				$config['user']=$_POST['user'];
				$config['pass']=$_POST['pass'];
				$config['address']=$_POST['address'];
				$config['srvdbtype']=$_POST['srvdbtype'];

					
				file_put_contents('conf/'.$_GET['filename'], '<?php return '. var_export($config, true) . ';?>');
				
				echo '<script type="text/javascript">parent.left.location.href="mainmenu.php?srv=0";parent.right.location.href="right.php?srv=0&id=8";</script>';

				

			} //if($_GET['actid'] == 3) 
			
			if($_GET['actid'] == 5) ///удалить
			{
				if(unlink("conf/".$_GET['filename'])) echo "Delete successfully";
				
				echo '<script type="text/javascript">parent.right.location.href="right.php?srv='.$srv.'&id=8";</script>';

				

			} //if($_GET['actid'] == 5) 

		} //if(isset($_GET['actid']))


                  

    }  //end GET[id]=8


    if($_GET['id']==10) {  //dicts

      if(isset($_GET['actid'])) //action ID.
        $actid=$_GET['actid'];
      else
        $actid=0;



///надо добавить обработку ошибки подключения к БД

      if(!isset($_GET['actid'])) {
        doPrintAllItems($globalSS,$_GET['dname']);

      } // end if(!isset...
  

      if($actid==1) {
        doPrintFormAddItem($globalSS,$_GET['dname']);
      }  //end if($actid==1..

      if($actid==2) {  //добавление 
        doAddItem($globalSS);
      }

      if($actid==3) { ///Редактирование 
        doPrintFormEditItem($globalSS,$_GET['dname']);

      }
      if($actid==4) { //сохранение изменений UPDATE
        doSaveItem($globalSS);

      }

      if($actid==5) { //удаление DELETE
        doDeleteItem($globalSS);

      } //удаление
    } ///end if($_GET['id']==2


    if($_GET['id']==11) {  //hosts services

      if(isset($_GET['actid'])) //action ID.
        $actid=$_GET['actid'];
      else
        $actid=0;



///надо добавить обработку ошибки подключения к БД

      if(!isset($_GET['actid'])) {
        doPrintAllHostServices($globalSS);

      } // end if(!isset...
  

      if($actid==1) {
        doPrintFormAddHostService($globalSS);
      }  //end if($actid==1..

      if($actid==2) {  //добавление 
        doHostAddService($globalSS);
      }

    
      if($actid==5) { //удаление DELETE
        doHostDeleteService($globalSS);

      } //удаление
    } ///end if($_GET['id']==2

    if($_GET['id']==12) {  //hosts sensors

      if(isset($_GET['actid'])) //action ID.
        $actid=$_GET['actid'];
      else
        $actid=0;



///надо добавить обработку ошибки подключения к БД

      if(!isset($_GET['actid'])) {
        doPrintAllHostSensors($globalSS);

      } // end if(!isset...
  

      if($actid==1) {
        doPrintFormAddHostSensor($globalSS);
      }  //end if($actid==1..

      if($actid==2) {  //добавление 
        doHostAddSensor($globalSS);
      }


      if($actid==3) {  //редактирование 
        doPrintFormHostEditSensor($globalSS);
      }

      if($actid==4) {  //сохранение 
        doHostSaveSensor($globalSS);
      }

    
      if($actid==5) { //удаление DELETE
        doHostDeleteSensor($globalSS);

      } //удаление
    } ///end if($_GET['id']==2

$end=microtime(true);

$runtime=$end - $start;

echo "<br /><br /><font size=2>".$_lang['stEXECUTIONTIME']." ".round($runtime,3)." ".$_lang['stSECONDS']."</font><br />";

echo $_lang['stCREATORS'];





?>

</body>
</html>
