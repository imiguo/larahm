TRUNCATE TABLE `emails`;
INSERT INTO `emails` (`id`, `name`, `subject`, `text`, `status`)
VALUES
	('account_update_confirmation','Account Update Confirmation','Account Update Confirmation','Dear #name# (#username#),\n\nSomeone from IP address #ip# (most likely you) is trying to change your account data.\n\nTo confirm these changes please use this Confirmation Code:\n#confirmation_code#\n\nThank you.\n#site_name#\n#site_url#',1),
	('bonus','Bonus Notification','Bonus Notification','Hello #name#,\r\n\r\nYou received a bonus: $#amount#\r\nYou can check your statistics here:\r\n#site_url#\r\n\r\nGood luck.',1),
	('brute_force_activation','Account Activation after Brute Force','#site_name# - Your account activation code.','Someone from IP #ip# has entered a password for your account \"#username#\" incorrectly #max_tries# times. System locked your accout until you activate it.\r\n\r\nClick here to activate your account :\r\n\r\n#site_url#?a=activate&code=#activation_code#\r\n\r\nThank you.\r\n#site_name#',1),
	('change_account','Account Change Notification','Account Change Notification','Hello #name#,\r\n\r\nYour account data has been changed from ip #ip#\r\n\r\n\r\nNew information:\r\n\r\nPassword: #password#\r\nE-gold account: #egold#\r\nE-mail address: #email#\r\n\r\nContact us immediately if you did not authorize this change.\r\n\r\nThank you.',1),
	('confirm_registration','Registration Confirmation','Confirm your registration','Hello #name#,\r\n\r\nThank you for registering in our program\r\nPlease confirm your registration or ignore this message.\r\n\r\nCopy and paste this link to your browser:\r\n#site_url#/?a=confirm_registration&c=#confirm_string#\r\n\r\nThank you.\r\n#site_name#',1),
	('deposit_admin_notification','Administrator Deposit Notification','A deposit has been processed','User #username# deposit $#amount# #currency# to #plan#.\r\n\r\nAccount: #account#\r\nBatch: #batch#\r\nCompound: #compound#%.\r\nReferrers fee: $#ref_sum#',1),
	('deposit_approved_admin_notification','Deposit Approved Admin Notification','Deposit has been approved','Deposit has been approved:\n\nUser: #username# (#name#)\nAmount: $#amount# of #currency#\nPlan: #plan#\nDate: #deposit_date#\n#fields#',1),
	('deposit_approved_user_notification','Deposit Approved User Notification','Deposit has been approved','Dear #name#\n\nYour deposit has been approved:\n\nAmount: $#amount# of #currency#\nPlan: #plan#\n#fields#',1),
	('deposit_user_notification','Deposit User Notification','Payment received','Dear #name# (#username#)\r\n\r\nWe have successfully recived your deposit $#amount# #currency# to #plan#.\r\n\r\nYour Account: #account#\r\nBatch: #batch#\r\nCompound: #compound#%.\r\n\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#',1),
	('direct_signup_notification','Direct Referral Signup','You have a new direct signup on #site_name#','Dear #name# (#username#)\n\nYou have a new direct signup on #site_name#\nUser: #ref_username#\nName: #ref_name#\nE-mail: #ref_email#\n\nThank you.',1),
	('exchange_admin_notification','Exchange Admin Notification','Currency Exchange Processed','User #username# has exchanged $#amount_from# #currency_from# to $#amount_to# #currency_to#.',0),
	('exchange_user_notification','Exchange User Notification','Currency Exchange Completed','Dear #name# (#username#).\r\n\r\nYou have successfully exchanged $#amount_from# #currency_from# to $#amount_to# #currency_to#.\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#',0),
	('forgot_password','Password Reminder','The password you requested','Hello #name#,\r\n\r\nSomeone (most likely you) requested your username and password from the IP #ip#.\r\nYour password has been changed!!!\r\n\r\nYou can log into our account with:\r\n\r\nUsername: #username#\r\nPassword: #password#\r\n\r\nHope that helps.',1),
	('penalty','Penalty Notification','Penalty Notification','Hello #name#,\r\n\r\nYour account has been charged for $#amount#\r\nYou can check your statistics here:\r\n#site_url#\r\n\r\nGood luck.',1),
	('pending_deposit_admin_notification','Deposit Request Admin Notification','Deposit Request Notification','User #username# save deposit $#amount# of #currency# to #plan#.\n\n#fields#',1),
	('referral_commision_notification','Referral Comission Notification','#site_name# Referral Comission','Dear #name# (#username#)\n\nYou have recived a referral comission of $#amount# #currency# from the #ref_name# (#ref_username#) deposit.\n\nThank you.',1),
	('registration','Registration Completetion','Registration Info','Hello #name#,\r\n\r\nThank you for registration on our site.\r\n\r\nYour login information:\r\n\r\nLogin: #username#\r\nPassword: #password#\r\n\r\nYou can login here: #site_url#\r\n\r\nContact us immediately if you did not authorize this registration.\r\n\r\nThank you.',1),
	('withdraw_admin_notification','Administrator Withdrawal Notification','Withdrawal has been sent','User #username# received $#amount# to #currency# account #account#. Batch is #batch#.',1),
	('withdraw_request_admin_notification','Administrator Withdrawal Request Notification','Withdrawal Request has been sent','User #username# requested to withdraw $#amount# from IP #ip#.',1),
	('withdraw_request_user_notification','User Withdrawal Request Notification','Withdrawal Request has been sent','Hello #name#,\r\n\r\n\r\nYou has requested to withdraw $#amount#.\r\nRequest IP address is #ip#.\r\n\r\n\r\nThank you.\r\n#site_name#\r\n#site_url#',1),
	('withdraw_user_notification','User Withdrawal Notification','Withdrawal has been sent','Hello #name#.\r\n\r\n$#amount# has been successfully sent to your #currency# account #account#.\r\nTransaction batch is #batch#.\r\n\r\n#site_name#\r\n#site_url#',1);

TRUNCATE TABLE `plans`;
INSERT INTO `plans` (`id`, `name`, `description`, `min_deposit`, `max_deposit`, `percent`, `status`, `parent`)
VALUES
	(1,'Plan 1',NULL,10.00,500.00,2.00,NULL,3),
	(2,'Plan 2',NULL,501.00,1000.00,5.00,NULL,3),
	(3,'Plan 1',NULL,500.00,3000.00,200.00,NULL,2),
	(4,'Plan 2',NULL,3001.00,10000.00,250.00,NULL,2),
	(5,'Plan 3',NULL,10001.00,50000.00,300.00,NULL,2),
	(6,'Plan 1',NULL,300.00,3000.00,10.00,NULL,1),
	(7,'Plan 2',NULL,3001.00,10000.00,15.00,NULL,1),
	(8,'Plan 3',NULL,10001.00,30000.00,20.00,NULL,1);

TRUNCATE TABLE `processings`;
INSERT INTO `processings` (`id`, `name`, `infofields`, `status`, `description`)
VALUES
	(1,'Bank Wire','a:3:{i:1;s:9:\"Bank Name\";i:2;s:12:\"Account Name\";i:3;s:15:\"Payment Details\";}',0,'Send your bank wires here:<br>\r\nBeneficiary\'s Bank Name: <b>Your Bank Name</b><br>\r\nBeneficiary\'s Bank SWIFT code: <b>Your Bank SWIFT code</b><br>\r\nBeneficiary\'s Bank Address: <b>Your Bank address</b><br>\r\nBeneficiary Account: <b>Your Account</b><br>\r\nBeneficiary Name: <b>Your Name</b><br>\r\n\r\nCorrespondent Bank Name: <b>Your Bank Name</b><br>\r\nCorrespondent Bank Address: <b>Your Bank Address</b><br>\r\nCorrespondent Bank codes: <b>Your Bank codes</b><br>\r\nABA: <b>Your ABA</b><br>'),
	(2,'e-Bullion','a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}',0,'Please send your payments to this account: <b>Your e-Bullion account</b>'),
	(3,'NetPay','a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}',0,'Send your funds to account: <b>Your NetPay account</b>'),
	(4,'GoldMoney','a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}',0,'Send your fund to account: <b>your GoldMoney account</b>'),
	(5,'MoneyBookers','a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}',0,'Send your funds to account: <b>your MoneyBookers account</b>'),
	(6,'Pecunix','a:2:{i:1;s:19:\"Your e-mail address\";i:2;s:16:\"Reference Number\";}',0,'Send your funds to account: <b>your Pecunix account</b>'),
	(7,'PicPay','a:2:{i:1;s:13:\"Payer Account\";i:2;s:14:\"Transaction ID\";}',0,'Send your funds to account: <b>Your PicPay account</b>');

TRUNCATE TABLE `referal`;
INSERT INTO `referal` (`id`, `level`, `name`, `from_value`, `to_value`, `percent`, `percent_daily`, `percent_weekly`, `percent_monthly`)
VALUES
	(1,1,'Level A',1,2,2.00,NULL,NULL,NULL),
	(2,1,'Level B',3,5,3.00,NULL,NULL,NULL),
	(3,1,'Level C',6,10,5.00,NULL,NULL,NULL),
	(4,1,'Level D',11,20,7.50,NULL,NULL,NULL),
	(5,1,'Level E',21,0,10.00,NULL,NULL,NULL);

TRUNCATE TABLE `types`;
INSERT INTO `types` (`id`, `name`, `description`, `q_days`, `min_deposit`, `max_deposit`, `period`, `status`, `return_profit`, `return_profit_percent`, `percent`, `pay_to_egold_directly`, `use_compound`, `work_week`, `parent`, `withdraw_principal`, `withdraw_principal_percent`, `withdraw_principal_duration`, `compound_min_deposit`, `compound_max_deposit`, `compound_percents_type`, `compound_min_percent`, `compound_max_percent`, `compound_percents`, `closed`, `withdraw_principal_duration_max`, `dsc`, `hold`, `delay`)
VALUES
	(1,'10%-20% daily for 25 days',NULL,25,NULL,NULL,'d','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0),
	(2,'200%-300% after 7 days',NULL,7,NULL,NULL,'end','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0),
	(3,'basic plan',NULL,1000,NULL,NULL,'w','on','0',0.00,NULL,0,0,0,0,0,0.00,0,0.00,0.00,0,0.00,0.00,'0',0,0,'',0,0);

TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `name`, `username`, `password`, `date_register`, `email`, `status`, `came_from`, `ref`, `deposit_total`, `confirm_string`, `ip_reg`, `last_access_time`, `last_access_ip`, `stat_password`, `auto_withdraw`, `user_auto_pay_earning`, `admin_auto_pay_earning`, `pswd`, `hid`, `question`, `answer`, `l_e_t`, `activation_code`, `bf_counter`, `address`, `city`, `state`, `zip`, `country`, `transaction_code`, `payeer_account`, `perfectmoney_account`, `bitcoin_account`, `intgold_account`, `evocash_account`, `egold_account`, `stormpay_account`, `ebullion_account`, `paypal_account`, `goldmoney_account`, `eeecurrency_account`, `pecunix_account`, `ac`, `is_test`, `explicit_password`, `remember_token`)
VALUES
	(1,'admin','miadmin','19f7408c270641948156705cd9ad83a9',NULL,'midollaradm@gmail.com','on','     ',0,0.00,'','','2017-08-16 21:47:51','127.0.0.1','',1,0,0,'','94E4319ECBECCE966F0E8050330AC8','','','2017-08-16 14:36:24','',0,'','','','','','','','','',0,0,0,'','','','',0,0,'24780f023f460c7c7b1a50244c5c51446928486609327f7a021a205c4524235451251a02410a07750266562431275b4c1b57442a364b51331a02410a0e7b1a205b322420545d20170d367b0d0e635d5453595a630337087374781a552d5159292d5946205c5472575b2051281c222a2f1a03370f07777b1a58204b4d6d52442e4f37573367794b02740f14677a4b0e76021b5e514535672d42637e3102087e17147e3202077b1a495b5e147a4b7e027b6760034b7e0c0c67355159244b4d535d4663032d08717e3f',0,NULL,'AxZHljqz6AY7FtMotj4F8FME2TVlos4diKw0bkJZx6rVcxZ3Y30j6mdo4XXy'),
	(2,'test','test','098f6bcd4621d373cade4e832627b4f6','2017-03-26 10:14:33','1194316669@qq.com','on','',0,0.00,'','115.148.45.42','2017-04-16 08:55:48','183.15.243.58','',1,0,0,'','3F22C6237FE3549A44398B8FBDC1CE','','','2017-08-13 23:14:59','',0,'','','','','','','','U1591235','',0,0,0,'','','','',0,0,'',0,NULL,NULL);
