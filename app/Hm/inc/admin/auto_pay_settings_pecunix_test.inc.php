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
      curl_setopt($ch, CURLOPT_URL, 'http://pxi.pecunix.com/');
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $a = curl_exec($ch);
      curl_close($ch);
      preg_match('/Date: \\w+, \\d+ \\w+ \\d+ (\\d+)/', $a, $m);
      $hour = $m[1];
      $token = strtoupper(md5($frm['pass'].':'.gmdate('Ymd').(''.':'.$hour)));
      $data = '
  <TransferRequest>
    <Transfer>
      <TransferId> </TransferId>
      <Payer> '.$frm['acc'].' </Payer>
      <Payee> '.$frm['acc'].' </Payee>
      <CurrencyId> GAU </CurrencyId>
      <Equivalent>
        <CurrencyId> USD </CurrencyId>
        <Amount> 0.01 </Amount>
      </Equivalent>
      <FeePaidBy> Payee </FeePaidBy>
      <Memo> HYIP Manager Pro Test </Memo>
    </Transfer>
    <Auth>
      <Token> '.$token.' </Token>
    </Auth>
  </TransferRequest>
  ';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://pxi.pecunix.com/money.refined...transfer');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $a = curl_exec($ch);
      curl_close($ch);
      $out = parsexml_pecunix($a);
      if ($out['status'] == 'ok') {
          echo 'Test status: OK<br>Batch id = '.$out['batch'];
      } else {
          if ($out['status'] == 'error') {
              echo 'Test Status: Error<br>'.$out['text'].'<br>'.$out['additional'];
          } else {
              echo 'Test Status: Error<br>Parse error: '.$a;
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
