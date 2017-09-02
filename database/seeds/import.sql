TRUNCATE TABLE `emails`;
INSERT INTO `emails` (`id`, `name`, `subject`, `text`, `status`)
VALUES
  ('account_update_confirmation', 'Account Update Confirmation', 'Account Update Confirmation',
   'Dear #name# (#username#),\n\nSomeone from IP address #ip# (most likely you) is trying to change your account data.\n\nTo confirm these changes please use this Confirmation Code:\n#confirmation_code#\n\nThank you.\n#site_name#\n#site_url#',
   1),
  ('bonus', 'Bonus Notification', 'Bonus Notification', 'Hello #name#,\r\n\r\nYou received a bonus: $#amount#\r\nYou can check your statistics here:\r\n#site_url#\r\n\r\nGood luck.', 1),
  ('brute_force_activation', 'Account Activation after Brute Force', '#site_name# - Your account activation code.', 'Someone from IP #ip# has entered a password for your account \"#username#\" incorrectly #max_tries# times. System locked your accout until you activate it.\r\n\r\nClick here to activate your account :\r\n\r\n#site_url#?a=activate&code=#activation_code#\r\n\r\nThank you.\r\n#site_name#', 1),
  ('change_account', 'Account Change Notification', 'Account Change Notification', 'Hello #name#,\r\n\r\nYour account data has been changed from ip #ip#\r\n\r\n\r\nNew information:\r\n\r\nPassword: #password#\r\nE-gold account: #egold#\r\nE-mail address: #email#\r\n\r\nContact us immediately if you did not authorize this change.\r\n\r\nThank you.', 1),
  ('confirm_registration', 'Registration Confirmation', 'Confirm your registration', 'Hello #name#,\r\n\r\nThank you for registering in our program\r\nPlease confirm your registration or ignore this message.\r\n\r\nCopy and paste this link to your browser:\r\n#site_url#/?a=confirm_registration&c=#confirm_string#\r\n\r\nThank you.\r\n#site_name#', 1),
  ('deposit_admin_notification', 'Administrator Deposit Notification', 'A deposit has been processed', 'User #username# deposit $#amount# #currency# to #plan#.\r\n\r\nAccount: #account#\r\nBatch: #batch#\r\nCompound: #compound#%.\r\nReferrers fee: $#ref_sum#', 1),
  ('deposit_approved_admin_notification', 'Deposit Approved Admin Notification', 'Deposit has been approved', 'Deposit has been approved:\n\nUser: #username# (#name#)\nAmount: $#amount# of #currency#\nPlan: #plan#\nDate: #deposit_date#\n#fields#', 1),
  ('deposit_approved_user_notification', 'Deposit Approved User Notification', 'Deposit has been approved', 'Dear #name#\n\nYour deposit has been approved:\n\nAmount: $#amount# of #currency#\nPlan: #plan#\n#fields#', 1),
  ('deposit_user_notification', 'Deposit User Notification', 'Payment received', 'Dear #name# (#username#)\r\n\r\nWe have successfully recived your deposit $#amount# #currency# to #plan#.\r\n\r\nYour Account: #account#\r\nBatch: #batch#\r\nCompound: #compound#%.\r\n\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#', 1),
  ('direct_signup_notification', 'Direct Referral Signup', 'You have a new direct signup on #site_name#', 'Dear #name# (#username#)\n\nYou have a new direct signup on #site_name#\nUser: #ref_username#\nName: #ref_name#\nE-mail: #ref_email#\n\nThank you.', 1),
  ('exchange_admin_notification', 'Exchange Admin Notification', 'Currency Exchange Processed', 'User #username# has exchanged $#amount_from# #currency_from# to $#amount_to# #currency_to#.', 0),
  ('exchange_user_notification', 'Exchange User Notification', 'Currency Exchange Completed', 'Dear #name# (#username#).\r\n\r\nYou have successfully exchanged $#amount_from# #currency_from# to $#amount_to# #currency_to#.\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#', 0),
  ('forgot_password', 'Password Reminder', 'The password you requested', 'Hello #name#,\r\n\r\nSomeone (most likely you) requested your username and password from the IP #ip#.\r\nYour password has been changed!!!\r\n\r\nYou can log into our account with:\r\n\r\nUsername: #username#\r\nPassword: #password#\r\n\r\nHope that helps.', 1),
  ('penalty', 'Penalty Notification', 'Penalty Notification', 'Hello #name#,\r\n\r\nYour account has been charged for $#amount#\r\nYou can check your statistics here:\r\n#site_url#\r\n\r\nGood luck.', 1),
  ('pending_deposit_admin_notification', 'Deposit Request Admin Notification', 'Deposit Request Notification', 'User #username# save deposit $#amount# of #currency# to #plan#.\n\n#fields#', 1),
  ('referral_commision_notification', 'Referral Comission Notification', '#site_name# Referral Comission', 'Dear #name# (#username#)\n\nYou have recived a referral comission of $#amount# #currency# from the #ref_name# (#ref_username#) deposit.\n\nThank you.', 1),
  ('registration', 'Registration Completetion', 'Registration Info', 'Hello #name#,\r\n\r\nThank you for registration on our site.\r\n\r\nYour login information:\r\n\r\nLogin: #username#\r\nPassword: #password#\r\n\r\nYou can login here: #site_url#\r\n\r\nContact us immediately if you did not authorize this registration.\r\n\r\nThank you.', 1),
  ('withdraw_admin_notification', 'Administrator Withdrawal Notification', 'Withdrawal has been sent', 'User #username# received $#amount# to #currency# account #account#. Batch is #batch#.', 1),
  ('withdraw_request_admin_notification', 'Administrator Withdrawal Request Notification', 'Withdrawal Request has been sent', 'User #username# requested to withdraw $#amount# from IP #ip#.', 1),
  ('withdraw_request_user_notification', 'User Withdrawal Request Notification', 'Withdrawal Request has been sent', 'Hello #name#,\r\n\r\n\r\nYou has requested to withdraw $#amount#.\r\nRequest IP address is #ip#.\r\n\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#', 1),
  ('withdraw_user_notification', 'User Withdrawal Notification', 'Withdrawal has been sent', 'Hello #name#.\r\n\r\n$#amount# has been successfully sent to your #currency# account #account#.\r\nTransaction batch is #batch#.\r\n\r\n#site_name#\r\n#site_url#', 1);

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
    (1,'120% After 1 Day',NULL,1,NULL,NULL,'d','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0,2,NULL,NULL),
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
