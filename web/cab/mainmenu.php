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

 

include("../config.php");

	#если нет авторизации, сразу выходим
	if (!isAuth())
	{
		header("Location: ".$globalSS['root_http']."cab/modules/PrivateAuth/login.php"); exit();
	}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<!-- The themes file -->
<link rel="stylesheet" type="text/css" href="<?php echo $globalSS['root_http']; ?>/themes/<?php echo $globalSS['globaltheme']; ?>/global.css"/>

<!-- The xtree script file -->
<script src="../javascript/xtree.js"></script>


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
	


";
echo "rootproxy.add(new WebFXTreeItem('Logout','javascript:GoLink(\'".$globalSS['root_http']."cab/modules/PrivateAuth/logout.php\')','','',''));";

$srv++;

}
else
{
echo "

//Second Level	
//Если проблемы с подключением разместим тестовую страницу.
	rootproxy.icon = 'img/themes/default/DisconnectedDatabase.png';
	rootproxy.openIcon = 'img/themes/default/DisconnectedDatabase.png';
	

	";
	
$srv++;
continue;

}  

}

//Пункт для добавления удаления БД
echo "

document.write(tree);
}
</script>
</div>";

?>

</body>
</html>
