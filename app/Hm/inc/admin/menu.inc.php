<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '                   <table cellspacing=0 cellpadding=2 border=0 width="172">
                    <tr>
                      <th colspan=2><img src=images/q.gif width=1 height=3></th>
                    </tr>
                    <tr>
                      <th colspan=2 class=title>Menu</th>
                    </tr>
<tr>
<td class=menutxt><a href=?a=rates>Investment Packages</a></td>
</tr><tr>
<td class=me';
  echo 'nutxt><a href=?a=members>Members</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
<td class=menutxt><a href=?a=thistory&ttype=deposit>Deposits History</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=thistory&ttype=withdraw_pending>Withdrawal Requests</a></td>
</tr><tr>
<td class=menutxt><a href=?a=thistory&ttype=earning>Earning History</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=thistory&ttype=';
  echo 'withdrawal>Withdrawals History</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
<td class=menutxt><a href=?a=thistory>Transactions History</a></td>
</tr><tr>
<td class=menutxt><a href=?a=thistory&ttype=bonus>Bonuses</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=thistory&ttype=penality>Penalties</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
    <td class=menutxt><a href=?a=processings>Othe';
  echo 'r Processings</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=pending_deposits>Pending Deposits</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>

<td class=menutxt><a href=?a=exchange_rates>Exchange Rates</a></td>
</tr><tr>
<td class=menutxt><a href=?a=thistory&ttype=exchange>Exchange History</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>

    <td class=menutxt><a href=?a=send_bonuce>Send ';
  echo 'a Bonus</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=send_penality>Send a Penalty</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>

    <td class=menutxt><a href=?a=newsletter>Send a Newsletter</a></td>
</tr><tr>
    <td class=menutxt><a href=?a=edit_emails>Edit E-mail Templates</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
';
  if ($settings['demomode'] != '1') {
      echo '    <td class=menutxt><a href=?a=custompages>Custom Pages</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>';
  }

  echo '

<td class=menutxt><a href=?a=settings>Settings</a></td>
</tr><tr>
<!--<td class=menutxt>&nbsp;</td>
</tr><tr>
<td class=menutxt><a href=?a=startup_bonus>Additional settings</a></td>
</tr><tr>-->
<td class=menutxt>&nbsp;</td>
</tr><tr>
    <td class=menutxt><a href=?a=referal>Referral Settings</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
    <td class=menutxt><a href=?a=auto-pay-settings>Auto-Withdraw';
  echo 'als Settings</a> 
      ';
  if ($settings['demomode'] == 1) {
      echo '      <br>
&nbsp; &nbsp; ';
      echo '<s';
      echo 'pan style="color: #D20202;">Pro version only</span>';
  }

  echo '
</td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
    <td class=menutxt><a href=?a=info_box>InfoBoxes Settings</a></td>
</tr><tr>
<td class=menutxt><a href=?a=news>News Management</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>
<td class=menutxt><a href=?a=security>Security</a></td>
</tr><tr>
<td class=menutxt>&nbsp;</td>
</tr><tr>

<td class=menutxt><a href=?a=logout>Logout</a></td>

	
          ';
  echo '          </tr>
				   </table>';
