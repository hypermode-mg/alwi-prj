<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.getreport.php </#FN>                                          
*                         File Birth   > <!#FB> 2021/12/06 22:19:13.464 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/05/16 21:23:36.132 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.2.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/






function doGetReportData($globalSS,$query,$template_name) {

    #возможно костыль
    $_lang=$globalSS['lang'];

    include_once('function.database.php');
    include_once('function.reportmisc.php');
    include(''.$globalSS['root_dir'].'/lib/templates/'.$template_name.'');
    

    if($globalSS['debug']==1) echo $query;

    

    $json_result=json_encode(doFetchQuery($globalSS,$query));

    #Преобразуем данные в таблицу
    $result_data_json=doPrepareTable($globalSS,$json_result,$colh,$colr,$colf);
    
    

return $result_data_json;

}

function doPrintTable($globalSS, $json_result,$enableFilter=0) {



    $json = json_decode($json_result);

    ///TABLE HEADER old style
    //Так как данные уже готовы, то мы просто делаем заголовки, обозначаем, 
    //что сейчас будет строка и пишем как есть.
 
    //Так как последняя строчка не должна попадать в сортировку, сразу найдем последний элемент.
    //И будем поглядывать на него.
    $lastitem=end($json);

    if($enableFilter==1){
        echo '<input type="text" id="QInput" onkeyup="QuickFinder(\'report_table_id\')" placeholder="'.$_lang['stFASTSEARCH'].'">';
        echo '<button id="ClearFilterBtn" type="button" onclick="ClearFilter(\'report_table_id\');">Сбросить фильтр</button>';
    
    }
    echo "<table id=report_table_id class=datatable>";
        foreach ($json as $row_item) {
            
            if ($row_item === $lastitem)
                echo "<tr class=sortbottom>";
            else
                echo "<tr>";

            foreach ($row_item as $item) {
                echo $item;
                
                }
            echo "	</tr>";
        }
    echo "</table>";

}

#Костыль для того, чтобы получить данные для графиков
function doGetArrayData($globalSS,$json_result,$column_number) {
        
    $json = json_decode($json_result);

    $arrayData = array();
    
    $i=0;
    #нам нужны только данные, поэтому вырежем первую и последнюю строки
    $i_count=count($json);

    foreach ($json as $row_item) {
            $j=0;
            foreach ($row_item as $item) {
                if($j==$column_number)
                    $arrayData[$i]=strip_tags($item);
                $j++;
            }
            $i++;

            if($i==$i_count-1) break;
        }

    return array_slice($arrayData,1);

}



?>