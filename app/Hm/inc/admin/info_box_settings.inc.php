<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($settings['demomode'] == 1) {
    echo start_info_table('100%');
    echo '<b>Demo version restriction!</b><br>
You cannot change settings!';
    echo end_info_table();
}

  echo '
';
  echo '<s';
  echo 'cript language=javascript>
function checkb(i) {
  var d = \'\';
  if (i == 1)
  {
    if (document.menuf.show_info_box[0].checked) {
      d = \'block\';
    } else {
      d = \'none\'
    }
  }
  if (i == 2)
  {
    if (document.menuf.show_stats_box[0].checked) {
      d = \'block\';
    } else {
      d = \'none\'
    }
  }
  if (i == 3)
  {
    if (document.menuf.show_news_box[0].ch';
  echo 'ecked) {
      d = \'block\';
    } else {
      d = \'none\'
    }
  }
  document.getElementById("table_"+i).style.display = d;
}
</script>
<b>Info box settings:</b><br><br>
<form method=post name=menuf>
<input type=hidden name=a value=info_box>
<input type=hidden name=action value=info_box>

<table cellspacing=0 cellpadding=2 border=0 width=460>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="';
  echo 'bgColor=\'\';">
 <td width=360><b>Show info box:</b></td>
 <td width=100><input type=radio name=show_info_box value=1 ';
  echo $settings['show_info_box'] == 1 ? 'checked' : '';
  echo ' onClick="checkb(1)">Yes &nbsp; <input type=radio name=show_info_box value=0 ';
  echo $settings['show_info_box'] == 0 ? 'checked' : '';
  echo ' onClick="checkb(1)">No</td>
</tr><tr><td colspan=2>
<table cellspacing=0 cellpadding=2 border=0 width=100% id=table_1>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td width=350>Show started information:</td>
 <td width=146><input type=radio name=show_info_box_started value=1 ';
  echo $settings['show_info_box_started'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_started value=0 ';
  echo $settings['show_info_box_started'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              The started information: Started: Jan 1 2004 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show running days information:</td>
 <td><input type=radio name=show_info_box_running_days value=1 ';
  echo $settings['show_info_box_running_days'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_running_days value=0 ';
  echo $settings['show_info_box_running_days'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              The running days information: Running days: 1124 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show \'accounts total\' information:</td>
 <td><input type=radio name=show_info_box_total_accounts value=1 ';
  echo $settings['show_info_box_total_accounts'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_total_accounts value=0 ';
  echo $settings['show_info_box_total_accounts'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Accounts total quantity: Accounts total: 2842 
              ';
  echo end_info_table();
  echo '            </td>
          </tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show active accounts information:</td>
 <td><input type=radio name=show_info_box_active_accounts value=1 ';
  echo $settings['show_info_box_active_accounts'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_active_accounts value=0 ';
  echo $settings['show_info_box_active_accounts'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Active accounts quantity: Active accounts: 2042 (users who have 
              made a deposit) 
              ';
  echo end_info_table();
  echo '            </td>
          </tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show VIP accounts information:</td>
 <td><input type=radio name=show_info_box_vip_accounts value=1 ';
  echo $settings['show_info_box_vip_accounts'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_vip_accounts value=0 ';
  echo $settings['show_info_box_vip_accounts'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              VIP accounts quantity: Active accounts: 42<br>
              (users who have made a deposit more than 
              <input type="text" name="vip_users_deposit_amount" value="';
  echo $settings['vip_users_deposit_amount'];
  echo '" class=inpts>)';
  echo end_info_table();
  echo ' </td></tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show deposited funds information:</td>
 <td><input type=radio name=show_info_box_deposit_funds value=1 ';
  echo $settings['show_info_box_deposit_funds'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_deposit_funds value=0 ';
  echo $settings['show_info_box_deposit_funds'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Deposited funds total: Deposited total: $108,344.23 
              ';
  echo end_info_table();
  echo '            </td>
          </tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td>Show funds deposited today information:</td>
 <td><input type=radio name=show_info_box_today_deposit_funds value=1 ';
  echo $settings['show_info_box_today_deposit_funds'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_today_deposit_funds value=0 ';
  echo $settings['show_info_box_today_deposit_funds'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Funds deposited today: Deposited today: $1,444.00 
              ';
  echo end_info_table();
  echo '            </td>
          </tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td>Show total withdrawals information:</td>
 <td><input type=radio name=show_info_box_total_withdraw value=1 ';
  echo $settings['show_info_box_total_withdraw'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_total_withdraw value=0 ';
  echo $settings['show_info_box_total_withdraw'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Total withdrawals information: Total withdrawals: $45,387.30 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td>Show online visitors information:</td>
 <td><input type=radio name=show_info_box_visitor_online value=1 ';
  echo $settings['show_info_box_visitor_online'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_visitor_online value=0 ';
  echo $settings['show_info_box_visitor_online'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              How many visitors are there at the moment on the server: Visitors 
              online: 123 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td>Show online members information:</td>
 <td><input type=radio name=show_info_box_members_online value=1 ';
  echo $settings['show_info_box_members_online'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_members_online value=0 ';
  echo $settings['show_info_box_members_online'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              How many members are there on the server at the moment: Members 
              online: 34 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td>Show the newest member:</td>
 <td><input type=radio name=show_info_box_newest_member value=1 ';
  echo $settings['show_info_box_newest_member'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_newest_member value=0 ';
  echo $settings['show_info_box_newest_member'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              The newest member username: The Newest Member: <b>Uncle Sam.</b> 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show last update information (current day):</td>
 <td><input type=radio name=show_info_box_last_update value=1 ';
  echo $settings['show_info_box_last_update'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_info_box_last_update value=0 ';
  echo $settings['show_info_box_last_update'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Last update information - current day. 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
</table>
</td></tr>
<tr>
 <td colspan=2>&nbsp;</td>
</tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td width=360><b>Show Stats box:</b></td>
 <td width=100><input type=radio name=show_stats_box value=1 ';
  echo $settings['show_stats_box'] == 1 ? 'checked' : '';
  echo ' onClick="checkb(2)">Yes &nbsp; <input type=radio name=show_stats_box value=0 ';
  echo $settings['show_stats_box'] == 0 ? 'checked' : '';
  echo ' onClick="checkb(2)">No</td>
</tr><tr><td colspan=2>
<table cellspacing=0 cellpadding=2 border=0 width=100% id=table_2>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td width=350>Show Investors Page:</td>
 <td width=146><input type=radio name=show_members_stats value=1 ';
  echo $settings['show_members_stats'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_members_stats value=0 ';
  echo $settings['show_members_stats'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Show/do not show member statistics on your site. 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show the PaidOut Page:</td>
 <td><input type=radio name=show_paidout_stats value=1 ';
  echo $settings['show_paidout_stats'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_paidout_stats value=0 ';
  echo $settings['show_paidout_stats'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Show/do not show the latest withdrawals statistics.
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show Investors Top 10:</td>
 <td><input type=radio name=show_top10_stats value=1 ';
  echo $settings['show_top10_stats'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_top10_stats value=0 ';
  echo $settings['show_top10_stats'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Show/do not show top 10 investors information. 
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show Investors Last 10:</td>
 <td><input type=radio name=show_last10_stats value=1 ';
  echo $settings['show_last10_stats'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_last10_stats value=0 ';
  echo $settings['show_last10_stats'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Show/do not show last 10 investors information.
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show Top 20 Referrers:</td>
 <td><input type=radio name=show_refs10_stats value=1 ';
  echo $settings['show_refs10_stats'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_refs10_stats value=0 ';
  echo $settings['show_refs10_stats'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr>
 <td>Rest date (yyyy-mm-dd):</td>
 <td><input type=text name=refs10_start_date value="';
  echo $settings['refs10_start_date'];
  echo '" class=inpts></td>
</tr><tr>
            <td colspan=2> 
              ';
  echo start_info_table('100%');
  echo '              Show/do not show top 20 Referers information.<br>
              Reset Date is date from which system will start counts referrals for statistic.
              ';
  echo end_info_table();
  echo '            </td>
          </tr>
</table>
</td></tr>
<tr>
 <td colspan=2>&nbsp;</td>
</tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show kitco dollar per ounce box:</td>
 <td><input type=radio name=show_kitco_dollar_per_ounce_box value=1 ';
  echo $settings['show_kitco_dollar_per_ounce_box'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_kitco_dollar_per_ounce_box value=0 ';
  echo $settings['show_kitco_dollar_per_ounce_box'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr><tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>Show kitco euro per ounce box:</td>
 <td><input type=radio name=show_kitco_euro_per_ounce_box value=1 ';
  echo $settings['show_kitco_euro_per_ounce_box'] == 1 ? 'checked' : '';
  echo '>Yes &nbsp; <input type=radio name=show_kitco_euro_per_ounce_box value=0 ';
  echo $settings['show_kitco_euro_per_ounce_box'] == 0 ? 'checked' : '';
  echo '>No</td>
</tr>

<tr>
 <td colspan=2>&nbsp;</td>
</tr>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
      <td width=360><b>Show News box:</b></td>
 <td width=100><input type=radio name=show_news_box value=1 ';
  echo $settings['show_news_box'] == 1 ? 'checked' : '';
  echo ' onClick="checkb(3)">Yes &nbsp; <input type=radio name=show_news_box value=0 ';
  echo $settings['show_news_box'] == 0 ? 'checked' : '';
  echo ' onClick="checkb(3)">No</td>
</tr><tr><td colspan=2>
<table cellspacing=0 cellpadding=2 border=0 width=100% id=table_3>
<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
            <td width=350>News count on Index Page:</td>
 <td width=146><input type=text name=last_news_count value="';
  echo $settings['last_news_count'];
  echo '" size=3 class=inpts></td>
</tr>
</table>
</td></tr>


<tr>
 <td><br>&nbsp;</td>
 <td><input type=submit value="Change settings" class=sbmt></td>
</tr></table>
</form>';
  echo '<s';
  echo 'cript language=javascript>
checkb(1);
checkb(2);
checkb(3);
</script>';
