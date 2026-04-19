<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.dictionaries.php </#FN>                                       
*                         File Birth   > <!#FB> 2023/08/17 21:05:53.558 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/09/12 21:42:43.654 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/




#Функции работы со справочниками
#так как они все типовые - id, name то просто будем давать им имена, а механизм CRUD везде одинаков
function doPrintAllItems($globalSS,$dictname){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
    $_lang = $globalSS['lang'];

$dictnames = array();

$dictnames = doGetDictnames($globalSS,$dictname);

    #здесь будем подкидывать имя таблицы
    $dicttable = $dictnames['dicttable'] ;
    $dictrealname = $dictnames['dictrealname'] ;




    
    $queryAllItems="
        SELECT 
          id,
          name

       FROM ".$dicttable."
       ORDER BY name asc";

    $result = doFetchQuery($globalSS,$queryAllItems);

    $numrow=1;
    echo 	"<h2>".$dictrealname.":</h2>";
    echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&actid=1&dname=".$dictname.">".$_lang['stADD']."</a>";
    echo "<br /><br />";

    echo "<table class=\"datatable\">
            <tr>
              <th ><b>#</b></th>
              <th><b>".$_lang['stROWNAME']."</b></th>
             <th><b>".$_lang['stACTION']."</b></th>
             </tr>
    ";

   foreach($result as $line) {
     echo "
     <tr>
       <td>".$numrow."</td>
       <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&actid=3&itemid=".$line[0]."&dname=".$dictname.">".$line[1]."&nbsp;</a></td>
     <td align=center><a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&actid=5&itemid=".$line[0]."&dname=".$dictname.">".$_lang['stDELETE']."</a></td>

     </tr>
     ";
     $numrow++;
    }  //end while
        
echo "</table>";
   echo "<br />";
   echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&actid=1&dname=".$dictname.">".$_lang['stADD']."</a>";
   echo "<br />";
    
    }

    function doPrintAllItemsAsDatalist($globalSS,$dictname){

      include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
      
      $_lang = $globalSS['lang'];
  
      $dictnames = array();

      $dictnames = doGetDictnames($globalSS,$dictname);
      
          #здесь будем подкидывать имя таблицы
          $dicttable = $dictnames['dicttable'] ;
          $dictrealname = $dictnames['dictrealname'] ;
  
      
      $queryAllItems="
          SELECT 
            id,
            name
  
         FROM ".$dicttable."
         ORDER BY name asc";
  
      $result = doFetchQuery($globalSS,$queryAllItems);
  
      $numrow=1;

      echo '<datalist id="'.$dicttable.'" >';

   
      foreach($result as $line) {

        echo "
       <option value='".$line[1]."' >";
        $numrow++;
       }  //end while
      
            echo '</datalist>';

      
      }


function doPrintFormAddItem($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');

  $m_host="";
  $m_hostname="";

  #закладка на случай модального окна
/*    if(!isset($GET['modal'])) 
    {
      $m_host="value='".$_GET['m_host']."'";
      $m_hostname="value='".$_GET['m_hostname']."'";
    }
  */  

    $dictname = $_GET['dname'];

    $dictnames = array();

    $dictnames = doGetDictnames($globalSS,$dictname);
    
        #здесь будем подкидывать имя таблицы
        $dicttable = $dictnames['dicttable'] ;
        $dictrealname = $dictnames['dictrealname'] ;

    $_lang = $globalSS['lang'];

          echo "<h2>".$_lang['stADD']."</h2>";
         echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=10&actid=2&dname='.$dictname.'" method="post">
       <table class=datatable>
       
       <tr><td><b>'.$_lang['stROWNAME'].'</b></td><td><input type="text" '.$m_host.' name="field_name"></td></tr>
      
       </table>
       <br />
       ';
       
       if(!isset($GET['modal'])) 
       {
        echo '<input type="hidden" name="field_ismodal" value="1">';
       }
         echo '
           <input type="submit" value="'.$_lang['stADD'].'"><br />
           </form>
           <br />';
       
         }
       
       
       function doAddItem($globalSS){
       
         include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
         
         $_lang = $globalSS['lang'];
       
         $dictname = $_GET['dname'];

         $dictnames = array();

         $dictnames = doGetDictnames($globalSS,$dictname);
         
             #здесь будем подкидывать имя таблицы
             $dicttable = $dictnames['dicttable'] ;
             $dictrealname = $dictnames['dictrealname'] ;
          
  
       
         if(!isset($_POST['field_name'])) $field_name="";  else $field_name=$_POST['field_name'];
 
        
         $sql="INSERT INTO ".$dicttable." (
          name
   
          ) VALUES (
           '$field_name'
          )";
  
       
        if (!doQuery($globalSS, $sql)) {
         die('Error: Can`t insert new item');
        }

         echo "".$_lang['stADDED']."<br /><br />";

//         if(isset($_POST['field_ismodal']))
  //       echo "Закройте это окно и продолжите работу.";
    //     else 

         echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&dname=".$dictname." target=right>".$_lang['stBACK']."</a>";
 
       }
       
       
       
       
       
       function doPrintFormEditItem($globalSS,$dictname){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
         
       $_lang = $globalSS['lang'];
       
  
       $dictnames = array();

       $dictnames = doGetDictnames($globalSS,$dictname);
       
           #здесь будем подкидывать имя таблицы
           $dicttable = $dictnames['dicttable'] ;
           $dictrealname = $dictnames['dictrealname'] ;
  


       $itemid=$_GET['itemid'];
       
       $queryOne="select
       id, 
       name
       from ".$dicttable." d
       where d.id=".$itemid."";
        
       
       $line=doFetchOneQuery($globalSS,$queryOne);
       
       
       echo "<h2>".$_lang['stEDIT']."</h2>";
       echo '
       <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=10&actid=4&itemid='.$itemid.'&dname='.$dictname.'" method="post">
       <table class=datatable>
         <tr><td><b>'.$_lang['stROWNAME'].'</b></td><td><input type="text"                   value=\''.$line[1].'\' name="field_name"></td></tr>
              
       </table>
       <br />
       ';
       
       echo '
       <input type="submit" value="'.$_lang['stSAVE'].'"><br />
       </form>
       <br />';
       
       echo '
          <form action="right.php?srv='.$globalSS['connectionParams']['srv'].'&id=10&actid=5&itemid='.$itemid.'&dname='.$dictname.'" method="post">
          <input type="submit" value="'.$_lang['stDELETE'].'" ><br />
          </form>
          <br />';
       
       
       }
       
       function doSaveItem($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       
       $dictname = $_GET['dname'];


       $dictnames = array();

       $dictnames = doGetDictnames($globalSS,$dictname);
       
           #здесь будем подкидывать имя таблицы
           $dicttable = $dictnames['dicttable'] ;
           $dictrealname = $dictnames['dictrealname'] ;
         
       $_lang = $globalSS['lang'];
       
       $itemid = $_GET['itemid'];
       
       if(!isset($_POST['field_name'])) $field_name="";  else $field_name=$_POST['field_name'];
       
       
       $queryUpdateOne="UPDATE  ".$dicttable." d
         set 
         name ='$field_name'
          
     
         where d.id = ".$itemid."  
           ";
       
    
       
       
       
       if (!doQuery($globalSS, $queryUpdateOne)) {
         die('Error: Cant update one');
       }
       
       echo "".$_lang['stUPDATED']."<br /><br />";
       echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&dname=".$dictname." target=right>".$_lang['stBACK']."</a>";
       
       
       
       }
       
       function doDeleteItem($globalSS){
       
       include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       

       $dictname=$_REQUEST['dname'];


       $dictnames = array();

       $dictnames = doGetDictnames($globalSS,$dictname);
       
           #здесь будем подкидывать имя таблицы
           $dicttable = $dictnames['dicttable'] ;
           $dictrealname = $dictnames['dictrealname'] ;
         
       $_lang = $globalSS['lang'];
       
       $itemid = $_GET['itemid'];
       
       
       #удаляем item
       $queryDeleteOne="delete from ".$dicttable." where id='".$itemid."'";
       
       if (!doQuery($globalSS, $queryDeleteOne)) {
       die('Error: Cant delete one host');
       }
       
       echo "".$_lang['stDELETED']."<br /><br />";
       echo "<a href=right.php?srv=".$globalSS['connectionParams']['srv']."&id=10&dname=".$dictname." target=right>".$_lang['stBACK']."</a><br />";
       
       
       }

function doGetDictnames($globalSS,$dictname){

  $_lang = $globalSS['lang'];

       if($dictname == "vendor") { $dicttable = "png_listvendors";  $dictrealname = $_lang['stDICTVENDORS']; }
       if($dictname == "model") { $dicttable = "png_listmodels";  $dictrealname = $_lang['stDICTMODELS']; }
       if($dictname == "devtype") { $dicttable = "png_listdevicetypes";  $dictrealname = $_lang['stDICTTYPES']; }
       if($dictname == "os") { $dicttable = "png_listos";  $dictrealname = $_lang['stDICTOSES']; }
       if($dictname == "groups") { $dicttable = "png_groups";  $dictrealname = $_lang['stDICTGROUPS']; }

$dictnames = array();

$dictnames['dicttable'] = $dicttable;
$dictnames['dictrealname'] = $dictrealname;


return $dictnames;


}

?>