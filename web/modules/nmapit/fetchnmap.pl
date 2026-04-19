#!/usr/bin/perl

use DBI; # DBI  Perl!!!

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

print $now=localtime;

$dbh = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);


if(-e "nmapstat.log") {
$sql_clear="truncate table png_nmapstat;";
$sth = $dbh->prepare($sql_clear);
$sth->execute; #
}


open(IN, "<nmapstat.log"); 

while (my $line=<IN>) {
@item = split " ", $line; 


if($count==0)
    {
    $sqltext="INSERT INTO png_nmapstat (date,host,hostname) VALUES";
    }
    
$count++;

if(eof or $count>=$count_lines_for_one_insert)
    {
    $sqltext=$sqltext."($item[0],'$item[1]','$item[2]')";
    $count=0;
    $sth = $dbh->prepare($sqltext);
    $sth->execute; #
    $sqltext="";
    }


if($count<$count_lines_for_one_insert and $count>0)
    {
    $sqltext=$sqltext."($item[0],'$item[1]','$item[2]'),";
    }


}


close(IN);




print "\n";
print $now=localtime;
#$rc = $dbh->disconnect;  #
unlink("test111");
unlink("nmapstat.log");
