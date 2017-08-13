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
<title>HYIP Manager Pro. Auto-payment, mass payment included.</title>
<link href="images/adminstyle.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFF2" link="#666699" vlink="#666699" alink="#666699" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<center>
<table width="760" border="0" c';
  echo 'ellpadding="0" cellspacing="0" height=100%>
  <tr>
    <td valign=top height=142>
      <table cellspacing=0 cellpadding=0 border=0 width=100% height=142>
	    <tr>
		  <td background="images/ver.gif" bgcolor=#FF8D00><img src="images/top.gif" width=304 height=142 border="0" align=left></td>
		  <td background="images/ver.gif" bgcolor=#FF8D00 valign=bottom align=right>';
  if ((($settings['md5altphrase'] == '' and $settings['md5altphrase_evocash'] == '') and $settings['md5altphrase_intgold'] == '')) {
      echo start_info_table('100%');
      echo 'To receive deposits you should enter your \'Secret alternate password md5 hash\' on the settings screen!<br>
          You can receive the secret alternate password md5 hash here:<br>
<a href=https://www.e-gold.com/acct/md5check.html target=_blank>https://www.e-gold.com/acct/md5check.html</a><br>
          type your alternative password in the \'Alternate Passphrase\' field, then
          click \'cacl';
      echo 'ulate hash now\' and copy the \'Passphrase Hash\' to the settings
          screen.<br>
          It is made to prevent fake deposits.
          ';
      echo end_info_table();
      echo '          ';
  }

  echo '          ';
  echo '<s';
  echo 'pan style="font-family: verdana; font-size: 12px; color: white"> <b>';
  /* version check by goldcoders */
 /*
  if (rand (1, 5) == 3)
  {
    echo '<img src="http://www.goldcoders.com/check.cgi?i=1&license=1&domain=';
    echo $frm_env['HTTP_HOST'];
    echo '&n=';
    echo $frm_env['SCRIPT_NAME'];
    echo '" width=1 height=1> ';
  }
  */
  echo '          <a href=/?a=logout class=toplink>Home</a> &middot; <a href=/?a=faq class=toplink>FAQ</a>
          &middot; <a href=/?a=rules class=toplink>Rules</a> &middot;
          <a href=/?a=logout class=toplink>Logout</a> &middot;
          <a href=/?a=support class=toplink>Support</a></b></span>&nbsp; &nbsp;</td>
 	    </tr>
	  </table>
     </td>
  </tr>';
