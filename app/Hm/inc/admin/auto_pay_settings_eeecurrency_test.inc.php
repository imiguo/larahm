<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>HYIP Manager Pro</title>
<link href="images/adminstyle.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFF2" link="#666699" vlink="#666699" alink="#666699" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<center>
<br><br><br>
	 <table cellspacing=0 cellpadding=1 border=0 width=80% he';
  echo 'ight=100% bgcolor=#ff8d00>
	   <tr>
	     <td>
           <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
             <tr bgcolor="#FFFFFF" valign="top"> 
<td bgcolor=#FFFFFF>';
  if (function_exists('curl_init')) {
      $ch = curl_init();
      echo curl_error($ch);
      curl_setopt($ch, CURLOPT_URL, 'https://eeecurrency.com/cgi-bin/autopay.cgi');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'ACCOUNTID='.$frm['acc'].'&PASSWORD='.$frm['pass'].'&SECPASSWORD='.$frm['code'].'&RECEIVER='.$frm['acc'].'&AMOUNT=0.01&TEST=Y');
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $a = curl_exec($ch);
      curl_close($ch);
      $parts = [];
      if (preg_match(''.'/TEST\\sTRANSACTION_ID:(.*?)$/ims', $a, $parts)) {
          echo 'Test status: OK<br>Batch id = '.$parts[1];
      } else {
          echo 'Test status: Failed<br>'.$a;
      }
  } else {
      echo 'Sorry, but curl does not installed on your server';
  }

  echo '
</tr></table>
</tr></table>
</center>
</body>';
  exit();
