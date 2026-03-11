<?php



$colhtext[1]="#";
$colhtext[2]=$_lang['stDATE'];
$colhtext[3]=$_lang['stHOST'];
$colhtext[4]=$_lang['stMESSAGE'];


$colftext[1]="&nbsp;";
$colftext[2]="&nbsp;";
$colftext[3]="&nbsp;";
$colftext[4]="&nbsp;";


$colh[0]=4;
$colh[1]="<th class=unsortable>".$colhtext[1]."</th>";
$colh[2]="<th>".$colhtext[2]."<a href=?></a></th>";
$colh[3]="<th>".$colhtext[3]."</th>";
$colh[4]="<th>".$colhtext[4]."</th>";


$colr[0]=1; ///report type 1 - prostoi, 2 - po vremeni, 3 - wide
$colr[1]="<td><a href=".$globalSS['root_http']."right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=3&hostid=line3>&nbsp;&nbsp;numrow&nbsp;&nbsp;</a></td>";
$colr[2]="<td>line0</td>";
$colr[3]="<td>line1</td>";
$colr[4]="<td>line2</td>";




$colf[1]="<td>".$colftext[1]."</td>";
$colf[2]="<td><b>".$colftext[2]."</b></td>";
$colf[3]="<td><b>".$colftext[3]."</b></td>";
$colf[4]="<td>".$colftext[4]."</td>";


?>