<?php

/*
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> module.php </#FN>                                                      
*                         File Birth   > <!#FB> 2022/09/28 21:52:47.271 </#FB>                                         *
*                         File Mod     > <!#FT> 2024/06/03 21:00:45.777 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.2.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/


class MailMan
{

function __construct($variables){ // 
    $this->vars = $variables;

	include_once(''.$this->vars['root_dir'].'/lib/functions/function.database.php');

	include(''.$this->vars['root_dir'].'/lang/'.$this->vars['language']);

	if (file_exists("langs/".$this->vars['language']))
		include("langs/".$this->vars['language']);  #подтянем файл языка если это возможно
	else	
		include("langs/en"); #если перевода на язык нет, то по умолчанию тянем английский.  	
	$this->lang = $_lang;

}

  function GetDesc()
  {
	  
      return $this->lang['stMODULEDESC']; 
  }

#печать всех сообщений
function doPrintAll($globalSS){


	
    include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
    
	$_lang=$this->lang;
	
	if($globalSS['connectionParams']['dbtype']==0)

	$queryAll="SELECT 
		from_unixtime(eventdate,'%d-%m-%y %k:%i:%s'),
		message,
		useremail,
		sentstate
	 FROM png_mod_mailman_users m WHERE eventdate > unix_timestamp() - 86400
	 ORDER BY id desc;";



$result=doFetchQuery($globalSS, $queryAll);
$numrow=1;
$file_count_msg=$globalSS['root_dir']."/api/count_msg";

echo $this->lang['stDATESYNCTGBOT'].": ".date("Y-m-d H:i:s", filemtime($file_count_msg));
echo "<br>";
echo "<br>";

echo "<a href=index.php?srv=".$this->vars['connectionParams']['srv']."&actid=1>Clean mailbox</a>";

echo "<br /><br />";
echo "<table class=datatable>

<tr>
  <th class=unsortable><b>#</b></th>
	  	<th><b>Date and time</b></th>
 		<th><b>".$_lang['stLOGMESSAGE']."</b></th>
 		<th><b>E-mail</b></th>
		 <th><b>SENTSTATE</b></th>
 </tr>";

foreach($result as $line) {

  
 echo "<tr >

		<td >".$numrow."</td>
		<td align=center >".$line[0]."</td>                 
		<td  >".$line[1]."&nbsp;</td>
		<td  >".$line[2]."&nbsp;</td>
		<td  >".$line[3]."&nbsp;</td>

			 
	  </tr>";
  $numrow++;
}
echo "</table>";
echo "<br />";
echo "<br />";


    
    }


#очистка всех сообщений
function doCleanAll($globalSS){


	
		include_once(''.$globalSS['root_dir'].'/lib/functions/function.database.php');
		
		$_lang=$this->lang;
		
	
		$queryCleanAll="delete from png_mod_mailman_users";
	
	
	
	doQuery($globalSS, $queryCleanAll);

	echo "<script language=javascript>alert('Mailbox cleaned')</script>";
	
		
		}

  function Install()
  {

	#если модуль уже есть, то вернемся.
#	if(doQueryExistsModule($this->vars,'MailMan')>0) {
#		echo "<script language=javascript>alert('Module already installed')</script>";
#		return;
#	}

# Table structure for table `scsq_mod_categorylist`

		if($this->vars['connectionParams']['dbtype']==0 ) {

		$CreateTable = "
			CREATE TABLE IF NOT EXISTS png_mod_mailman (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  eventdate int(11) NOT NULL,
			  message varchar(500) NOT NULL,
			  sentstate tinyint(4) DEFAULT '0',
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";

		$UpdateParameters="INSERT INTO `png_modules_param` (`module`, `param`, `val`, `switch`, `comment`) VALUES
		('MailMan', 'Last Date Send log', unix_timestamp(sysdate()), 0, 'Last Date Send log')";
	
		}

	


		doQuery($this->vars, $CreateTable) or die ("Can`t create table for MailMan");

		doQuery($this->vars, $UpdateParameters) or die ("Can`t update parameters table");


		echo "<script language=javascript>alert('".$this->lang['stINSTALLED']."')</script>";

 }
  
 function Uninstall() #добавить LANG
  {

		
		$query = "
		DROP TABLE IF EXISTS png_mod_mailman;
		";

	
		$UpdateParameters="DELETE from png_modules_param  where module='MailMan'";


		doQuery($this->vars, $query) or die ("Can`t drop table for MailMan");

		doQuery($this->vars, $UpdateParameters) or die ("Can`t update params");


		echo "<script language=javascript>alert('".$this->lang['stUNINSTALLED']."')</script>";
		
  }


}
?>
