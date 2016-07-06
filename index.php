<?php

// 
// Beispiele für die verwendung der Fritz Klasse
// 

include( 'fritz.php' );

$fritz = new Fritz( '192.168.178.1', 'fritzpasswd' ); // simple instanciating (default username fritz-api)
//$fritz = new Fritz( '192.168.178.1', 'fritzpasswd', 'fritzuser' ); // instanciating with username

// 
// call forwarding to number
// 

//$backurl = '';
//$uid = 'rul_0'; 					// rul_0 für ersten eintrag, rul_1 für zweiten, etc
//$number = '0531123123123';	// destination number
//$callerid = 'SIP5';

//echo "Enable rul for num ($number): ";

// Tamper data from fritzbox to find your values!
//$data = array(
//	'mode_new' => 'all',
//	'call_to_sel' => 'tochoose',
//	'num_from' => '',
//	'unknown_num' => 'tochoose',
//	'destination' => 'phone',
//	'num_dest_sel' => 'new_num',
//	'num_dest' => $number, // zielrufnummer
//	'num_out' => '*',
//	'type_action' => '0',
//	'type_diversion' => '0',
//	'apply' => '',
//	'back_to_page' => $backurl,
//	'is_new' => 'false',
//	'uid' => $uid,
//	'caller_id' => $callerid,
//	'mode' => 'call_to',
//	'rul_type' => 'rul'
//);

//echo (int) $fritz->setFormData( "/fon_num/rul_edit.lua", $data );


// 
// Rufumleitung auf bekannte Nummer einschalten
// 

//$formurl = $fritz->buildUrl( "/fon_num/rul_edit.lua" ); // fon_num/rul_edit.lua
//$backurl = '';
//$uid = 'rul_0'; 					// rul_0 für ersten eintrag, rul_1 für zweiten, etc
//$number = '0531123123123';	// destination number
//$callerid = 'SIP5';

//echo "Enable rul for num ($number): ";

//$data = array(
//	'mode_new' => 'all',
//	'call_to_sel' => 'tochoose',
//	'num_from' => '',
//	'unknown_num' => 'tochoose',
//	'destination' => 'phone',
//	'num_dest_sel' => '32_0',
//	'num_dest' => $number, // zielrufnummer
//	'num_out' => '*',
//	'type_action' => '0',
//	'type_diversion' => '0',
//	'apply' => '',
//	'back_to_page' => $backurl,
//	'is_new' => 'false',
//	'uid' => $uid,
//	'caller_id' => $callerid,
//	'mode' => 'call_to',
//	'rul_type' => 'rul'
//);

//echo (int) $fritz->setFormData( "/fon_num/rul_edit.lua", $data );



// 
// Rufumleitung ausschalten (alle!)
// 

//echo "Disable rul: ";
//$data = array(
//	'apply' => '',
//	'back_to_page' => ''
//);
//echo (int) $fritz->setFormData( "/fon_num/rul_list.lua", $data );

// 
// Rufumleitung ausschalten (nur die erste)
// 

echo "Disable rul: ";
$data = array(
	'rul_1' => 'on',
	'apply' => '',
	'back_to_page' => ''
);
echo (int) $fritz->setFormData( "/fon_num/rul_list.lua", $data );




//// GetInfo (string)
//echo "Model: " . $fritz->getInfo( 'model' ) . "<br />";				// Get FritzBox Model
//echo "Serial: " . $fritz->getInfo( 'serial' ) . "<br />";			// Get FritzBox Serialnumber
//echo "Version: " . $fritz->getInfo( 'version' ) . "<br />";		// Get FritzBox Software-Version
//echo "Uptime: " . $fritz->getInfo( 'uptime' ) . "<br />";			// Get FritzBox Uptime
//echo "Log: <br />" . $fritz->getInfo( 'log', 10 ) . "<br />";	// Get FritzBox last 17 lines 

// Last reconnect (datetime)
//echo $fritz->getLastConnection() . " Uhr";
//echo '<br />';

// Downlink speed (float)
//echo $fritz->getDownSpeed() . " Mbit/s Down";
//echo '<br />';

// Uplink speed (float)
//echo $fritz->getUpSpeed() . " Mbit/s Up";
//echo '<br />';

// Get sid (string)
// echo $fritz->getSID();

// Get WAN status (array)
//echo '<pre>';
//print_r( $fritz->statusWAN() );
//echo '</pre>';

// Get Callerlist (array)
//echo '<pre>';
//print_r( $fritz->getCallerlist() );
//echo '</pre>';

// Get Phonebook (array)
//echo '<pre>';
//print_r( $fritz->getPhonebook() );
//echo '</pre>';

// Anrufbeantworter Ein/Ausschalten
//$fritz->setTam( 'on' ); // Einschalten
//$fritz->setTam( 'off' ); // Ausschalten

// Anrufbeantworter Anruferliste (array)
//echo '<pre>';
//print_r( $fritz->getTamList() );
//echo '</pre>';

// Wifi 2.4 GHz
//$fritz->setWifi( true, '2.4' ); // Einschalten
//$fritz->setWifi( false, '2.4' ); // Ausschalten

// Wifi 5 GHz
//$fritz->setWifi( true, '5' ); // Einschalten
//$fritz->setWifi( false, '5' ); // Ausschalten

// Guest-Wifi 
//$fritz->guestWifi( true ); // Einschalten
//$fritz->guestWifi( false ); // Ausschalten


//
// Use this commands with care!!
// 

// Reconnect 
//$fritz->reconnectDSL();

// Reboot 
//$fritz->reboot();


?>
