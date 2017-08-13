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
      $balance = '<atip.batch.v1><balancerequest.list>';
      $balance .= '<balancerequest>';
      $balance .= '<accountid>'.$settings['def_payee_account_ebullion'].'</accountid>';
      $balance .= '<metal>1</metal>';
      $balance .= '<unit>1</unit>';
      $balance .= '<ref>REQ123</ref>';
      $balance .= '</balancerequest>';
      $balance .= '</balancerequest.list></atip.batch.v1>';
      $infile = tempnam('', 'in.');
      $outfile = tempnam('', 'out.');
      $fd = fopen($infile, 'w');
      fwrite($fd, $balance);
      fclose($fd);
      $atippath = CACHE_PATH;
      $gpg_path = escapeshellcmd($settings['gpg_path']);
      $passphrase = decode_pass_for_mysql($settings['md5altphrase_ebullion']);
      $atip_status_url = $settings['site_url'];
      $gpg_options = ' --yes --no-tty --no-secmem-warning --no-options --no-default-keyring --batch --homedir '.$atippath.' --keyring=pubring.gpg --secret-keyring=secring.gpg --armor --throw-keyid --always-trust --passphrase-fd 0';
      $gpg_command = 'echo \''.$passphrase.'\' | '.$gpg_path.' '.$gpg_options.' --recipient A20077\\@e-bullion.com --local-user '.$settings['def_payee_account_ebullion'].(''.'\\@e-bullion.com --output '.$outfile.' --sign --encrypt '.$infile.' 2>&1');
      $buf = '';
      $fp = popen(''.$gpg_command, 'r');
      while (!feof($fp)) {
          $buf .= fgets($fp, 4096);
      }
      pclose($fp);
      if (0 < filesize($outfile)) {
          $fd = fopen($outfile, 'r');
          $atip_batch_msg = fread($fd, filesize($outfile));
          fclose($fd);
      } else {
          echo 'Error: GPG can not encrypt data:<br><pre>'.$buf.'</pre>';
      }
      unlink($infile);
      unlink($outfile);
      $qs = 'ATIP_ACCOUNT='.$settings['def_payee_account_ebullion'].'&ATIP_BATCH_MSG='.rawurlencode($atip_batch_msg).'&ATIP_STATUS_URL='.rawurlencode($atip_status_url);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://www2.e-bullion.com/atip/batch.php?'.$qs);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 1);
      $a = curl_exec($ch);
      curl_close($ch);
      $matches = [];
      $verification = '';
      if (preg_match('/Location: .*?\\?ATIP_VERIFICATION=([^\\r\\n]+)%0A/', $a, $matches)) {
          $verification = $matches[1];
      }
      $verification = urldecode($verification);
      $xmlfile = tempnam('', 'xml.cert.');
      $tmpfile = tempnam('', 'xml.tmp.');
      $fd = fopen(''.$tmpfile, 'w');
      fwrite($fd, $verification);
      fclose($fd);
      $gpg_options = ' --yes --no-tty --no-secmem-warning --no-options --no-default-keyring --batch --homedir '.$atippath.' --keyring=pubring.gpg --secret-keyring=secring.gpg --armor --passphrase-fd 0';
      $gpg_command = 'echo \''.$passphrase.'\' | '.$gpg_path.' '.$gpg_options.' --output '.$xmlfile.' --decrypt '.$tmpfile.' 2>&1';
      $buf = '';
      $keyID = '';
      $fp = popen(''.$gpg_command, 'r');
      while (!feof($fp)) {
          $buf = fgets($fp, 4096);
          $pos = strstr($buf, 'key ID');
          if (0 < strlen($pos)) {
              $keyID = preg_replace('/[\\n\\r]/', '', substr($pos, 7));
              continue;
          }
      }
      pclose($fp);
      if ($keyID == $settings['ebullion_keyID']) {
          if (is_file(''.$xmlfile)) {
              $fx = fopen(''.$xmlfile, 'r');
              $xmlcert = fread($fx, filesize(''.$xmlfile));
              fclose($fx);
          } else {
              echo 'Error: Can not find decripted file! It seems you have upload incorrect secting.gpg and puring.gpg';
          }
          $data = parsexml($xmlcert);
          if ($data['status'] == 'balance') {
              echo 'Test status: OK<br>Your balance is '.$data['amount'].' USD';
          } else {
              if ($data['status'] == 'error') {
                  echo 'Error: '.$data['text'].': '.$data['additional'];
              } else {
                  echo 'Unknown Error!';
              }
          }
      } else {
          $e = quote('Error: Can not decript verification response! It seems you have provide invalid Key ID');
      }
      unlink($tmpfile);
      unlink($xmlfile);
  } else {
      echo 'Sorry, but curl does not installed on your server';
  }

  echo '
</tr></table>
</tr></table>
</center>
</body>';
  exit();
