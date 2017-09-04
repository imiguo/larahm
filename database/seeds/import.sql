TRUNCATE TABLE `processings`;
INSERT INTO `processings` (`id`, `name`, `infofields`, `status`, `description`)
VALUES
  (1, 'PerfectMoney', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>Your PerfectMoney account</b>'),
  (2, 'Payeer', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>Your Payeer account</b>'),
  (3, 'BitCoin', 'a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}', 0,
   'Send your funds to account: <b>your BitCoin account</b>');

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

INSERT INTO `plans` (`name`, `description`, `min_deposit`, `max_deposit`, `percent`, `status`, `parent`, `created_at`, `updated_at`)
VALUES
    ('2% weekly forever', NULL, 10.00, 500.00, 2.00, NULL, 7, NULL, NULL),
    ('120% After 5 Day', NULL, 300.00, 500.00, 120.00, NULL, 2, NULL, NULL),
    ('135% After 5 Day', NULL, 501.00, 1000.00, 135.00, NULL, 2, NULL, NULL),
    ('150% After 5 Day', NULL, 1001.00, 2000.00, 150.00, NULL, 2, NULL, NULL),
    ('180% After 5 Day', NULL, 2001.00, 5000.00, 180.00, NULL, 2, NULL, NULL),
    ('260% After 5 Day', NULL, 5001.00, 20000.00, 260.00, NULL, 2, NULL, NULL),
    ('200% After 10 Days', NULL, 300.00, 500.00, 200.00, NULL, 3, NULL, NULL),
    ('250% After 10 Days', NULL, 501.00, 1000.00, 250.00, NULL, 3, NULL, NULL),
    ('300% After 10 Days', NULL, 1001.00, 5000.00, 300.00, NULL, 3, NULL, NULL),
    ('400% After 10 Days', NULL, 5001.00, 20000.00, 400.00, NULL, 3, NULL, NULL),
    ('430% After 10 Days', NULL, 20001.00, 50000.00, 430.00, NULL, 3, NULL, NULL),
    ('103% After1 Day', NULL, 10.00, 100.00, 103.00, NULL, 1, NULL, NULL),
    ('106% After1 Day', NULL, 101.00, 500.00, 106.00, NULL, 1, NULL, NULL),
    ('120% After1 Day', NULL, 501.00, 2000.00, 120.00, NULL, 1, NULL, NULL),
    ('126% After1 Day', NULL, 2001.00, 0.00, 126.00, NULL, 1, NULL, NULL),
    ('300% After 15 Day', NULL, 300.00, 1000.00, 300.00, NULL, 4, NULL, NULL),
    ('420% After 15 Day', NULL, 1001.00, 2000.00, 420.00, NULL, 4, NULL, NULL),
    ('480% After 15 Day', NULL, 2001.00, 5000.00, 480.00, NULL, 4, NULL, NULL),
    ('540% After 15 Day', NULL, 5001.00, 20000.00, 540.00, NULL, 4, NULL, NULL),
    ('600% After 15 Day', NULL, 20001.00, 50000.00, 600.00, NULL, 4, NULL, NULL),
    ('550% After 20 Day', NULL, 250.00, 1000.00, 550.00, NULL, 5, NULL, NULL),
    ('680% After 20 Day', NULL, 1001.00, 5000.00, 680.00, NULL, 5, NULL, NULL),
    ('800% After 20 Day', NULL, 5001.00, 10000.00, 800.00, NULL, 5, NULL, NULL),
    ('880% After 20 Day', NULL, 10001.00, 20000.00, 880.00, NULL, 5, NULL, NULL),
    ('1100% After 20 Day', NULL, 20001.00, 50000.00, 1100.00, NULL, 5, NULL, NULL),
    ('350% After 25 Day', NULL, 200.00, 500.00, 350.00, NULL, 6, NULL, NULL),
    ('450% After 25 Day', NULL, 501.00, 1000.00, 600.00, NULL, 6, NULL, NULL),
    ('850% After 25 Day', NULL, 1001.00, 2000.00, 850.00, NULL, 6, NULL, NULL),
    ('1200% After 25 Day', NULL, 2001.00, 5000.00, 1200.00, NULL, 6, NULL, NULL),
    ('1200% After 25 Day', NULL, 5001.00, 10000.00, 1450.00, NULL, 6, NULL, NULL),
    ('1650% After 25 Day', NULL, 10001.00, 50000.00, 1650.00, NULL, 6, NULL, NULL);

