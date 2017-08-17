<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

  $q = 'select count(*) as `call` from hm2_news';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $count_all = $row['call'];
  $page = app('data')->frm['page'];
  $onpage = 20;
  $colpages = ceil($count_all / $onpage);
  if ($page <= 1) {
      $page = 1;
  }

  if (($colpages < $page and 1 < $colpages)) {
      $page = $colpages;
  }

  $from = ($page - 1) * $onpage;
  $q = 'select
                 *,
                 date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as d
           from
                 hm2_news
           order by
                 date desc
           limit
                 '.$from.', '.$onpage);
  $sth = db_query($q);
  $news = [];
  while ($row = mysql_fetch_array($sth)) {
      if ($row['full_text'] == '') {
          $row['full_text'] = $row['small_text'];
      }

      $row['full_text'] = preg_replace('/
/', '<br>', $row['full_text']);
      array_push($news, $row);
  }

  view_assign('news', $news);
  $pages = [];
  for ($i = 1; $i <= $colpages; ++$i) {
      $apage = [];
      $apage['page'] = $i;
      $apage['current'] = ($i == $page ? 1 : 0);
      array_push($pages, $apage);
  }

  view_assign('pages', $pages);
  view_assign('colpages', $colpages);
  view_assign('current_page', $page);
  if (1 < $page) {
      view_assign('prev_page', $page - 1);
  }

  if ($page < $colpages) {
      view_assign('next_page', $page + 1);
  }

  view_execute('news.tpl');
