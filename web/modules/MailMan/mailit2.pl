#!/usr/bin/perl

=cut
<!#CR>
************************************************************************************************************************
*                                                    Copyrigths ©                                                      *
* -------------------------------------------------------------------------------------------------------------------- *
* -------------------------------------------------------------------------------------------------------------------- *
*                                           File and License Informations                                              *
* -------------------------------------------------------------------------------------------------------------------- *
*                         File Name    > <!#FN> mailit2.pl </#FN>                                                        
*                         File Birth   > <!#FB> 2022/06/10 21:31:40.946 </#FB>                                         *
*                         File Mod     > <!#FT> 2022/10/18 21:32:37.116 </#FT>                                         *
*                         License      > <!#LT> ERROR: no License name provided! </#LT>                                
*                                        <!#LU>  </#LU>                                                                
*                                        <!#LD> MIT License                                                            
*                                        GNU General Public License version 3.0 (GPLv3) </#LD>                         
*                         File Version > <!#FV> 1.1.0 </#FV>                                                           
*                                                                                                                      *
</#CR>
=cut

   #SMTP SSL!!!
   use DBI;
   use Net::SMTP::SSL;

#=======================CONFIGURATION BEGIN============================

my $dbtype = "0"; #type of db - 0 - MySQL, 1 - PostGRESQL

#mysql default config
if($dbtype==0){
$host = "localhost"; # host s DB
$port = "3306"; # port DB
$user = "mysql-user"; # username k DB
$pass = "pass"; # pasword k DB
$db = "hpinger"; # name DB
}


#mail info
# dont change ' to " !!!
 

$mail_from = 'from@mail.com'; #login to mailserver
$mail_password = 'yoursecretpass'; #password to mailserver

$mailserver = 'smtp.mail.com';

$mailport = 465;

$linkToAdmin="http://localhost/hle2";

#=======================CONFIGURATION END============================


$datestart=0;


#возьмем последнюю дату и чтобы она была одинаковой на момент исполнения обоих Grab
sub doGetLastDate {

    my $sqlquery=shift;

    if($dbtype==0){ #mysql
       $dbh_child1 = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);
    }

    
           $sqlquery = "select ifnull(max(eventdate),unix_timestamp(sysdate())-60) from png_mod_mailman_users ;";

          
         $sth3 = $dbh_child1->prepare($sqlquery);
         $sth3->execute;
         @row=$sth3->fetchrow_array;
         $sth3->finish();

$datestart = $row[0];

}



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


sub doGrabMessagesToAdmin {

    

    if($dbtype==0){ #mysql

   $dbh_child = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);

         $sqlGetUsername="select login, email from png_users u where u.active=1 and length(u.email)>0 and u.login='admin';";
         
         $sth = $dbh_child->prepare($sqlGetUsername);
            $sth->execute;

   
    
         while(@rowusers=$sth->fetchrow_array) {
          $sqlquery = "INSERT INTO png_mod_mailman_users (eventdate, message, useremail) select date, 
         CASE when LOCATE('Online to',m.alarm) > 0 then concat('PROBLEM: ',ph.location,' ',ph.devname, ' OFF-LINE',';',m.alarm,' <br><a href=\"','".$linkToAdmin."','/right.php?srv=0&id=1&actid=3&hostid=', ph.id,'\">Открыть карточку</a>')
         when LOCATE('Offline to',m.alarm) > 0 then concat('OK: ',ph.location,' ',ph.devname, ' ON-LINE',';',m.alarm,' <br><a href=\"','".$linkToAdmin."','/right.php?srv=0&id=1&actid=3&hostid=', ph.id,'\">Открыть карточку</a>')
         end alm,
         '".$rowusers[1]."'
          from png_messages m, png_hosts ph where ph.name=m.host and date>".$datestart."";
  
  


    
      $sth1 = $dbh_child->prepare($sqlquery);
      $sth1->execute;
      $sth1->finish();


    }


      $dbh_child->disconnect();
    }

#  return @row;

}




sub doGrabMessages {


    

    if($dbtype==0){ #mysql
         $dbh_child = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);

         $sqlGetUsername="select login, email from png_users u where u.active=1 and length(u.email)>0 and u.login<>'admin';";
         
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



     


         $sqlquery = "INSERT INTO png_mod_mailman_users (eventdate, message, useremail) select date, 
         CASE when LOCATE('Online to',m.alarm) > 0 then concat('PROBLEM: ',ph.location,' ',ph.devname, ' OFF-LINE',';',m.alarm)
         when LOCATE('Offline to',m.alarm) > 0 then concat('OK: ',ph.location,' ',ph.devname, ' ON-LINE',';',m.alarm)
         end alm, 
         '".$rowusers[1]."'
         from png_messages m, png_hosts ph where ph.id in (".$hostsList.") and 
         ph.name=m.host and date>".$datestart."";


      $sth2 = $dbh_child->prepare($sqlquery);
      $sth2->execute;
      $sth2->finish();

         }

         $sth->finish();

   
    }


      $dbh_child->disconnect();



}


sub doSendMessages {



    if($dbtype==0){ #mysql

      $dbh_child = DBI->connect("DBI:mysql:$db:$host:$port",$user,$pass);

      $sqlquery = "select id, from_unixtime(eventdate,'%d-%m-%y %k:%i:%s') ,message, useremail from png_mod_mailman_users where sentstate=0 limit 100";

    }

    
      $sth = $dbh_child->prepare($sqlquery);
      $sth->execute;
  
      while(@row=$sth->fetchrow_array) {
         #сформируем письмо
         @subj=split(";",$row[2]);
      
         #$subj[1] =~ s|<.+?>||g;

         $mail_to=$row[3];

         &send_mail($mail_to, $subj[0], $row[1].' '.$subj[1]);

         $sqlupdate = "update png_mod_mailman_users set sentstate=1 where id=$row[0]";

         doQueryToDatabase($sqlupdate);

      }
  
      $sth->finish();
      $dbh_child->disconnect();

}

sub send_mail {
   my $to = $_[0];
   my $subject = $_[1];
   my $body = $_[2];

   my $from = $mail_from;
   my $password = $mail_password;

   my $smtp;

   if (not $smtp = Net::SMTP::SSL->new($mailserver,
                              Port => $mailport,
                              Debug => 0)) {
      die "Could not connect to server\n";
   }

   $smtp->auth($from, $password)
      || die "Authentication failed!\n";

   $smtp->mail($from . "\n");
   my @recepients = split(/,/, $to);
   foreach my $recp (@recepients) {
      $smtp->to($recp . "\n");
   }
   $smtp->data();
   $smtp->datasend("From: " . $from . "\n");
   $smtp->datasend("To: " . $to . "\n");
   $smtp->datasend("Content-Type: text/html; charset=\"utf-8\" \n");
   $smtp->datasend("Subject: " . $subject . "\n");
   $smtp->datasend("\n");
   $smtp->datasend($body . "\n");
   $smtp->dataend();
   $smtp->quit;
}

# Send away!



doGetLastDate;
doGrabMessagesToAdmin;
doGrabMessages;
doSendMessages;