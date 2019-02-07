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
	
// Get balans
	$qiwi_num=1;	// Select wallet
	$_balans = new qiwi;
	$balans=$_balans->get_balans($qiwi_num);

    echo "<pre>";
	print_r($balans);
    echo "</pre>";
    
?>
