<?php



$colhtext[1]="#";
$colhtext[2]=$_lang['stHOST'];
$colhtext[3]=$_lang['stSTATUS'];
$colhtext[4]=$_lang['stLASTPOLLED'];
$colhtext[5]=$_lang['stDURATION'];


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


$colr[0]=1; 
$colr[1]="<td>&nbsp;&nbsp;numrow&nbsp;&nbsp;</td>";
$colr[2]="<td class=tooltip><a href=../right.php?srv=".$globalSS['connectionParams']['srv']."&id=1&actid=3&hostid=line4>line0</a> <span class='tooltiptext'>hostinfo</span></td>";
$colr[3]="<td>getstatusname_line_1</td>";
$colr[4]="<td>line2</td>";
$colr[5]="<td>getuptime_line_3</td>";



$colf[1]="<td>".$colftext[1]."</td>";
$colf[2]="<td><b>".$colftext[2]."</b></td>";
$colf[3]="<td><b>".$colftext[3]."</b></td>";
$colf[4]="<td>".$colftext[4]."</td>";
$colf[5]="<td>".$colftext[5]."</td>";


?>