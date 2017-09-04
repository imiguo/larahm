TRUNCATE TABLE `processings`;
INSERT INTO `processings` (`id`, `name`, `infofields`, `status`, `description`)
VALUES
  (1, 'Bank Wire', 'a:3:{i:1;s:9:\"Bank Name\";i:2;s:12:\"Account Name\";i:3;s:15:\"Payment Details\";}', 0,
   'Send your bank wires here:<br>\r\nBeneficiary\'s Bank Name: <b>Your Bank Name</b><br>\r\nBeneficiary\'s Bank SWIFT code: <b>Your Bank SWIFT code</b><br>\r\nBeneficiary\'s Bank Address: <b>Your Bank address</b><br>\r\nBeneficiary Account: <b>Your Account</b><br>\r\nBeneficiary Name: <b>Your Name</b><br>\r\n\r\nCorrespondent Bank Name: <b>Your Bank Name</b><br>\r\nCorrespondent Bank Address: <b>Your Bank Address</b><br>\r\nCorrespondent Bank codes: <b>Your Bank codes</b><br>\r\nABA: <b>Your ABA</b><br>'),
  (2, 'e-Bullion', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Please send your payments to this account: <b>Your e-Bullion account</b>'),
  (3, 'NetPay', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>Your NetPay account</b>'),
  (4, 'GoldMoney', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your fund to account: <b>your GoldMoney account</b>'),
  (5, 'MoneyBookers', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>your MoneyBookers account</b>'),
  (6, 'Pecunix', 'a:2:{i:1;s:19:\"Your e-mail address\";i:2;s:16:\"Reference Number\";}', 0,
   'Send your funds to account: <b>your Pecunix account</b>'),
  (7, 'PicPay', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>Your PicPay account</b>');

TRUNCATE TABLE `referal`;
INSERT INTO `referal` (`id`, `level`, `name`, `from_value`, `to_value`, `percent`, `percent_daily`, `percent_weekly`, `percent_monthly`)
VALUES
  (1, 1, 'Level A', 1, 2, 2.00, NULL, NULL, NULL),
  (2, 1, 'Level B', 3, 5, 3.00, NULL, NULL, NULL),
  (3, 1, 'Level C', 6, 10, 5.00, NULL, NULL, NULL),
  (4, 1, 'Level D', 11, 20, 7.50, NULL, NULL, NULL),
  (5, 1, 'Level E', 21, 0, 10.00, NULL, NULL, NULL);

TRUNCATE TABLE `types`;
INSERT INTO `types` (`id`, `name`, `description`, `q_days`, `min_deposit`, `max_deposit`, `period`, `status`, `return_profit`, `return_profit_percent`, `percent`, `pay_to_egold_directly`, `use_compound`, `work_week`, `parent`, `withdraw_principal`, `withdraw_principal_percent`, `withdraw_principal_duration`, `compound_min_deposit`, `compound_max_deposit`, `compound_percents_type`, `compound_min_percent`, `compound_max_percent`, `compound_percents`, `closed`, `withdraw_principal_duration_max`, `dsc`, `hold`, `delay`, `group`, `created_at`, `updated_at`)
VALUES
    (1,'120% After 1 Day',NULL,1,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,2,NULL,NULL),
    (2,'260% After 5 Day',NULL,5,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,0,NULL,NULL),
    (3,'430% After 10 Days',NULL,10,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,0,NULL,NULL),
    (4,'600% After 15 Day',NULL,15,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,0,NULL,NULL),
    (5,'1100% After 20 Day',NULL,20,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,0,NULL,NULL),
    (6,'1650% After 25 Day',NULL,25,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,0,NULL,NULL),
    (7,'2% weekly forever',NULL,365,NULL,NULL,'w','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,1,NULL,NULL);

TRUNCATE TABLE `plans`;
INSERT INTO `plans` (`id`, `name`, `description`, `min_deposit`, `max_deposit`, `percent`, `status`, `parent`, `created_at`, `updated_at`)
VALUES
    (1,'120% After1 Day',NULL,1.00,50000.00,126.00,NULL,1,NULL,NULL),
    (2,'260% After 5 Day',NULL,500.00,50000.00,260.00,NULL,2,NULL,NULL),
    (3,'430% After 10 Days',NULL,300.00,50000.00,300.00,NULL,3,NULL,NULL),
    (4,'600% After 15 Day',NULL,300.00,50000.00,600.00,NULL,4,NULL,NULL),
    (5,'1100% After 20 Day',NULL,250.00,50000.00,1100.00,NULL,5,NULL,NULL),
    (6,'1650% After 25 Day',NULL,200.00,50000.00,1650.00,NULL,6,NULL,NULL),
    (7,'2% weekly forever',NULL,10.00,500.00,2.00,NULL,7,NULL,NULL);
