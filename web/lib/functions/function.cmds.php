<?php

#Функции работы с командами nmap
function doPrintAllCmd($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];
  
       
    $queryAll="
        SELECT 
          id,
          from_unixtime(date),
          scanarray,
          is_executed
          
       FROM png_nmapcmd
       ORDER BY INET_ATON(SUBSTRING_INDEX(scanarray, '/', 1)) asc";
  
    $result = doFetchQuery($globalSS,$queryAll);
  
    $numrow=1;
    echo 	"<h2>".$_lang['stNMAPCMD'].":</h2>";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2&actid=1>".$_lang['stADD']."</a>";
    echo "<br /><br />";
  
    echo "<table class=\"datatable\">
            <tr>
              <th ><b>#</b></th>
              <th><b>".$_lang['stDATE']."</b></th>
              <th><b>".$_lang['stNMAPARRAY']."</b></th>
              <th ><b>".$_lang['stISEXECUTED']."</b></th>
             <th><b>".$_lang['stDELETE']."</b></th>
             </tr>
    ";
  
   foreach($result as $line) {
     if($line[3]=="1")
       $line[3]="<b><font color=blue>".$_lang['stEXECUTING']."</font></b>";
     if($line[3]=="2")
       $line[3]="<b><font color=green>".$_lang['stEXECUTED']." <a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2&actid=7&cmdid=".$line[0].">".$_lang['stREPEAT']."</a></font></b>";
     if($line[3]=="0")
       $line[3]="<b><font color=red>".$_lang['stWAIT']."</font></b>";
     echo "
     <tr>
       <td>".$numrow."</td>
       <td>".$line[1]."&nbsp;</td>
       <td>".$line[2]."&nbsp;</td>
       <td>".$line[3]."&nbsp;</td>
     <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2&actid=5&cmdid=".$line[0].">DELETE</a></td>
  
     </tr>
     ";
     $numrow++;
    }  //end while
        
  echo "</table>";
   echo "<br />";
   echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2&actid=1>".$_lang['stADD']."</a>";
   echo "<br />";
    
    }
  
  
  function doPrintFormAddCmd($globalSS){
  
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
    
  
    $_lang = $globalSS['lang'];
  
          echo "<h2>".$_lang['stADD']."</h2>";
         echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=2&actid=2" method="post">
       <table class=datatable>
       
       <tr><td><b>'.$_lang['stNMAPARRAY'].'</b></td><td><input type="text"  name="field_scanarray"></td></tr>
  
       
       </table>
       <br />
       ';
         echo '
           <input type="submit" value="'.$_lang['stCMDADD'].'"><br />
           </form>
           <br />';
       
         }
       
       
       function doAddCmd($globalSS){
       
         include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
         
         $_lang = $globalSS['lang'];
       
  
       
         if(!isset($_POST['field_scanarray'])) $field_scanarray="";  else $field_scanarray=$_POST['field_scanarray'];
        
        
        
         $sql="INSERT INTO png_nmapcmd (
          date,
          scanarray
   
          ) VALUES (
           unix_timestamp(sysdate()),
           '$field_scanarray'
          )";
  
       
        if (!doQuery($globalSS, $sql)) {
         die('Error: Can`t insert new cmd');
        }
  
         echo "".$_lang['stADDED']."<br /><br />";
  
         echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2 target=right>".$_lang['stBACK']."</a>";
  
       }
       
  
       function doRepeatCmd($globalSS){
       
        include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
        
        $_lang = $globalSS['lang'];
        
        $o_id = $_GET['cmdid'];
        
        
        #Обновляем статус хост
        $queryUpdateOne="update png_nmapcmd set is_executed='0' where id='".$o_id."'";
        echo $queryUpdateOne;
        if (!doQuery($globalSS, $queryUpdateOne)) {
        die('Error: Cant update one host');
        }
        
        doPrintAllCmd($globalSS);
        
        
        }     
      
       
       function doDeleteCmd($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       
       $_lang = $globalSS['lang'];
       
       $o_id = $_GET['cmdid'];
       
       
       #удаляем хост
       $queryDeleteOne="delete from png_nmapcmd where id='".$o_id."'";
       
       if (!doQuery($globalSS, $queryDeleteOne)) {
       die('Error: Cant delete one host');
       }
       
       echo "".$_lang['stDELETED']."<br /><br />";
       echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=2 target=right>".$_lang['stBACK']."</a><br />";
       
       
       }

?>