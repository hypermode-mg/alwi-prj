<?php
#попробуем работать вместо кук с сессиями
session_start();
 

$globalSS['root_http']=pathUrl();	

$i=0;

#try to get conf
$path    = $globalSS['root_dir'].'/conf/';
$files = array_diff(scandir($path), array('.', '..','.gitignore'));


foreach($files as $file) {
$config= include 'conf/'.$file;

if($config['enabled']!=0)
    {
   $srvname[$i]=$config['srvname'];
   $db[$i]=$config['db'];
   $user[$i]=$config['user'];
   $pass[$i]=$config['pass'];
   $address[$i]=$config['address'];
   $srvdbtype[$i]=$config['srvdbtype'];
   $globalSS['config'][$i]=$config;
   $i++;
   
}

}

if(isset($_SESSION['hle_hash']))
doUpdateHashLogin($_SESSION['hle_hash']);


if(isset($_SESSION['user_login']) and $_SESSION['user_login']=='admin')
    doDeleteOldSessions();

function isAuth(): bool
{
    return !!(($_SESSION['user_id'] and $_SESSION['user_login']==doGetHashCabLogin($_SESSION['hle_cab_hash'])  ) ?? false);
}

function isAuthAdmin(): bool
{
      return !!(($_SESSION['user_id'] and $_SESSION['user_login']=='admin' and $_SESSION['user_login']==doGetHashLogin($_SESSION['hle_hash']) and doAuthEnabled() )?? false);


}

#Функция чтения хэша
function doGetHashLogin($hash){
      
    $json_session_data=file_get_contents(__DIR__.'/modules/PrivateAuth/hash/'.$hash);

    $session_data = array();

    $session_data = json_decode($json_session_data,true);


     return $session_data['user_login'];
      
}

#Функция чтения хэша
function doGetHashCabLogin($hash){
      
    $json_session_data=file_get_contents(__DIR__.'/cab/modules/PrivateAuth/hash/'.$hash);

    $session_data = array();

    $session_data = json_decode($json_session_data,true);


     return $session_data['user_login'];
      
}


        #Функция обновим хэш (дату)
function doUpdateHashLogin($hash){

    $json_session_data=file_get_contents(__DIR__.'/modules/PrivateAuth/hash/'.$hash);

      if((time() - filemtime(__DIR__.'/modules/PrivateAuth/hash/'.$hash))>600 )
    file_put_contents(__DIR__.'/modules/PrivateAuth/hash/'.$hash,$json_session_data);
        
}

    #Удалим старые сессии
function doDeleteOldSessions(){
#try to get conf
$path    = __DIR__.'/modules/PrivateAuth/hash/';
$files = array_diff(scandir($path), array('.', '..','.gitignore'));

    foreach($files as $file) {
        if((time() - filemtime(__DIR__.'/modules/PrivateAuth/hash/'.$file) > 1000 ))
            unlink(__DIR__.'/modules/PrivateAuth/hash/'.$file);
        
        }

    }

#здесь модули не устанавливаются, поэтому всегда true
function doAuthEnabled(){
      
    return true;      
}

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

?>