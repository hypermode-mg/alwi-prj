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

         $sqlGetUsername="select login, id from png_users u where u.active=1;";
         
         $sth = $dbh_child->prepare($sqlGetUsername);
            $sth->execute;
      
         while(@rowusers=$sth->fetchrow_array) {
    

            $sqlGetHostsId="select h.host_id from png_hostingroup h, png_groupinuser g, png_users u
            where u.login='".$rowusers[0]."' and u.id=g.user_id and g.group_id=h.group_id";
            $hostsList="0";

            $sth1 = $dbh_child->prepare($sqlGetHostsId);
            $sth1->execute;

            while(@rowhosts=$sth1->fetchrow_array) {
               $hostsList=$hostsList.",".$rowhosts[0];
            }
            $sth1->finish();


   #тренд количество онлайн в процентах от общего количества
         $sqlquery = "INSERT INTO png_mod_user_trends (userid, date, value) select '".$rowusers[1]."', unix_timestamp(sysdate()), 
         (select truncate(tmp.onn/(tmp.onn+tmp.off)*100,0) from (select (select count(1) from png_hosts t where t.active=1 and t.curstate=0 and t.id in (".$hostsList.")) off, (select count(1) from png_hosts t where t.active=1 and t.curstate=1 and t.id in (".$hostsList.")) onn) tmp); ";
    



      $sth2 = $dbh_child->prepare($sqlquery);
      $sth2->execute;
      $sth2->finish();

         }

         $sth->finish();

    }



      $dbh_child->disconnect();

}




#
doTrendData;
