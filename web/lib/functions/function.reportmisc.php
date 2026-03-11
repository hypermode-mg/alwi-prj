<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> function.reportmisc.php </#FN>                                         
*                         File Birth   > <!#FB> 2023/04/10 21:49:40.433 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/04/10 21:49:55.974 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/






//Функция подготовки данных для таблицы
//возвращает json набор данных, мы его пишем в файл и показываем пользователю.
//а когда он запросит то же самое - достанем из кэша
function doPrepareTable($globalSS, $json_result,$colh,$colr,$colf) {

    //возьмём данные, которые нам вернул запрос и переведем их из JSON в обычный массив
    $json = json_decode($json_result);
    $_lang = $globalSS['lang'];
    $row_line_arr=[];
    
    $chunked_array =array();


    $result[0] =array();
    $result[0] = array_slice($colh, 1); ;

    $chunked_array = array_chunk($result[0],$colh[0]);

    $result[0] = $chunked_array[0];

//TABLE BODY


$i=0;

#возможно лучший костыль. Идея такая. Если в шаблоне в конце указано слово total
#то будем этот столбец считать. Номер столбца это номер totals
$totals = array();
$totals[0] = 0;

///for($i=1;$i<$numrow;$i++) {
foreach ($json as $row_item) {

    $i++;
$row_line="";


//вот этот костыль надо убирать!
foreach ($row_item as $item) {
    $row_line=$row_line.$item.";;";
    
    }
  
$line=explode(';;',$row_line);




for($j=1;$j<=$colh[0];$j++){

#если это нужно считать, то сразу приведем это к нормальному виду. 
if(preg_match("/total_column_byte/i", $colf[$j]))
{
    $line_index =strip_tags(preg_replace("/line/i", "", $colr[$j]));
    $line[$line_index]=sprintf("%f",doConvertBytes($line[$line_index],'mbytes'));  //disable scientific format, like 5E-10

}

#Это если просто цифры сложить. с 2 десятичными знаками
if(preg_match("/total_column_float/i", $colf[$j]))
{
    $line_index =strip_tags(preg_replace("/line/i", "", $colr[$j]));
    $line[$line_index]=sprintf("%0.2f",$line[$line_index]);  //disable scientific format, like 5E-10
}

#Это если просто цифры сложить
if(preg_match("/total_column_number/i", $colf[$j]))
{
    $line_index =strip_tags(preg_replace("/line/i", "", $colr[$j]));
    $line[$line_index]=sprintf("%d",$line[$line_index]); 
}



$resultcolr=$colr[$j];


if(preg_match("/lang_var/i", $resultcolr))
    $resultcolr=preg_replace("/lang_var_line_0/i", $_lang[$line[0]], $resultcolr);



//обработка аптайма
if(isset($line[3]) && preg_match("/getuptime_line_3/i", $resultcolr)) {
    $resultcolr=preg_replace("/getuptime_line_3/i", secondsToTime($line[3]), $resultcolr);
}

//Обработка поля Статус
if(isset($line[1]) && preg_match("/getstatusname_line_1/i", $resultcolr))
{
if($line[1] == 1) 
    $resultcolr=preg_replace("/getstatusname_line_1/i", "<b><font color=green>ON-LINE</font></b>", $resultcolr);
if($line[1] == 0)
    $resultcolr=preg_replace("/getstatusname_line_1/i", "<b><font color=red>OFF-LINE</font></b>", $resultcolr);
if($line[1] == 2)
    $resultcolr=preg_replace("/getstatusname_line_1/i", "<b><font color=orange>UNKNOWN</font></b>", $resultcolr);

if($line[1] == 3)
    $resultcolr=preg_replace("/getstatusname_line_1/i", "<b><font color=#333333>".$_lang['stPOLLSTOPPED']."</font></b>", $resultcolr);


}



for($n=0;$n<=30;$n++){
    if(isset($line[$n]))
        $resultcolr=preg_replace("/\bline".$n."\b/i", $line[$n], $resultcolr);
       
}

#Если есть информация по хосту подтянем её

if(preg_match("/hostinfo/i", $resultcolr)) {
    $resrow=doFetchOneQuery($globalSS,"select type,name,devname,location from png_hosts where id = '$line[4]' limit 1");
    if(isset($resrow[0]))
        $resultcolr=preg_replace("/hostinfo/i", $resrow[0]."<br>".$resrow[1]."<br>".$resrow[2]."<br>".$resrow[3], $resultcolr);
    else
        $resultcolr=preg_replace("/hostinfo/i", "Нет информации", $resultcolr);
        
    
}

if(isset($i))
$resultcolr=preg_replace("/numrow/i", $i, $resultcolr);

$row_line_arr[$j]=$resultcolr;

if(preg_match("/total_column/i", $colf[$j])) {
    if(!isset($totals[$j]))
        $totals[$j] = 0;
    $totals[$j] = $totals[$j] + strip_tags($row_line_arr[$j]);
}

#$row_line_arr[$j]=preg_replace("/\./",",",$resultcolr);
    

}

$result[$i] = $row_line_arr;


}

///TABLE FOOTER


if(isset($colh))
for($k=1;$k<=$colh[0];$k++){
    if (preg_match("/total_column/i", $colf[$k])) {
        if(!isset($totals[$k])) $totals[$k] = 0;
        $colf[$k]= preg_replace("/total_column(.+)\</i", $totals[$k]."<", $colf[$k]);
    }
//echo $colf[$i];
}

$chunked_array = array_chunk($colf,$colh[0]);

$result[$i+1] = $chunked_array[0];

$result_json=json_encode($result);

return $result_json;
}



//Конвертируем байты в мегабайты. Возможно потом еще добавим вариант - гигабайты
function doConvertBytes($bytes,$toWhat) {
//$oneMegabyte = 1024 * 1024;
$oneMegabyte = 1000 * 1000;

#Если вдруг прилетела пустота
if($bytes=='')
    $bytes = 0;

    if($toWhat == 'mbytes')
        return $bytes / $oneMegabyte;

    else
        //если ничего не укажем, будем -1 показывать
        return -1;
}

function secondsToTime($seconds) {
    $dtF = new DateTime();
    $dtF->setTimestamp(0);
    $dtT = new DateTime();
    $dtT->setTimestamp(intval($seconds));
    return $dtF->diff($dtT)->format('%a days, %h h, %im %ss');
} 

?>