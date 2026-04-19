#!/usr/bin/perl

use DBI; # DBI  Perl!!!
use Time::Piece;
use Time::Seconds;
use Time::Local;

#==========================================================
my $host = "localhost"; #
my $port = "3306"; #
my $user = "mysql-user"; #
my $pass = "pass"; #
my $db = "hpinger"; #  
#==========================================================
#==========================================================



my $count=0;

my $lastdate=0;

my $count_lines_for_one_insert=100; #how much INSERT for one 'transaction'

my $sqltext="";

my $sql_getlastdate="";

print $now=localtime;

$dbh = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);

$sql_getlastdate="select max(date) from png_pingstat";
$sth = $dbh->prepare($sql_getlastdate);
$sth->execute; #

@row=$sth->fetchrow_array;
$lastdate=$row[0];

$now1=timelocal(localtime());


open(IN, "<pngstat.log"); 

while (my $line=<IN>) {
@item = split " ", $line; 

if($item[0]>$lastdate)
{

if($count==0)
    {
    $sqltext="INSERT INTO png_pingstat (date,host,result) VALUES";
    }
    
$count++;

if(eof or $count>=$count_lines_for_one_insert)
    {
    $sqltext=$sqltext."($now1,'$item[1]','$item[2]')";
    $count=0;
    $sth = $dbh->prepare($sqltext);
    $sth->execute; #
    $sqltext="";
    }


if($count<$count_lines_for_one_insert and $count>0)
    {
    $sqltext=$sqltext."($now1,'$item[1]','$item[2]'),";
    }

}
}

close(IN);
unlink('pngstat.log');

#сгенерируем уведомления если статус обновился

$prev_lastdate=$lastdate;
#последнюю дату и предпоследнюю возьмем
$sql_getlastdate="select max(date) from png_pingstat";
$sth = $dbh->prepare($sql_getlastdate);
$sth->execute; #

@row=$sth->fetchrow_array;
$lastdate=$row[0];


$sql="
select * from (
select 
name,
pss.curstate prev_res,
ifnull((select result from png_pingstat ps1 where ps1.host=pss.name and date=$lastdate),1) res,
pss.numtries,
pss.statedate
 from png_hosts pss where pss.active=1) tmp
 
 
 ;";



$sth = $dbh->prepare($sql);
$sth->execute; #

$alm_text="";
$numtries=0;



while (@row = $sth->fetchrow_array())

    {

$statedate=$row[4]; #дата предыдущего состояния

#фильтр помех. если три опроса подряд один и тот же статус - тогда сменим статус
if($row[3]>=3) {

$sql_updatestate="";
#$id = $row[0];
$alm_text="";
#если онлайн -> оффлайн
if($row[1]>$row[2])
{
$sql_updatestate="update png_hosts set curstate=0, statedate='$lastdate' where name='$row[0]'";
$alm_text=$row[0]." change status Online to <font color=red>OFFLINE</font> ";
}

#если оффлайн -> онлайн
if($row[1]<$row[2])
{
$sql_updatestate="update png_hosts set curstate=1, statedate='$lastdate' where name='$row[0]'";

$diffdate=$lastdate-$statedate;

$duration = Time::Seconds->new($diffdate);


$alm_text=$row[0]." change status Offline to <font color=green>ONLINE</font>. Time offline=".($duration->pretty)."";



}

if($row[1]!=$row[2])
{
$sql="insert into png_messages (date,host,alarm) VALUES ('$lastdate','$row[0]','$alm_text')";
$sthr = $dbh->prepare($sql);
$sthr->execute; #

$sthr = $dbh->prepare($sql_updatestate);
$sthr->execute; #

}
$numtries=0;
$sql_update="update png_hosts set numtries='$numtries' where name='$row[0]'";
$sthr = $dbh->prepare($sql_update);
$sthr->execute; #


} #if numtries

else
{
    #если появилось несоответствие статусов то начинаем считать попытки
    if($row[1]!=$row[2]){
        $numtries=$row[3]+1;
    }   
    else
    {
        $numtries=0;

    }
$sql_update="update png_hosts set numtries='$numtries' where name='$row[0]'";
$sthr = $dbh->prepare($sql_update);
$sthr->execute; #



}





} #while

$sql_getcount="select count(1) from png_pingstat";
$sth = $dbh->prepare($sql_getcount);
$sth->execute; #

@row=$sth->fetchrow_array;
$count=$row[0];

if($count>20000){
$sql_delstat="delete from png_pingstat where date < unix_timestamp(sysdate()) - 1800";
$sth = $dbh->prepare($sql_delstat);
$sth->execute; #


}


print "\n";
print $now=localtime;
#$dbh->commit;
#$dbh->disconnect;  #
