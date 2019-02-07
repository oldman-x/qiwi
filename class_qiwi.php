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
 
class qiwi {
// Define variables
	public $qiwi_num;
	public $sum;
	public $phone;
	public $rows;
	public $operation;
	public $comment;
	
// Function Get Balans	
	public function get_balans($qiwi_num) {
	$configs=include('config.php');
	
	// Get Person ID
	$q='qiwi_'.$qiwi_num;	
	$phonenumber = $configs->$q->phone_number;
	$phoneprefix = $configs->$q->phone_prefix;
	$personid=$phoneprefix.$phonenumber;
	
	// Get API Key
	$apikey = $configs->$q->api_key;
	
	$header=array(
		'Accept: application/json',
		'Authorization: Bearer '.$apikey,
		'Host: edge.qiwi.com'
		);
	$ch = curl_init();
	$timeout = 60;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_URL, 'https://edge.qiwi.com/funding-sources/v2/persons/'.$personid.'/accounts');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	$result = curl_exec($ch);
	$response=http_response_code(); // Get and check server response code
	if ($response != 200) {
	$result = 'Error: '.$response;
	curl_close($ch);
	return $result;
	exit;	
	}
	
	$result = json_decode($result, true);	// Decode result
	
	echo curl_error($ch);
	curl_close($ch);
	return $result;

	}
	
// Function mobile payment
	public function mobile_payment($qiwi_num, $sum, $phone) {
	$configs=include('config.php');
		
	// Get Person ID
	$q='qiwi_'.$qiwi_num;	
	$phonenumber = $configs->$q->phone_number;
	$phoneprefix = $configs->$q->phone_prefix;
	$personid=$phoneprefix.$phonenumber;
	
	// Get API Key
	$apikey = $configs->$q->api_key;
	
	// Validate phone number (phone number without country code (witout 7 for Russia)	
	if (strlen($phone)!=10) {
	$result = 'Error: Phone number is not valid';
	return $result;
	exit;
	}
	
	if (filter_var($phone, FILTER_VALIDATE_INT) === false) {
	$result = 'Error: Phone number is not valid';
	return $result;
	exit;
	}
	
	// Validate sum	
	if (filter_var($sum, FILTER_VALIDATE_INT) === false) {
	$result = 'Error: Invalid sum';
	return $result;
	exit;
	}

	if (strlen($sum)>4){
	$result = 'Error: Invalid sum';
	return $result;
	exit;
	}
		
	// Get operator ID
	$header=array(
		'Host: qiwi.com',
		'Accept: application/json',
		'Content-Type: application/x-www-form-urlencoded',
		'Cache-Control: no-cache'
		);
	$ch = curl_init();
	$timeout = 60;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'phone=7'.$phone);	// Only Russian number (7)
	curl_setopt($ch, CURLOPT_URL, 'https://qiwi.com/mobile/detect.action');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	$result = curl_exec($ch);
	$response=http_response_code(); // Get and check server response code
	if ($response != 200) {
	$result = 'Error: '.$response;
	curl_close($ch);
	return $result;
	exit;	
	}

	$result = json_decode($result, true);	// Decode result
	$status = $result['code']['value']; // Extract status (if not 0 -> Error)
	if ($status != 0) {
	$result = 'Error: Operator not defined';
	curl_close($ch);
	return $result;
	exit;	
	}
	
	$operator_id=$result['message'];
	
	// Send money to mobile phone
		// Create TimeStamp
    $date = new DateTime(); 
    $tStamp=$date->getTimestamp();
		// Create transaction id
	$transactionID = intval($tStamp)*1000;
	
		// Setup body
	$transactionBody = '{"id":"'.$transactionID.'","sum":{"amount":'.$sum.',"currency":"643"},"paymentMethod":{"type":"Account","accountId":"643"},"fields":{"account":"'.$phone.'"}}';
		
		$header=array(
		'Content-Type: application/json',
		'Accept: application/json',
		'Authorization: Bearer '.$apikey,
		'Host: edge.qiwi.com'
		);
	$ch = curl_init();
	$timeout = 60;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $transactionBody);
	curl_setopt($ch, CURLOPT_URL, 'https://edge.qiwi.com/sinap/api/v2/terms/'.$operator_id.'/payments');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	$result = curl_exec($ch);
	
	// Get and check server response code
	$response=http_response_code();
	if ($response != 200) {
	$result = 'Error: '.$response;
	curl_close($ch);
	return $result;
	exit;	
	}

	$result = json_decode($result, true);	// Decode result
	
	echo curl_error($ch);
	curl_close($ch);
	return $result;

	}

// Function get mobile history (Qiwi wallet, rub)
	public function payment_history($qiwi_num, $rows, $operation) {
	$configs=include('config.php');
	
	// Get Person ID
	$q='qiwi_'.$qiwi_num;	
	$phonenumber = $configs->$q->phone_number;
	$phoneprefix = $configs->$q->phone_prefix;
	$personid=$phoneprefix.$phonenumber;
	
	// Get API Key
	$apikey = $configs->$q->api_key;
	
	$header=array(
		'Accept: application/json',
		'Authorization: Bearer '.$apikey,
		'Host: edge.qiwi.com'
		);
	$ch = curl_init();
	$timeout = 60;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_URL, 'https://edge.qiwi.com//payment-history/v2/persons/'.$personid.'/payments?rows='.$rows.'&operation='.$operation.'&sources[0]=QW_RUB');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	$result = curl_exec($ch);
	$response=http_response_code(); // Get and check server response code
	if ($response != 200) {
	$result = 'Error: '.$response;
	curl_close($ch);
	return $result;
	exit;	
	}
	
	$result = json_decode($result, true);	// Decode result
	
	echo curl_error($ch);
	curl_close($ch);
	return $result;
		
	}

// Function send money to Qiwi (comission not included)
	public function send_qiwi($qiwi_num, $phone, $sum, $comment) {
	$configs=include('config.php');
		
	// Get Person ID
	$q='qiwi_'.$qiwi_num;	
	$phonenumber = $configs->$q->phone_number;
	$phoneprefix = $configs->$q->phone_prefix;
	$personid=$phoneprefix.$phonenumber;
	
	// Get API Key
	$apikey = $configs->$q->api_key;
	
	// Validate phone number (phone number without country code (witout 7 for Russia)	
	if (strlen($phone)!=10) {
	$result = 'Error: Phone number is not valid';
	return $result;
	exit;
	}
	
	if (filter_var($phone, FILTER_VALIDATE_INT) === false) {
	$result = 'Error: Phone number is not valid';
	return $result;
	exit;
	}
	
	// Validate sum	
	if (filter_var($sum, FILTER_VALIDATE_INT) === false) {
	$result = 'Error: Invalid sum';
	return $result;
	exit;
	}

	if (strlen($sum)>4){
	$result = 'Error: Invalid sum';
	return $result;
	exit;
	}
	
	// Validate comment (<= 50 characters)
	if (strlen($comment)>50) {
	$result = 'Error: Comment more than 50 characters';
	return $result;
	exit;
	}

	
	// Send money to Qiwi
		// Create TimeStamp
    $date = new DateTime(); 
    $tStamp=$date->getTimestamp();
		// Create transaction id
	$transactionID = intval($tStamp)*1000;
	
		// Setup body
	$transactionBody = '{"id":"'.$transactionID.'","sum":{"amount":'.$sum.',"currency":"643"},"paymentMethod":{"type":"Account","accountId":"643"},"comment":"'.$comment.'","fields":{"account":"+7'.$phone.'"}}';
	
		// Setup operator_id
	$operator_id=99; // Qiwi wallet
		
		$header=array(
		'Content-Type: application/json',
		'Accept: application/json',
		'Authorization: Bearer '.$apikey,
		'Host: edge.qiwi.com'
		);
	$ch = curl_init();
	$timeout = 60;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $transactionBody);
	curl_setopt($ch, CURLOPT_URL, 'https://edge.qiwi.com/sinap/api/v2/terms/'.$operator_id.'/payments');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	$result = curl_exec($ch);
	
	// Get and check server response code
	$response=http_response_code();
	if ($response != 200) {
	$result = 'Error: '.$response;
	curl_close($ch);
	return $result;
	exit;	
	}

	$result = json_decode($result, true);	// Decode result
	
	echo curl_error($ch);
	curl_close($ch);
	return $result;

	}
	
}

?>
