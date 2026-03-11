<?php



$colhtext[1]="#";
$colhtext[2]=$_lang['stDATE'];
$colhtext[3]=$_lang['stHOST'];
$colhtext[4]=$_lang['stHOSTNAME'];
$colhtext[5]=$_lang['stADDTODB'];


$colftext[1]="&nbsp;";
$colftext[2]="&nbsp;";
$colftext[3]="&nbsp;";
$colftext[4]="&nbsp;";
$colftext[5]="&nbsp;";
 

$colh[0]=5;
$colh[1]="<th class=unsortable>".$colhtext[1]."</th>";
$colh[2]="<th>".$colhtext[2]."<a href=?></a></th>";
$colh[3]="<th>".$colhtext[3]."</th>";
$colh[4]="<th>".$colhtext[4]."</th>";
$colh[5]="<th>".$colhtext[5]."</th>";


$colr[0]=1; ///report type 1 - prostoi, 2 - po vremeni, 3 - wide
$colr[1]="<td>numrow</td>";
$colr[2]="<td>line0</td>";
$colr[3]="<td>line1</td>";
$colr[4]="<td>line2</td>";
$colr[5]="<td><input id=\"ButtonAdd\" type=\"button\" value=\"+\" onclick=\"showModalPopUp(".$globalSS['connectionParams']['srv'].",'line1','line2')\" /></td>";




$colf[1]="<td>".$colftext[1]."</td>";
$colf[2]="<td><b>".$colftext[2]."</b></td>";
$colf[3]="<td><b>".$colftext[3]."</b></td>";
$colf[4]="<td>".$colftext[4]."</td>";
$colf[5]="<td>".$colftext[5]."</td>";


?>