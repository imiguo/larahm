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
      curl_setopt($ch, CURLOPT_URL, 'https://www.e-gold.com/acct/confirm.asp');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'AccountID='.$frm['acc'].'&PassPhrase='.$frm['pass'].'&Payee_Account='.$frm['acc'].'&Amount=0.01&PAY_IN=1&WORTH_OF=Gold&Memo=Test+transaction&IGNORE_RATE_CHANGE=y');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $a = curl_exec($ch);
      echo '<hr>'.$a.'<hr>';
      curl_close($ch);
      $parts = [];
      if (preg_match('/<input type=hidden name=PAYMENT_BATCH_NUM VALUE="(\\d+)">/ims', $a, $parts)) {
          echo 'Test status: OK<br>Batch id = '.$parts[1];
      } else {
          if (preg_match('/<input type=hidden name=ERROR VALUE="(.*?)">/ims', $a, $parts)) {
              $txt = preg_replace('/&lt;/i', '<', $parts[1]);
              $txt = preg_replace('/&gt;/i', '>', $txt);
              echo 'Test status: Failed<br>'.$txt;
          } else {
              echo 'Test status: Failed<br>Unknown Error:<BR>'.$a;
          }
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
