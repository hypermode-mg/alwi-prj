<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> reports.php </#FN>                                                     
*                         File Birth   > <!#FB> 2022/07/07 22:04:39.148 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/04/24 21:38:53.402 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/







include("../../config.php");


#составим список  хостов доступных сайтов пользователя
function doCreateHostsList($globalSS,$user){

    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');

	$sqlGetHostsId="select h.host_id from png_hostingroup h, png_groupinuser g, png_users u
	where u.login='".$user."' and u.id=g.user_id and g.group_id=h.group_id";
    $result = doFetchQuery($globalSS,$sqlGetHostsId);
	$hostsList="0";
    foreach ($result as $row) {
        $hostsList=$hostsList.",".$row[0];
    }

    return $hostsList;

}

	#если нет авторизации, сразу выходим
	if (!isAuth())
	{
		header("Location: ".$globalSS['root_http']."cab/modules/PrivateAuth/login.php"); exit();
	}
	

$paramsGET = array();

//номер БД
if(isset($_GET['srv']))  $srv=$_GET['srv']; else  $srv=0;

//reports id

//ID номер отчёта
if (isset($_GET['id']))	$id=$_GET['id']; else $id=0;


$header='<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- The themes file -->
<link rel="stylesheet" type="text/css" href="'.$globalSS['root_http'].'/themes/'.$globalSS['globaltheme'].'/global.css"/>
';

if($id == 1 or $id == 2 or $id == 5)
$header=$header.'<META HTTP-EQUIV="REFRESH" CONTENT="'.$globalSS['refreshPeriod'].'">'; ///обновление страницы в секундах


$header=$header.'
</head>

<body>
';





#узнаем пользователя из кук
$userlogin=$_SESSION['user_login'];

#соберем ограничивающее условие
$queryHosts=doCreateHostsList($globalSS,$userlogin);

$dbtype = $globalSS['connectionParams']['dbtype'];

$query = "SELECT id FROM png_users WHERE login='$userlogin' LIMIT 1";
	
$row=doFetchOneQuery($globalSS, $query);
$userid=$row[0];


#include("".$globalSS['root_dir']."/modules/Chart/module.php");

//$grap = new Chart($globalSS); #получим экземпляр класса и будем уже туда закидывать запросы на исполнение




$start=microtime(true);





	echo $header;

	// Javascripts
?>
<script>
function doit(btn) {
 xx = window.top.document.getElementsByTagName("frameset")[0];
 if (xx.cols == "170,*")
 {xx.cols = "0,*";
 btn.value = "Show menu";}
 else
 {xx.cols = "170,*";
 btn.value = "Hide menu";}
 }
 
 function showModalPopUp(srv, host, hostname){

popUpObj=window.open("../right.php?srv="+srv+"&id=1&actid=1&modal=1&m_host="+host+"&m_hostname="+hostname+"","ModalPopUp","width=500," + "height=500");

popUpObj.focus();

LoadModalDiv();

}

</script>


<?php
// Javascripts END


//querys for reports

$queryCurrentStatusDown="SELECT DISTINCT
ph.devname,
case when (unix_timestamp(sysdate()) - (select max(date) from png_pingstat)) > 300 then 3 else 0 end,
from_unixtime((select max(date) from png_pingstat)),
unix_timestamp(sysdate())- statedate
FROM png_hosts ph
WHERE  ph.curstate=0 and ph.active=1 and ph.id in (".$queryHosts.")
order by INET_ATON(ph.name) asc";


$queryCurrentStatusUp="SELECT DISTINCT
ph.devname,
case when (unix_timestamp(sysdate()) - (select max(date) from png_pingstat)) > 300 then 3 else 1 end,
from_unixtime((select max(date) from png_pingstat)),
unix_timestamp(sysdate())- statedate
FROM png_hosts ph
WHERE  ph.curstate=1
order by INET_ATON(ph.name) asc";



$queryCurrentStatusOff="SELECT 
ph.devname,
'2',
from_unixtime((select max(date) from png_pingstat)),
''
FROM png_hosts ph
WHERE  ph.active=0
order by INET_ATON(ph.name) asc";

$queryCurrentStatusNmap="SELECT 
png_nmapstat.host,
png_nmapstat.hostname,
png_nmapstat.ports,
from_unixtime(png_nmapstat.date) 
from png_nmapstat 
    INNER JOIN (select host,max(date) as d from png_nmapstat 
		INNER JOIN png_nmaphosts ON png_nmapstat.host=png_nmaphosts.name 
	group by host) as tt ON png_nmapstat.date=tt.d and png_nmapstat.host=tt.host
";



$queryCurrentStatusDashboard="SELECT count(1),result  FROM 
(
(
	SELECT DISTINCT
ph.name,
ph.curstate result,
from_unixtime((select max(date) from png_pingstat)),
''

FROM png_hosts ph
where ph.active=1 and ph.curstate=1 and ph.id in (".$queryHosts.")
)
UNION ALL

(
SELECT DISTINCT
ph.name,
ph.curstate result,
from_unixtime((select max(date) from png_pingstat)),
''

FROM png_hosts ph
where ph.active=1 and ph.curstate=0  and ph.id in (".$queryHosts.")
)

) tmp
group by result
";

$queryLogTable="SELECT
FROM_UNIXTIME(p.date,'%Y-%m-%d %H:%i:%s') as d1,
h.devname,
p.alarm
FROM png_messages p, png_hosts h WHERE h.name=p.host and h.id in (".$queryHosts.") order by d1 desc limit 10";


//последняя дата опроса
$queryLastPoll="SELECT    
from_unixtime(max(date)) maxdate

from png_pingstat s  
group by host;";


$queryDiscoveredHosts="SELECT 
from_unixtime(pn.date), 
pn.host,
pn.hostname,
case when (select count(1) from png_hosts p where p.name=pn.host) > 0 then 'Да' else 'Нет' end
from png_nmapstat pn order by date desc, INET_ATON(pn.host) asc;";


$queryNmapCommands="SELECT 
from_unixtime(pn.date), 
pn.scanarray,
pn.is_executed,
from png_nmapcmd pn order by date desc;";





$queryCurrentStatusNmap="SELECT 
png_nmapstat.host,
png_nmapstat.hostname,
png_nmapstat.ports,
from_unixtime(png_nmapstat.date) 
from png_nmapstat 
    INNER JOIN (select host,max(date) as d from png_nmapstat 
		INNER JOIN png_nmaphosts ON png_nmapstat.host=png_nmaphosts.name 
	group by host) as tt ON png_nmapstat.date=tt.d and png_nmapstat.host=tt.host
";

///echo $queryCurrentStatusNmap;



//querys for reports end


///REPORTS HEADERS

$repheader="";
if($id==1)
$repheader= "<h2>Down </h2>";

if($id==2)
$repheader= "<h2>Online </h2>";

if($id==3)
$repheader= "<h2>Off </h2>";

if($id==4)
$repheader= "<h2>Discovered </h2>";

if($id==5)
$repheader= '<h2>Dashboard <input id="b1" type="button" value="Hide menu" onclick="doit(this)"></h2>';



$globalSS['repheader'] = $repheader;

///REPORTS HEADERS END
echo $repheader;







/////////// DOWN REPORT


if($id==1)
{
  

$json_result=doGetReportData($globalSS,$queryCurrentStatusDown,'template1.php');
	doPrintTable($globalSS,$json_result);

}

/////////// DOWN REPORT END

/////////// UP REPORT


if($id==2)
{
  

$json_result=doGetReportData($globalSS,$queryCurrentStatusUp,'template1.php');
	doPrintTable($globalSS,$json_result);

}

/////////// UP REPORT END

/////////// OFF REPORT

if($id==3)
{
  

$json_result=doGetReportData($globalSS,$queryCurrentStatusOff,'template1.php');
	doPrintTable($globalSS,$json_result);

}

/////////// OFF REPORT END


/////////// DISCOVER REPORT

if($id==4)
{
  
	$json_result=doGetReportData($globalSS,$queryDiscoveredHosts,'template5.php');
	doPrintTable($globalSS,$json_result);

}

/////////// DISCOVER REPORT END

/////////// DASHBOARD REPORT

if($id==5)
{



	if(!isset($_GET['actid'])){
		#24 часа
		$queryGetOnlineStatForGraph="
		SELECT tmp.fld_avgval,fld_date from (
		SELECT AVG( t.value ) fld_avgval,FROM_UNIXTIME(date,'%d.%m.%Y %H') fld_date
		FROM png_mod_user_trends t
		WHERE  (unix_timestamp(sysdate()) - 86400 ) <  date and t.userid=".$userid."
		GROUP BY FROM_UNIXTIME(date,'%d.%m.%Y %H')
		order by FROM_UNIXTIME(date,'%Y-%m-%d-%H') asc) tmp;
		";
 }
 else
 {
   if($_GET['actid']==30) {
	 #2-х минутки
	   $queryGetOnlineStatForGraph="
	   SELECT tmp.fld_avgval, from_unixtime(fld_date*120,'%d.%m.%Y %H:%i'), fld_date 
	   from ( 
		   SELECT AVG( t.value ) fld_avgval,(date DIV 120) fld_date 
		   FROM png_mod_user_trends t 
		   WHERE (unix_timestamp(sysdate()) - 3600 ) < date and t.userid=".$userid."
		   GROUP BY date DIV 120 order by 
		   FROM_UNIXTIME(date,'%Y-%m-%d %H:%i') asc) tmp; ";
   }
   if($_GET['actid']==31) {
	   #24 часа
	   $queryGetOnlineStatForGraph="
	   SELECT tmp.fld_avgval,fld_date from (
	   SELECT AVG( t.value ) fld_avgval,FROM_UNIXTIME(date,'%d.%m.%Y %H') fld_date
	   FROM png_mod_user_trends t
	   WHERE  (unix_timestamp(sysdate()) - 86400 ) <  date and t.userid=".$userid."
	   GROUP BY FROM_UNIXTIME(date,'%d.%m.%Y %H')
	   order by FROM_UNIXTIME(date,'%Y-%m-%d-%H') asc) tmp;
	   ";
   }
   if($_GET['actid']==32) {
	   #месяц
	   $queryGetOnlineStatForGraph="
	   SELECT tmp.fld_avgval,fld_date from (
	   SELECT AVG( t.value ) fld_avgval,FROM_UNIXTIME(date,'%d.%m.%Y') fld_date
	   FROM png_mod_user_trends t
	   WHERE  (unix_timestamp(sysdate()) - 2592000 ) <  date and t.userid=".$userid."
	   GROUP BY FROM_UNIXTIME(date,'%d.%m.%Y') 
	   order by FROM_UNIXTIME(date,'%Y-%m-%d') asc) tmp;
	   ";
   }

 }


 $row = doFetchOneQuery($globalSS, $queryLastPoll);

 echo "<h3>".$_lang['stLASTPOLLING']." ".$row[0]."</h3>";


 echo "<table><tr><td valign=top >";
 include("".$globalSS['root_dir']."/modules/Chart/module.php");

 $grap = new Chart($globalSS); #получим экземпляр класса и будем уже туда закидывать запросы на исполнение

$json_result=doGetReportData($globalSS,$queryCurrentStatusDashboard,'template3.php');
 doPrintTable($globalSS,$json_result);


 $json_result=doGetReportData($globalSS,$queryGetOnlineStatForGraph,'template4.php');

 
 $arrValues=array();
 $arrValues2=array();

 $arrValues=doGetArrayData($globalSS,$json_result,1);
 $arrValues2=doGetArrayData($globalSS,$json_result,2);

#соберем данные для графика
$userData['charttype']="gauge";
$userData['chartname']="Online";
$userData['charttitle']="";
$userData['arrSerie1']=$arrValues;
$userData['arrSerie2']=$arrValues2;
$userData['userid']=$userid;

 //create chart
 $pathtoimage = $grap->drawImage($userData);

 //display
 echo $pathtoimage;

echo "</td><td align=center width=80%>";	


echo '<h3><a href="reports.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=30">1H</a>&emsp;<a href="reports.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=31">1D</a>&emsp;<a href="reports.php?srv='.$globalSS['connectionParams']['srv'].'&id=5&actid=32">1M</a></h3>';









#соберем данные для графика
$userData['charttype']="line";
$userData['chartname']="Status";
$userData['charttitle']="";
$userData['arrSerie1']=$arrValues;
$userData['arrSerie2']=$arrValues2;
$userData['userid']=$userid;
 //create chart
 $pathtoimage = $grap->drawImage($userData);

 //display
 echo $pathtoimage;




 echo "</td></tr></table>";


echo "<table>
<tr>
	<td valign=top>";
	

$json_result=doGetReportData($globalSS,$queryCurrentStatusDown,'template1_cab.php');
	doPrintTable($globalSS,$json_result);

echo "</td> <td valign=top>";
$json_result=doGetReportData($globalSS,$queryLogTable,'template4_cab.php');
	doPrintTable($globalSS,$json_result);
echo "</td></tr></table>";

}

/////////// DASHBOARD REPORT END


/////////// HISTORY STATUS REPORT

if($id==92)
{

  $json_result=doGetReportData($globalSS,$queryHistoryStatus,'template2.php');
	doPrintTable($globalSS,$json_result);

}


$end=microtime(true);

$runtime=$end - $start;

echo "<br /><font size=2>".$_lang['stEXECUTIONTIME']." ".round($runtime,3)." ".$_lang['stSECONDS']."</font><br />";

echo $_lang['stCREATORS'];

?>


<form name=fastdateswitch_form onsubmit="return false;">


</form>


<?php

echo "



</body>
</html>
";

?>

