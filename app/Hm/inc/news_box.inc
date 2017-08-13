<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ((($settings['show_news_box'] and ($frm['a'] == '' or $frm['a'] == 'home')) or $settings['show_news_always'])) {
    ($max_news = $settings['last_news_count'] or 5);
    $q = 'select
                 *,
                 date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as d
           from
                 hm2_news
           order by
                 date desc
           limit
                 0, '.$max_news);
    $sth = db_query($q);
    $news = [];
    while ($row = mysql_fetch_array($sth)) {
        if ($row['small_text'] == '') {
            $row['full_text'] = strip_tags($row['full_text']);
            $row['small_text'] = preg_replace('/^(.{100,120})\\s.*/', '$1...', $row['full_text']);
        }

        $row['small_text'] = preg_replace('/
/', '<br>', $row['small_text']);
        array_push($news, $row);
    }

    if (sizeof($news) == 0) {
        $settings['show_news_box'] = 0;
        $smarty->assign('settings', $settings);
    } else {
        $smarty->assign('news', $news);
    }
} else {
    $settings['show_news_box'] = 0;
    $smarty->assign('settings', $settings);
}
