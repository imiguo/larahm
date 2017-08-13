<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<b>Custom pages:</b><br><br>



You can add any custom html to our script, for example "Rate Us" html where will be stored links to rating sites.<br><br>
To create custom page you should follow the next steps:<br>
<li>copy \'example.tpl\' file to [your_document_name].tpl (for example \'rate_us.tpl\')</li>
<li>Change content of the page with your favorite html editor</li>
<li>Upload this file to your server into \'';
  echo 'tmpl/custom\' directory</li>
<li>Check result - ';
  echo $settings['site_url'];
  echo '/?a=cust&page=[your_document_name] <br>Example: <a href=';
  echo $settings['site_url'];
  echo '/?a=cust&page=rate_us target=_blank>';
  echo $settings['site_url'];
  echo '/?a=cust&page=rate_us</a></li>
<li>Add this link to the top menu (edit \'tmpl/logo.tpl\' file) or to the left menu 
  (edit \'tmpl/left.tpl\' file)</li>

';
