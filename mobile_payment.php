<?php
/*
 * Copyright 2019 oldman-x <oldman-x@xmpp.jp>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 */

	include('class_qiwi.php');
	$configs = include('config.php');
	
// Mobile Payment
	$qiwi_num=1;	// Select wallet for payment
	$sum = 1;	// Sum for payment (rub)
	$phone = 1234567890;	// Target 10 digits without 7
	
	$_mpayment = new qiwi;
	$mpayment=$_mpayment->mobile_payment($qiwi_num, $sum, $phone);
	
    echo "<pre>";
	print_r($mpayment);
    echo "</pre>"; 

?>
