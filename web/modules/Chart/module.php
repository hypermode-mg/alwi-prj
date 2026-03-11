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
*                         File Birth   > <!#FB> 2022/09/28 21:52:47.264 </#FB>                                         *
*                         File Mod     > <!#FT> 2023/03/24 21:55:39.736 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.1.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
*/






class Chart
{
var $DataSet;
var $graphPchart;
var $maindir;
var $chartlib;
var $link;

function __construct($variables){ // 
    $this->vars = $variables;
	
	#clean directory for svg
	foreach (glob($this->vars['root_dir']."/pictures/*.svg") as $filename) {
	    unlink($filename);
	}

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

  function writeArraysToFile($userData) #write point arrays to file for external libs
  {

	  #data serie
 
	$fp = fopen($this->vars['root_dir'].'/modules/Chart/data/'.$userData['chartname'].'_val.txt', 'w');
    
		fputcsv($fp, $userData['arrSerie1'],';');

	fclose($fp);

	if($userData['arrSerie2'] == "")
		$userData['arrSerie2'] = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	 

	$fp = fopen($this->vars['root_dir'].'/modules/Chart/data/'.$userData['chartname'].'_label.txt', 'w');
		fputcsv($fp, $userData['arrSerie2'],';');
	fclose($fp);

}

  function drawImage($userData) #рисуем график
  {
	
	#прежде чем рисовать, запишем в файлы данные по графику.
	 $this->writeArraysToFile($userData);

	  //рисуем
	  
		return $this->drawImagePygal($userData);
	

	}


  function drawImagePygal($userData) #рисуем график
  {
	  //Тут просто. В зависимости от типа графика запускаем скрипт рисования. Возвращаем результат эхом.
	  if($userData['charttype']=="line"){
			echo passthru("python3 ".$this->vars['root_dir']."/modules/Chart/pygal/line.py ".$userData['chartname']."");
			echo file_get_contents($this->vars['root_dir'].'/modules/Chart/pictures/'.$userData['chartname'].'.svg');
	  }

	  if($userData['charttype']=="pie"){
			echo passthru("python3 ".$this->vars['root_dir']."/modules/Chart/pygal/pie.py ".$userData['chartname']."");
			echo file_get_contents($this->vars['root_dir'].'/modules/Chart/pictures/'.$userData['chartname'].'.svg');
	  }

	  if($userData['charttype']=="gauge"){
		echo passthru("python3 ".$this->vars['root_dir']."/modules/Chart/pygal/gauge.py ".$userData['chartname']."");
		echo file_get_contents($this->vars['root_dir'].'/modules/Chart/pictures/'.$userData['chartname'].'.svg');
  }


}



 

  function Install()
  	{
	echo "<script language=javascript>alert('This is system module, its already installed')</script>";

	 }
  
 function Uninstall() 
  {
	echo "<script language=javascript>alert('This is system module, you cant uninstall it')</script>";

  }


}
?>
