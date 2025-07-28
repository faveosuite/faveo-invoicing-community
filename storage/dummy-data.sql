-- phpMyAdmin SQL Dump
-- version 4.0.10.14
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2017 at 08:00 AM
-- Server version: 5.5.54-38.6-log
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `installer`
--


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255)  NOT NULL,
  `first_name` varchar(255)  NOT NULL,
  `last_name` varchar(255)  NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(60) NOT NULL,
  `company` varchar(255) NOT NULL,
  `company_type` varchar(255)  NOT NULL,
  `company_size` varchar(255)  NOT NULL,
  `bussiness` varchar(255) NOT NULL,
  `mobile` varchar(255)  NOT NULL,
  `mobile_code` varchar(255)  NOT NULL,
  `address` varchar(255)  NOT NULL,
  `town` varchar(255)  NOT NULL,
  `state` varchar(255)  NOT NULL,
  `zip` varchar(255)  NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `active` int(11)  NOT NULL,
  `role` varchar(255) NOT NULL,
  `currency` varchar(255)  NOT NULL,
  `debit` decimal(10,0)	  DEFAULT NULL,
  `timezone_id` int(11)  NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
   `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country` varchar(255) NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `mobile_verified` int(11)  NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `skype` varchar(255) DEFAULT NULL,
  `manager` int(11)  DEFAULT NULL,
 
  PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Dumping data for table `labels`
--

INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `password`,`company`,`company_type`
	,`company_size`,`bussiness`,`mobile`,`mobile_code`,`address`,`town`,`state`,`zip`,`profile_pic`
	,`active`,`role`,`currency`,`debit`,`timezone_id`,`remember_token`, `created_at`, `updated_at`,`country`,`ip`,
	`mobile_verified`,`position`,`skype`,`manager`) VALUES
(1, 'demo', 'Demo', 'admin', 'demo@admin.com', '$2y$10$EceQcMGzqFS8fC2Ks7TOLOt0a.NQOh69xMQO18vlo5dCt1CCpqc96'  , 'ladybird' , 'privately-held', 
  '2-10', 'aviation_aerospace', '9999999999', '91', 'Nearest Place Around', 'Mumbai','IN-KA','123456', '', 
  '1', 'admin', 'INR', '0', '79', '','2017-03-29 22:41:17', '2017-03-29 22:41:17', 'IN' , '106.51.140.178', '1','','','0'),
(2, 'rico', 'Rico', 'Vanderval', 'faveomails@gmail.com','$2y$10$EceQcMGzqFS8fC2Ks7TOLOt0a.NQOh69xMQO18vlo5dCt1CCpqc96'  , 'MydemoCompany' , 'privately-held', 
	'2-10', 'aviation_aerospace', 'rico', '91', 'Nearest Place Around', 'Mumbai','IN-MH','123456', '', 
	'1', 'user', 'INR', '0', '79', '','2017-03-29 22:41:17', '2017-03-29 22:41:17', 'IN' , '106.51.140.178', '1','','','0'),
(3, 'aniel', 'Aniel', 'Simmons', 'sunny_2693@yahoo.com','$2y$10$EceQcMGzqFS8fC2Ks7TOLOt0a.NQOh69xMQO18vlo5dCt1CCpqc96'  , 'MydemoCompany1' , 'privately-held', 
	'2-10', 'aviation_aerospace', '1111111111', '51', 'Nearest Place Around', 'Sydney','AU-QL','123456', '', 
	'1', 'user', 'USD', '0', '79', '', '2017-03-29 22:41:17', '2017-03-29 22:41:17','AU' , '106.51.140.178', '1','','','0');





CREATE TABLE IF NOT EXISTS `tax_by_states` (
  `id` smallint(5) unsigned NOT NULL,
  `country` varchar(200)  DEFAULT NULL,
  `state_code` varchar(200)  DEFAULT NULL,
  `state` varchar(200)  DEFAULT NULL,
  `c_gst` varchar(80) DEFAULT NULL,
  `s_gst` varchar(80)  DEFAULT NULL,
  `i_gst` varchar(50) DEFAULT NULL,
  `ut_gst` varchar(50)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `tax_by_states` (`id`,`country`,`state_code`, `state`, `c_gst`, `s_gst` ,`i_gst` , `ut_gst`,`created_at`, `updated_at`) VALUES
(1, 'IN','IN-AN','Andaman Nicobar Islands', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'IN','IN-AP','Andhra Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, 'IN','IN-AR','Arunachal Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'IN','IN-AS','Assam', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, 'IN','IN-BR','Bihar', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(6, 'IN','IN-CH','Chandigarh', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(7, 'IN','IN-CT','Chhattisgarh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(8, 'IN','IN-DN','Dadra and Nagar Haveli', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(9, 'IN','IN-DD','Daman and Diu', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(10,'IN','IN-DL', 'Delhi', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(11, 'IN','IN-GA','Goa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(12, 'IN','IN-GJ','Gujarat', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(13, 'IN','IN-HR','Haryana', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(14, 'IN','IN-HP','Himachal pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(15, 'IN','IN-JK','Jammu and Kashmir', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(16, 'IN','IN-JH','Jharkand', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(17, 'IN','IN-KA','Karnataka', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(18, 'IN','IN-KL','Kerala', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(19, 'IN','IN-LD','Lakshadweep', '9', 'NULL', '18','9','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(20, 'IN','IN-MP','Madhya Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(21, 'IN','IN-MH','Maharashtra', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(22, 'IN','IN-MN','Manipur', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(23, 'IN','IN-ML','Megalaya', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(24, 'IN','IN-MZ','Mizoram', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(25, 'IN','IN-NL','Nagaland', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(26, 'IN','IN-OR','Orissa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(27, 'IN','IN-OR','Orissa', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(28, 'IN','IN-PY','Pondichery', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(29, 'IN','IN-PB','Punjab', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(30, 'IN','IN-RJ','Rajastan', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(31, 'IN','IN-SK','Sikkim', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(32, 'IN','IN-TN','Tamilnadu', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(33, 'IN','','Telangana', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(34, 'IN','IN-TR','Tripura', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(35, 'IN','IN-UP','Uttar Pradesh', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(36, 'IN','IN-UL','Uttaranchal', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(37, 'IN','IN-WB','West Bengal', '9', '9', '18','NULL','2018-04-13 12:53:58','2018-04-13 12:53:58');


CREATE TABLE IF NOT EXISTS `currencies` (
  `id` smallint(5) unsigned NOT NULL,
  `code` varchar(200)  DEFAULT NULL,
  `symbol` varchar(200)  DEFAULT NULL,
  `name` varchar(200)  DEFAULT NULL,
  `base_conversion` varchar(80) DEFAULT NULL,
  `created_at` varchar(80)  DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `currencies` (`id`,`code`,`symbol`, `name`, `base_conversion`,`created_at`, `updated_at`) VALUES
(1, 'USD','$','US Dollar', '1.0','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'INR','₹','Indian Rupee', '1,0', '2018-04-13 12:53:58','2018-04-13 12:53:58');




CREATE TABLE IF NOT EXISTS `tax_rules` (
  `id` smallint(5) unsigned NOT NULL,
  `tax_enable` int(11)  DEFAULT NULL,
  `inclusive` int(11)  DEFAULT NULL,
  `rounding` int(11)  DEFAULT NULL,
  `created_at` varchar(80)  DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tax_rules` (`id`,`tax_enable`,`inclusive`, `rounding`, `created_at`, `updated_at`) VALUES
(1, '1','0','1', '2018-04-13 12:53:58','2018-04-13 12:53:58');




INSERT INTO `products` (`id`,`name`,`type`,`group`, `created_at`, `updated_at`) VALUES
(1, 'default','2','1','2018-04-13 12:53:58','2018-04-13 12:53:58');

INSERT INTO `periods` (`id`,`name`,`days`, `created_at`, `updated_at`) VALUES
(1, '1 months','30','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, '1 year','365','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, '2 year','730','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, '3 year','1095','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, '4 year','1460','2018-04-13 12:53:58','2018-04-13 12:53:58');


INSERT INTO `product_groups` (`id`,`name`,`headline`,`tagline`,`available_payment`,`hidden`,`cart_link`, `created_at`, `updated_at`) VALUES
(1, 'none','none','none','none',0,'none','2018-04-13 12:53:58','2018-04-13 12:53:58');


INSERT INTO `product_types` (`id`,`name`,`description`, `created_at`, `updated_at`) VALUES
(1, 'Saas','','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'Download','', '2018-04-13 12:53:58','2018-04-13 12:53:58');



INSERT INTO `promotion_types` (`id`,`name`,`created_at`, `updated_at`) VALUES
(1, 'Percentage','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'Fixed Amount', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, 'Price Override','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'Free Setup', '2018-04-13 12:53:58','2018-04-13 12:53:58');



INSERT INTO `template_types` (`id`,`name`,`created_at`, `updated_at`) VALUES
(1, 'welcome_mail','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'forgot_password_mail', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'subscription_going_to_end_mail','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, 'subscription_over_mail', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(6, 'invoice_mail', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(7, 'order_mail', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(8, 'download_mail', '2018-04-13 12:53:58','2018-04-13 12:53:58'),
(9, 'manager_email', '2018-04-13 12:53:58','2018-04-13 12:53:58');
 

 INSERT INTO `templates` (`id`,`name`,`type`,`url`,`data`,`created_at`, `updated_at`) VALUES
(2, '[Faveo Helpdesk] Verify your email address','1','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;"> </td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;"> </td>
</tr>
<tr>
<td style="width: 30px;"> </td>
<td style="width: 640px;  padding-top: 30px;">
<table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
<p>Dear {{name}}, <br /><br /> Before you can login, you must active your account. Click <a href="{{url}}">{{url}}</a> to activate your account.<br /><br /> <strong>Your Profile & Control Panel Login</strong><br /><br /> You can start exploring our feature-rich Control Panel, which will allow you to manage all your Products, buy new Products, check all your transactions and more.<br /><br /> <strong>Login Details:</strong><br /> <strong>URL: </strong><a href="https://www.billing.faveohelpdesk.com/">https://www.billing.faveohelpdesk.com</a> <br /> <strong>Username:</strong> {{username}}<br /> <strong>Password:</strong> If you can not recall your current password, <a href="https://www.billing.faveohelpdesk.com/public/password/email">click here</a> to request a new password to login.<br /><br /> Thank You.<br /> Regards,<br /> Faveo Helpdesk</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; padding: 0; width: 560px;" align="left"> </td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank">Verify Email </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;"> </td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="www.faveohelpdesk.com">https://www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
</tbody>
</table>
<p> </p>','2018-04-13 12:53:58','2018-04-13 12:53:58'),

(4, '[Faveo Helpdesk] Purchase confirmation','7','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="http://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your {{product}} order</h1>
<br /> Your order and payment details are below.</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0 30px                        0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order Number</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Download</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{downloadurl}}" target="_blank"> Download </a></td>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{expiry}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click below to login to your Control Panel to view invoice or to pay for any pending invoice.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{invoiceurl}}" target="_blank"> View Invoice </a></td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /> <a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /> <a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>', '2018-04-13 12:53:58','2018-04-13 12:53:58'),


(6, '[Faveo Helpdesk] Consolidated renewal reminder','4','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="http://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your orders are expiring soon.<br /> Renew them now.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Some of your orders are expiring soon (or have already expired.) Please renew them before they are deleted to avoid loss of data.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order ID</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}} <br /><span style="color: #ad7b33; font-family: Arial, sans-serif; font-size: 12px;">Expiring Soon!</span></td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your orders.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank"> Renew Order </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>','2018-04-13 12:53:58','2018-04-13 12:53:58'),


(5, '[Faveo Helpdesk] Reset your password','2','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="http://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
<p>Dear {{name}},<br /><br /> A request to reset password was received from your account.&nbsp; Use the link below to reset your password and login.<br /><br /> <strong>Link:</strong>&nbsp; <a href="{{url}}">{{url}}</a><br /><br /> Thank You.<br /> Regards,<br /> Faveo Helpdesk<br /><br /> <strong>IMP:</strong> If you have not initiated this request, <a href="https://www.faveohelpdesk.com/contact-us/">report it to us immediately</a>.<br /><br /> <em>This is an automated email, please do not reply.</em></p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank">Reset Password </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:support@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="http://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>','2018-04-13 12:53:58','2018-04-13 12:53:58'),


(7, '[Faveo Helpdesk] URGENT: Order has expired','5','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your orders has expired.<br /> Renew them now.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Some of your orders are expired. Please renew them before they are deleted to avoid loss of data.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order ID</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your orders.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank"> Renew Order </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>', '2018-04-13 12:53:58','2018-04-13 12:53:58'),


(8, '[Faveo Helpdesk] Invoice','6','null','<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://www.faveohelpdesk.com/billing/public/cart/img/logo/faveo.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your order</h1>
<br /> Your order and payment details are below.</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0 30px                        0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Invoice Number</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Total</th>
</tr>
</thead>
<tbody>
<tr>
<td>&nbsp;{{content}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click below to login to your Control Panel to view invoice or to pay for any pending invoice.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{invoiceurl}}" target="_blank"> Invoice </a></td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /> <a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /> <a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>', '2018-04-13 12:53:58','2018-04-13 12:53:58'),


(9, '[Faveo Helpdesk] Your New Account Manager','9','null','<p>Dear {{name}},</p>
<p>This is {{manager_first_name}} {{manager_last_name}}.</p>
<p>From now onwards I will be your one point of contact. I will followup with you as well as with our team. Please feel free to get in touch with me anytime if you have any issues with regards to your account. You can also add me on Skype. My ID is mentioned in my signature. It is a pleasure to have you on board and I look forward to effective conversations with you in future.</p>
<p>Hope you have a great day.</p>
<p>Regards,</p>
<p>{{manager_first_name}}{{manager_last_name}}</p>
<p>Account Manager,<br /> Faveo Helpdesk<br /> Mobile :{{manager_code}} {{manager_mobile}}<br /> Skype ID : {{manager_skype}}<br /> Email : {{manager_email}}</p>', '2018-04-13 12:53:58','2018-04-13 12:53:58');


INSERT INTO `githubs` (`id`,`client_id`,`client_secret`,`username`,`password`,`created_at`, `updated_at`) VALUES
(1, '','','','','2018-04-13 12:53:58','2018-04-13 12:53:58');

INSERT INTO `api_keys` (`id`,`rzp_key`,`rzp_secret`,`apilayer_key`,`bugsnag_api_key`,`zoho_api_key`,`msg91_auth_key`,`created_at`, `updated_at`) VALUES
(1, '','','','','','','2018-04-13 12:53:58','2018-04-13 12:53:58');




INSERT INTO `tax_classes` (`id`,`name`,`created_at`, `updated_at`) VALUES
(1, 'Intra State GST','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'Inter State GST','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, 'Union Territory GST','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'Others','2018-04-13 12:53:58','2018-04-13 12:53:58');

INSERT INTO `taxes` (`id`,`tax_classes_id`,`level`,`active`,`name`,`country`,`state`,`rate`,`compound`,`created_at`, `updated_at`) VALUES
(1, '1','0','1','CGST+SGST','IN','Any State','','0','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, '2','0','1','IGST','IN','Any State','','0','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, '3','0','1','CGST+UTGST','IN','Any State','','0','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, '4','0','1','Others','AU','AU-QLD','20','0','2018-04-13 12:53:58','2018-04-13 12:53:58');



INSERT INTO `frontend_pages` (`id`,`parent_page_id`,`slug`,`name`,`content`,`url`,`type`,`publish`,`hidden`,`created_at`, `updated_at`) VALUES
(1, '0','pricing','pricing','
                            <div class="col-md-3 col-sm-6">
                                <div class="plan">
                                    <h3>{{name}}<span>{{price}}</span></h3>
                                    <ul>
                                        <li>{{feature}}</li>
                                    </ul><br />
                                    <ul>
                                       
                                       <li class="subscription">{{subscription}}</li>
                                        <li>{{url}}</li> 
                                    </ul>
                                </div>
                            </div>
              ','','cart','1','1','2018-04-13 12:53:58','2018-04-13 12:53:58');



INSERT INTO `settings` (`id`,`company`,`website`,`city`,`state`,`country`,`error_log`,`invoice`,`subscription_over`,`subscription_going_to_end`,`forgot_password`,`order_mail`,`welcome_mail`,`download`,`invoice_template`,`created_at`, `updated_at`) VALUES
(1, 'Ladybird Web Solution','http://www.ladybirdweb.com','Bangalore','IN-KA','IN','1','8','7','6','5','4','2','9','8','2018-04-13 12:53:58','2018-04-13 12:53:58');



INSERT INTO `company_types` (`id`,`name`,`short`,`created_at`, `updated_at`) VALUES
(1, 'Public Company','public-company','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, 'Self Employed','self-employed','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, 'Non Profit','non-profit','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, 'Privately Held','privately held','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, 'Partnership','partnership','2018-04-13 12:53:58','2018-04-13 12:53:58');


INSERT INTO `company_sizes` (`id`,`name`,`short`,`created_at`, `updated_at`) VALUES
(1, 'Myself only','myself-only','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(2, '2-10','2-10','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(3, '11-50','11-50','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(4, '51-200','51-200','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(5, '201-500','201-500','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(6, '501-1000','501-1000','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(7, '1001-5000','1001-5000','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(8, '5001-10000','5001-10000','2018-04-13 12:53:58','2018-04-13 12:53:58'),
(9, '10001+','10001','2018-04-13 12:53:58','2018-04-13 12:53:58');





