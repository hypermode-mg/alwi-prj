<?php


#Build date Thursday 6th of August 2020 12:58:11 PM
#Build revision 1.2

$start=microtime(true);


function pathUrl($dir = __DIR__){

    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));

    //HTTPS or HTTP
    $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    //HOST
    $root .= '://' . $_SERVER['HTTP_HOST'];

    //ALIAS
    if(!empty($_SERVER['CONTEXT_PREFIX'])) {
        $root .= $_SERVER['CONTEXT_PREFIX'];
        $root .= substr($dir, strlen($_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ]));
    } else {
        $root .= substr($dir, strlen($_SERVER[ 'DOCUMENT_ROOT' ]));
    }

    $root .= '/';

    return $root;
}


$root_http=str_replace('install/', '', pathUrl());	

		#write config from step2
			if(isset($_POST['submit'])){
				$config['enabled']=1;
				$config['srvname']=$_POST['srvname'];
				$config['db']=$_POST['db'];
				$config['user']=$_POST['user'];
				$config['pass']=$_POST['pass'];
				$config['address']=$_POST['address'];
				$config['srvdbtype']=$_POST['srvdbtype'];

					
			}
			#set default config as new config
		
			copy("../config.php.default","../config.php");
			

			
			if($_POST['createtables']){
			
					#try to create tables
							
							#mysql
					if($_POST['srvdbtype']==0)
					{
					
						
						$query=file_get_contents("../createdb/createdb.sql");
					
					try {
						$con=mysqli_connect($_POST['address'],$_POST['user'],$_POST['pass'],$_POST['db']);
					}
					catch (Exception $e) {
						echo 'Error: php-mysqli not activated. Restart web-server and try again.';
						exit;
					}
						if (!$con) 	echo "Error: Cant connect to mysql server." . PHP_EOL;
						
						else
						
						if(!mysqli_multi_query($con,$query))
						
						echo "Error: Cant execute query." . PHP_EOL;
						
						else 				
						
						file_put_contents('../conf/db'.$start.'.php', '<?php return '. var_export($config, true) . ';?>');

						
					
					}
					


						file_put_contents('../conf/db'.$start.'.php', '<?php return '. var_export($config, true) . ';?>');

						
					
					}
					
		


?>

<html><head>

<meta http-equiv="Cache-Control" content="no-cache"> 	
<title><?php echo "Install System";?></title>

<link rel="stylesheet" type="text/css" href="css/install.css"/>

</head>

<body>
	<div id="page" align="center">
		<div id="page_in" align="left">

	
			<header align="center">
				<div id="logo"><img src="images/logo.png" style="width: 116px; height: auto;" align="left"></div>
				
				<h1>
					Install System 
				</h1>
			

			</header>
		</div>
			<div class="clr"></div>

<div class="clr"></div>	
<div class="line"></div>


	<article>
		<div id="left_col">
		
		</div>
		
<table cellspacing="2" cellpadding="2">
	<tr>
		<td><img src="images/1off.png" style="width: 50px; height: auto;" alt="Step 1" /></td>
		<td><img src="images/2off.png" style="width: 50px; height: auto;" alt="Step 2" /></td>
		<td><img src="images/3on.png" style="width: 50px; height: auto;" alt="Step 3" /></td>

	</tr>
</table>

<br/>
  <div >
	  
      Process complete. The installation process has completed, at your request database tables were created. Config file has been reset and all pre-installation tests have passed. Thank you, and here is your <a href="<?php echo $root_http;  ?>">AlertsOnWings</a>. If something went wrong, you can type in address string http://your_ip/path_where_is_installed.
	  <br>Default password to log in <b>admin</b>
    </div>

	</article>
</div>

<div class="clr"></div>	
<div class="line"></div>

</body></html>