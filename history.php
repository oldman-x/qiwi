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

// Payment history (only for Qiwi wallet)
	$qiwi_num=1;	// Select wallet
	$rows = 2;	// How many rows 1-50
	$operation = "ALL";	// ALL - all transaction, IN - income transaction, OUT - outgoing transaction
	
	$_history = new qiwi;
		
	$history=$_history->payment_history($qiwi_num, $rows, $operation);
	

    echo "<pre>";
	print_r($history);
    echo "</pre>";	
 
?>
