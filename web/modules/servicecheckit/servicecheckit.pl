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

$sql_getlisthosts="select phs.host,phs.port from png_hosts_services phs, png_hosts ph where ph.name=phs.host and ph.active=1;";
$sth = $dbh->prepare($sql_getlisthosts);
$sth->execute; #





open(OUT, ">>pngstat.log"); 

$now=timelocal(localtime());
 
$p=Net::Ping->new("tcp", $timeout) or die bye ;



 while (($host,$port) = $sth->fetchrow_array) {

$p->port_number($port);

if ($p->ping($host))
{
print OUT $now." ". $host." ". $port." 1\n";
} 
else  {
print OUT $now." ". $host." ". $port." 0\n";
}
   }
 
$p->close();
# while (($host,$rtt,$ip) = $p->ack) {
# print OUT "HOST: $host [$ip] ACKed in $rtt seconds.\n";
# }
close(OUT);


