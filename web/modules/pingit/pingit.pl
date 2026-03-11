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

#==========================================================

$timeout=2; #таймаут ответа пинга в секундах




$dbh = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);


$sql_getscanarray="select count(1) from png_nmapcmd where is_executed=1;";
$sth = $dbh->prepare($sql_getscanarray);
$sth->execute; #

@row=$sth->fetchrow_array;

#if nmap is executed, stop ping scan
if($row[0]!=0)
{
exit;

}




$sql_getlisthosts="select name from png_hosts where active=1;";
$sth = $dbh->prepare($sql_getlisthosts);
$sth->execute; #





open(OUT, ">>pngstat.log"); 

$now=timelocal(localtime());
 
$p=Net::Ping->new("icmp", $timeout) or die bye ;

 while (($host) = $sth->fetchrow_array) {

if ($p->ping($host))
{
print OUT $now." ". $host." 1\n";
} 
else  {
print OUT $now." ". $host." 0\n";
}
   }
 
$p->close();
# while (($host,$rtt,$ip) = $p->ack) {
# print OUT "HOST: $host [$ip] ACKed in $rtt seconds.\n";
# }
close(OUT);


