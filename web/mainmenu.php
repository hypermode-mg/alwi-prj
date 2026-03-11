<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> mainmenu.php </#FN>                                                    
*                         File Birth   > <!#FB> 2022/07/04 21:14:05.347 </#FB>                                         *
*                         File Mod     > <!#FT> 2022/07/04 21:16:03.979 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.0.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/

 

include_once("config.php");

	#если нет авторизации, сразу выходим
	if (!isAuthAdmin())
	{
		header("Location: ".$globalSS['root_http']."/modules/PrivateAuth/login.php"); exit();
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<!-- The themes file -->
<link rel="stylesheet" type="text/css" href="<?php echo $globalSS['root_http']; ?>/themes/<?php echo $globalSS['globaltheme']; ?>/global.css"/>

<!-- The xtree script file -->
<script src="javascript/xtree.js"></script>


<script language=JavaScript>
function TwoByOne(frame1, frame2)
{
parent.left.location.href=frame1;
parent.right.location.href=frame2;
}

function GoReport(srv,id)
{

parent.right.location.href='reports/reports.php?srv='+srv+'&id='+id;

}


function GoRightReport(srv,id)
{
parent.right.location.href='right.php?srv='+srv+'&id='+id;
}

function GoDicts(srv,id,dname)
{
parent.right.location.href='right.php?srv='+srv+'&id='+id+'&dname='+dname;
}


function GoModule(srv,id,modulename)
{
parent.right.location.href='modules/'+modulename+'/index.php?srv='+srv+'&id='+id;
}


function GoLink(dest_link,dest_target)
{
parent.right.location.href=dest_link;
}

function GoInternetLink(dest_link)
{
	window.open(dest_link, '_blank');
}


</script>

</head>

<body class="browser">


	<div class="logo">
		<a href="right.php" target="right">
			
		</a>
	</div>


<?php


echo "
<div style='float:left; top: 70px; left: 10px; height: 85%; width: 100%; padding: 5px; overflow:auto;'>
<script language=JavaScript>

webFXTreeConfig.rootIcon		= 'img/themes/default/Servers.png';
webFXTreeConfig.openRootIcon	= 'img/themes/default/Servers.png';
webFXTreeConfig.folderIcon		= '';
webFXTreeConfig.openFolderIcon	= '';
webFXTreeConfig.fileIcon		= 'img/themes/default/Report.png';
webFXTreeConfig.iIcon			= 'img/themes/default/I.png';
webFXTreeConfig.lIcon			= 'img/themes/default/L.png';
webFXTreeConfig.lMinusIcon		= 'img/themes/default/Lminus.png';
webFXTreeConfig.lPlusIcon		= 'img/themes/default/Lplus.png';
webFXTreeConfig.tIcon			= 'img/themes/default/T.png';
webFXTreeConfig.tMinusIcon		= 'img/themes/default/Tminus.png';
webFXTreeConfig.tPlusIcon		= 'img/themes/default/Tplus.png';
webFXTreeConfig.blankIcon		= 'img/themes/default/blank.png';
webFXTreeConfig.loadingIcon		= 'img/themes/default/Loading.gif';
webFXTreeConfig.loadingText		= 'Loading...';
webFXTreeConfig.errorIcon		= 'img/themes/default/ObjectNotFound.png';
webFXTreeConfig.errorLoadingText = 'Error Loading';
webFXTreeConfig.reloadText		= 'Click to reload';

if (document.getElementById) {
	var tree = new WebFXTree('ROOT');
	tree.setBehavior('classic');
";

$variableSet = array();

for($i=0;$i<count($srvname);$i++)
{

$addr=$address[$i];
$usr=$user[$i];
$psw=$pass[$i];
$dbase=$db[$i];
$dbtype=$srvdbtype[$i];


$variableSet['addr']=$addr;
$variableSet['usr']=$usr;
$variableSet['psw']=$psw;
$variableSet['dbase']=$dbase;
$variableSet['dbtype']=$dbtype;

$globalSS['connectionParams'] = $variableSet;


echo "//First Level
	var rootproxy = new WebFXTreeItem('".$srvname[$srv]."');

	tree.add(rootproxy);";



if(doConnectToDatabase($globalSS['connectionParams'])!="ErrorConnection")
{

echo "

rootproxy.add(new WebFXTreeItem('Dashboard','javascript:GoReport(".$srv.",5)'));
rootproxy.add(new WebFXTreeItem('Down','javascript:GoReport(".$srv.",1)'));
rootproxy.add(new WebFXTreeItem('Discovered','javascript:GoReport(".$srv.",4)'));
rootproxy.add(new WebFXTreeItem('Online','javascript:GoReport(".$srv.",2)'));
rootproxy.add(new WebFXTreeItem('Off','javascript:GoReport(".$srv.",3)'));
rootproxy.add(new WebFXTreeItem('Notification','javascript:GoRightReport(0,5)','','img/themes/default/Statistics.png','img/themes/default/Statistics.png'));
rootproxy.add(new WebFXTreeItem('Sensors','javascript:GoReport(0,7)'));


//Devices
var devices = new WebFXTreeItem('".$_lang['stDEVICES']."');

rootproxy.add(devices);

devices.add(new WebFXTreeItem('".$_lang['stHOSTS']."','javascript:GoRightReport(".$srv.",1);','','img/themes/default/Reports.png','img/themes/default/Reports.png'));
devices.add(new WebFXTreeItem('".$_lang['stGROUPS']."','javascript:GoRightReport(".$srv.",3);','','img/themes/default/Reports.png','img/themes/default/Reports.png'));


//Misc
var dict = new WebFXTreeItem('".$_lang['stDICTIONARIES']."');

rootproxy.add(dict);


dict.add(new WebFXTreeItem('".$_lang['stDICTVENDORS']."','javascript:GoDicts(".$srv.",10,\'vendor\')','','img/themes/default/Reports.png','img/themes/default/Reports.png'));
dict.add(new WebFXTreeItem('".$_lang['stDICTMODELS']."','javascript:GoDicts(".$srv.",10,\'model\')','','img/themes/default/Reports.png','img/themes/default/Reports.png'));
dict.add(new WebFXTreeItem('".$_lang['stDICTTYPES']."','javascript:GoDicts(".$srv.",10,\'devtype\')','','img/themes/default/Reports.png','img/themes/default/Reports.png'));
dict.add(new WebFXTreeItem('".$_lang['stDICTOSES']."','javascript:GoDicts(".$srv.",10,\'os\')','','img/themes/default/Reports.png','img/themes/default/Reports.png'));

var tools = new WebFXTreeItem('".$_lang['stTOOLS']."');

rootproxy.add(tools);

tools.add(new WebFXTreeItem('".$_lang['stFINDDEVICES']."','javascript:GoRightReport(".$srv.",2);','','img/themes/default/Search.png','img/themes/default/Search.png'));
tools.add(new WebFXTreeItem('".$_lang['stUSERS']."','javascript:GoRightReport(".$srv.",6);','','img/themes/default/Users.png','img/themes/default/Users.png'));

var systemtools = new WebFXTreeItem('".$_lang['stSYSTEMTOOLS']."');

rootproxy.add(systemtools);

systemtools.add(new WebFXTreeItem('".$_lang['stMAILPOSTUSERS']."','javascript:GoLink(\'".$globalSS['root_http']."modules/MailMan/index.php\')','','img/themes/default/Columns.png','img/themes/default/Columns.png'));


";

echo "rootproxy.add(new WebFXTreeItem('Logout','javascript:GoLink(\'".$globalSS['root_http']."modules/PrivateAuth/logout.php\')','','',''));";

$srv++;

}
else
{
echo "

//Second Level	
//Если проблемы с подключением разместим тестовую страницу.
//	rootproxy.add(new WebFXTreeItem('Test connection page','javascript:GoRightReport(".$srv.",999)'));
	rootproxy.icon = 'img/themes/default/DisconnectedDatabase.png';
	rootproxy.openIcon = 'img/themes/default/DisconnectedDatabase.png';
	

	";
	
$srv++;
continue;

}  

}

//Пункт для добавления удаления БД
echo "
    tree.add(new WebFXTreeItem('".$_lang['stEDITCONN']."','javascript:GoRightReport(0,8)','','img/themes/default/Processes.png','img/themes/default/Processes.png'));


document.write(tree);
}
</script>
</div>";

?>

</body>
</html>
