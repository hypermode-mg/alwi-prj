<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.misc.php </#FN>                                               
*                         File Birth   > <!#FB> 2022/07/06 22:15:44.496 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/04/10 21:53:04.929 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/














#Функция получения значения параметра из scsq_modules_param
function doGetParam($globalSS,$module,$param){

        include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
        $queryGetParam="select val from scsq_modules_param  where module='".$module."' and param='".$param."';";
        $row=doFetchOneQuery($globalSS,$queryGetParam);

        return $row[0];
        }

#Функция установки значения параметра в scsq_modules_param
function doSetParam($globalSS,$module,$param,$val){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');

  $querySetParam="update scsq_modules_param set val='".$val."' where module='".$module."' and param='".$param."';";

  doQuery($globalSS,$querySetParam);

  }

#Функция чтения глобальных значенией параметров в scsq_modules_param
function doReadGlobalParamsTable($globalSS){

  include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');

  $queryReadParams="select param,val,switch from scsq_modules_param where module='Global';";

  $result=doFetchQuery($globalSS,$queryReadParams);
 
  foreach($result as $line ){
   
    #if param is switch, then convert On to 1, '' to  0
    if($line[2] == '1')
      $globalSS[$line[0]] = ($line[1] == 'on' ? '1' : '0');
    else
      $globalSS[$line[0]] = $line[1];
  }
    return $globalSS;
  }

  function doReadGlobalParamsTableToConfig($globalSS){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
  
    $queryReadParams="select param,val,switch,comment from scsq_modules_param where module='Global';";
  
    $result=doFetchQuery($globalSS,$queryReadParams);
   
    foreach($result as $line ){
     
      #if param is switch, then convert On to 1, '' to  0
      if($line[2] == '1')
        $globalSS['gParams'][$line[0]]['value'] = ($line[1] == 'on' ? 'on' : 'off');
      else
        $globalSS['gParams'][$line[0]]['value'] = $line[1];

        $globalSS['gParams'][$line[0]]['comment'] = $line[3];
      }
      return $globalSS;
    }

    function doWriteToLogTable($globalSS, $params){

      include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
     
      $sqlquery="insert into png_logtable (datestart,dateend,message) VALUES ('".$params['datestart']."','".$params['dateend']."','".$params['message']."');";
    
      doQuery($globalSS,$sqlquery);
    
          
      }

      function doIsAuth($globalSS, $params){

        include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
       
        $sqlquery="insert into png_logtable (datestart,dateend,message) VALUES ('".$params['datestart']."','".$params['dateend']."','".$params['message']."');";
      
        doQuery($globalSS,$sqlquery);
      
            
        }

?>