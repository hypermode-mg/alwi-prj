#!/usr/bin/perl

use DBI; # DBI  Perl!!!
use Time::Piece;
use Time::Seconds;


#==========================================================
my $host = "localhost"; #
my $port = "3306"; #
my $user = "mysql-user"; #
my $pass = "pass"; #
my $db = "hpinger"; #  
#==========================================================
#==========================================================





sub doQueryToDatabase {

    my $sqlquery=shift;

    if($dbtype==0){ #mysql
       $dbh_child1 = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);
    }

    
      $sth1 = $dbh_child1->prepare($sqlquery);
      $sth1->execute;
      $sth1->finish();
      $dbh_child1->disconnect();

}


sub doTrendData {

    

    if($dbtype==0){ #mysql
         $dbh_child = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);

         #тренд количество онлайн в процентах от общего количества
         $sqlquery = "INSERT INTO png_mod_trends (date, value) select unix_timestamp(sysdate()), 
         (select truncate(tmp.onn/(tmp.onn+tmp.off)*100,0) from (select (select count(1) from png_hosts t where t.active=1 and t.curstate=0) off, (select count(1) from png_hosts t where t.active=1 and t.curstate=1) onn) tmp); ";


    }

    
      $sth = $dbh_child->prepare($sqlquery);
      $sth->execute;
#      @row=$sth->fetchrow_array;
      $sth->finish();

      $dbh_child->disconnect();

}




#
doTrendData;
