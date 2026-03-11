#!/usr/bin/perl

use DBI; # DBI  Perl!!!

use Net::Ping;
use Time::Local;

#==========================================================
my $host = "localhost"; #
my $port = "3306"; #
my $user = "mysql-user"; #
my $pass = "pass"; #
my $db = "hpinger"; #  
#==========================================================
my $filetoparse="test111"; #name of file where info about nmapped host is.
#=======================CONFIGURATION END==============================


#$timeout=2; #таймаут ответа пинга в секундах
$dbh = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);



$sql_getscanarray="select scanarray,id from png_nmapcmd where is_executed=0 and not exists(select id from png_nmapcmd where is_executed=1) limit 1;";
$sth = $dbh->prepare($sql_getscanarray);
$sth->execute; #

@row=$sth->fetchrow_array;

$idcmd=$row[1];

$scanarray=$row[0];

if($scanarray ne "") {

open(OUT, ">>nmapstat.log"); 

$now=timelocal(localtime());


$sql_updatescanarray="update png_nmapcmd set is_executed=1 where id=$idcmd";
$sth = $dbh->prepare($sql_updatescanarray);
$sth->execute; #

$hosttonmap="";
 

#print $hosttonmap;
$makenmap = `nmap -oG $filetoparse -sP $scanarray`;

#
#open log file for writing
open(IN, "<$filetoparse");

while(my $line=<IN>) {

$line=~s|\(||g; #delete (
     

@matchs= ($line=~ /Host: (.+)\)/);


if($matchs[0] ne ""){

@item = split " ", $matchs[0];


print OUT $now." ".$item[0]." ".$item[1]."\n";
}

}

close(IN);

close(OUT);

$sql_updatescanarray="update png_nmapcmd set is_executed=2 where id=$idcmd";
$sth = $dbh->prepare($sql_updatescanarray);
$sth->execute; #

} #if($scanarray ne "") {
#close log file

