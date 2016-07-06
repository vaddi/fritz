<?php

//
// Simple class to automate some FritzBox stuff
// Base Idea: https://www.symcon.de/forum/threads/25745-FritzBox-mit-SOAP-auslesen-und-steuern
// Build as PHP class and extend for send requests via web to FritzBox 
//

class Fritz {
	
	protected $fb_ip = '';
	protected $fb_user = '';
	protected $fb_pass = '';
	
	protected $client = null;
	protected $connected = null;
	
	//
	// Base functions
	//
	
	/**
	 * Constructor
	 * @param		$fb_ip		(string)	ip address or url for the FritzBox
	 * @param		$fb_pass	(string)	password for the FritzBox
 	 * @param 	$fb_user	(string)	username for the FritzBox (default = fritz-api)
	 */
	public function __construct( $fb_ip = null, $fb_pass = null, $fb_user = null ) {
		if( $fb_ip === null || $fb_pass == null ) return false;
		if( $fb_user === null ) $this->fb_user = "fritz-api";
		$this->fb_ip = $fb_ip;
		$this->fb_pass = $fb_pass;
		$this->setCon( true ); // TODO, validate connection to FritzBox
	}
	
	/**
	 * Client Helper function 
	 */
	public function client( $upnp = null, $param = null, $uri = null ) {
		if( $upnp === null || $upnp === "" ) return;
		if( $param === null || $param === "" ) return;
		if( $uri === null || $uri === "" ) return;
		$this->client = new SoapClient(
				null,
				array(
				     'location'   => "http://" . $this->fb_ip . ":49000/" . $upnp . "/control/" . $param,
				     'uri'        => $uri,
				     'noroot'     => True,
				     'login'      => $this->fb_user,
				     'password'   => $this->fb_pass,
				     'trace'      => True,
				     'exceptions' => false
				)
		);
	}
	
	/**
	 * Set connection state
	 * @return (boolean)
	 */
	public function setCon( $state = null ) {
		if( $state === null ) return false;
		$this->connected = $state;
	}
	
	/**
	 * Get connection state
	 * @return (boolean)
	 */
	public function getCon() {
		return $this->connected;
	}
	
	//
  // Website access functions
  //
  
  /**
	 * Get SID
	 * @return (string)
	 */
	public function getSID() {
	  $this->client( 'upnp', 'deviceconfig', 'urn:dslforum-org:service:DeviceConfig:1' );
	  return substr( $this->client->{"X_AVM-DE_CreateUrlSID"}(), 4 );
	}
	
	/**
	 * Get fritzbox url for webaccess
	 * @param		$urlpath 		(string)	set path to use (e.g.: "/fon_num/sperre.lua" )
	 * @param		$urlparams	(string)	set url params (e.g.: "&key=value&key1=value2" )
	 * @return	$url 				(string)	
	 */
	public function buildUrl( $urlpath = null, $urlparams = null ) {
		if( $urlpath === null ) $urlpath = "";
		if( $urlparams === null ) $urlparams = "";
		$url = "http://" . $this->fb_ip . "/" . $urlpath . "?sid=" . $this->getSID() . $urlparams;
		return $url;
	}	
	
	/**
	 * Send data to a form in fritzbox
	 * @param		$formurl	(string)	set form url (destination)
	 * @param 	$data 		(array)		all form elements in an assosiated array
	 */
	public function setFormData( $formurl = null, $data = null ) {
		if( $formurl === null ) return false;
		if( $data === null || ! is_array( $data ) ) return false;
		
		$formurl = $this->buildUrl( $formurl );
		
		$options = array(
			'http' => array(
		    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		    'method'  => 'POST',
		    'content' => http_build_query($data),
			),
		);

		$context  = stream_context_create($options);
		$result = file_get_contents($formurl, false, $context);
		
		if ( $result === false ) {
			return false;
		} else {
			return true;
		}
	}
	
	//
  // DSL functions
  //
  
  /**
	 * Get last Connection data
	 * @return (string)
	 */
  public function getConnectionInfo() {
		$this->client( 'upnp', 'time', 'urn:dslforum-org:service:Time:1' );
		$result = $this->client->GetInfo();
		$this->client( 'igdupnp', 'WANIPConn1', 'urn:schemas-upnp-org:service:WANIPConnection:1' );
		$status = $this->client->GetStatusInfo();
		return date('d.m.Y, H:i:s', strtotime($result['NewCurrentLocalTime']) - $status['NewUptime']);
	}
	
	/**
	 * Get last ReConnection data
	 * @return (string)
	 */
	public function getLastConnection() {
		$this->client( 'upnp', 'time', 'urn:dslforum-org:service:Time:1' );
		$result = $this->client->GetInfo();
		$this->client( 'igdupnp', 'WANIPConn1', 'urn:schemas-upnp-org:service:WANIPConnection:1' );
		$status = $this->client->GetStatusInfo();
		return date('d.m.Y, H:i', strtotime($result['NewCurrentLocalTime']) - $status['NewUptime']);
	}
	
	/**
	 * Get DOWN link speed
	 * @return (string)
	 */
	public function getDownSpeed() {
		$this->client( 'igdupnp', 'WANCommonIFC1', 'urn:schemas-upnp-org:service:WANCommonInterfaceConfig:1' );
		$result = $this->client->GetCommonLinkProperties();
		return round( $result['NewLayer1DownstreamMaxBitRate'] / 1000000, 1 );
	}
	
	/**
	 * Get UP link speed
	 * @return (string)
	 */
	public function getUpSpeed() {
		$this->client( 'igdupnp', 'WANCommonIFC1', 'urn:schemas-upnp-org:service:WANCommonInterfaceConfig:1' );
		$result = $this->client->GetCommonLinkProperties();
		return isset( $result ) ? round( $result['NewLayer1UpstreamMaxBitRate'] / 1000000, 1 ) : "0";
	}
			
	/**
	 * Get WAN Status
	 * @return (array)
	 */
	public function statusWAN() {
  	$this->client( 'igdupnp', 'WANIPConn1', 'urn:schemas-upnp-org:service:WANIPConnection:1' );
  	return $this->client->GetStatusInfo();
  }
	
	//
	// Telephone functions
	//
	
	/**
	 * Get Callerlist
	 * @return (array)
	 */
	public function getCallerlist() {
		$this->client( 'upnp', 'x_contact', 'urn:dslforum-org:service:X_AVM-DE_OnTel:1' );
		$callerurl = $this->client->GetCallList();
		return @simplexml_load_file( $callerurl );
	}
	
	/**
	 * Get Phonebook data
	 * @return (array)
	 */
	public function getPhonebook() {
		$this->client( 'upnp', 'x_contact', 'urn:dslforum-org:service:X_AVM-DE_OnTel:1' );
		$phonebookurl = $this->client->GetPhonebook(new SoapParam( 0,"NewPhonebookID" ) );
		return @simplexml_load_file( $phonebookurl['NewPhonebookURL'] );
	}
	
	//
  // Answering Mashine (TAM) functions
  //
  
	/**
	 * Set Answering Mashine state
	 * @param $state	(boolean|string) true|false|on|off
	 * @return (array) 
	 */
	public function setTam( $state = null ) {
	  if( $state === null ) return;
	  if( $state || $state == 'on' ) {
	  	$value = 1;
	  } else {
	  	$value = 0;
	  }
	  $this->client( 'upnp', 'x_tam', 'urn:dslforum-org:service:X_AVM-DE_TAM:1' );
	  $this->client->SetEnable(new SoapParam(0, 'NewIndex'),
    			                   new SoapParam( $value, 'NewEnable' )
    );
	}
	
	/**
	 * Get Answering Mashine data
	 * @return (array)
	 */
	public function getTamList() {
		$this->client( 'upnp', 'x_tam', 'urn:dslforum-org:service:X_AVM-DE_TAM:1' );
		$result = $this->client->GetMessageList( new SoapParam( 0, 'NewIndex') );
		return @simplexml_load_file($result);
	}
	
	//
  // Wifi functions
  //
	
	/**
	 * GetWifiInfo
	 * @param $ghz	null = 2.4GHz, not null = 5GHz
	 * 							enabled, status, channel, ssid, macfilter, bssid
	 * @return (string|array)
	 */
  public function getWifiInfo( $wifi = null, $value = null ) {
  if( $wifi === null ) $wifi = 1; // 1=2.4GHz, 2=5GHz, 3=gust2.4GHz, 4=guest5GHz 
  	else if( ! is_number( $wifi ) )  
  	$this->client( 'upnp', 'wlanconfig' . $wifi, 'urn:dslforum-org:service:WLANConfiguration:' . $wifi );
  	$data = $this->client->GetInfo();
  	if( $value === 'enabled' ) {
  		return $data['NewEnable'];
  	} else if( $value === 'status' ) {
  		return $data['NewStatus'];
  	} else if( $value === 'channel' ) {
  		return $data['NewChannel'];
  	} else if( $value === 'ssid' ) {
  		return $data['NewSSID'];
  	} else if( $value === 'macfilter' ) {
  		return $data['NewMACAddressControlEnabled'];
  	} if( $value === 'bssid' ) {
  		return $data['NewBSSID'];
  	} else {
  		return $data;
  	}
  }
  
  /**
	 * Wlan 2.4 and 5 GHz
	 * @param $state 	(boolean|string) true|false|on|off
	 * @param $ghz 		(int) 5
	 */
	public function setWifi( $wifi = null, $state = null ) {
		if( $state === null ) return;
		if( $wifi === null || $wifi > 4 || $wifi < 1 ) $wifi = 1; // 1=2.4GHz, 2=gust2.4GHz, 3=5GHz, 4=guest5GHz
		if( $state || $state == 'on' ) {
	  	$value = 1;
	  } else {
	  	$value = 0;
	  }
		$this->client( 'upnp', 'wlanconfig' . $wifi, 'urn:dslforum-org:service:WLANConfiguration:' . $wifi );
		$this->client->SetEnable( new SoapParam( $value, 'NewEnable' ) ); 
		if( $this->getWifiInfo( $wifi, 'enabled' ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * get active Wifi clients 
	 * @param	(int)		unset=all,  1=2.4GHz, 2=guest2.4GHz, 3=5GHz, 4=guest5GHz
	 *
	 */
	public function getActiveWifiClients( $wifi = null ) {
		if( $wifi !== null && $wifi >= 1 && $wifi <= 4 ) {
			// Get from special frequency
			$this->client( 'upnp', 'wlanconfig' . $wifi, 'urn:dslforum-org:service:WLANConfiguration:' . $wifi );
			$hosts_amount = (int) $this->client->GetTotalAssociations();
			$hosts = array();
			for( $i = 0; $i < $hosts_amount; $i++ ) {
				$hosts[] = $this->client->GetGenericAssociatedDeviceInfo(new SoapParam($i,'NewAssociatedDeviceIndex')); 
			}
		} else {
			// get from all wifi frequencies
			$hosts = array();
			for( $w = 1; $w < 4; $w++ ) {
				$this->client( 'upnp', 'wlanconfig' . $w, 'urn:dslforum-org:service:WLANConfiguration:' . $w );
				$hosts_amount = (int) $this->client->GetTotalAssociations();
				for( $i = 0; $i < $hosts_amount; $i++ ) {
					$hosts[] = $this->client->GetGenericAssociatedDeviceInfo(new SoapParam($i,'NewAssociatedDeviceIndex')); 
				}
				$hosts_amount = 0;
			}
		}
		return $hosts;
	}
  
	/**
	 * GetInfo
	 * @param $value	(string) model, serial, version, uptime, log
	 * @return (string)
	 */
	public function getInfo( $value = null, $lines = null ) {
		if( $lines === null ) $lines = 5;
	  $this->client( 'upnp', 'deviceinfo', 'urn:dslforum-org:service:DeviceInfo:1' );
  	$data = $this->client->GetInfo();
  	if( $value === 'model' ) {
  		return $data['NewModelName'];
  	} else if( $value === 'serial' ) {
  		return $data['NewSerialNumber'];
  	} else if( $value === 'version' ) {
  		return $data['NewSoftwareVersion'];
  	} else if( $value === 'uptime' ) {
  		return gmdate( "H:i:s", $data['NewUpTime'] );
  	} else if( $value === 'log' ) {
//  		$logentries = utf8_encode( $data['NewDeviceLog'] );
  		$logentries = $data['NewDeviceLog'];
  		$lograw = explode( "\n", $logentries );
  		$log = "";
  		foreach( $lograw as $key => $value ) {
  			if( ( $key +1 ) > $lines ) break;
  			$log .= $value . "\n";
  		}
  		return $log;
  	} else {
  		return $data;
  	}
	}
  
  //
  // UPNP
  //
  
  public function getUPNPInfo() {
		$this->client( 'upnp', 'X_UPnP', 'urn:dslforum-org:service:X_AVM-DE_UPnP:1' );
		return $this->client->GetInfo();
	}
  
  //
  // LAN
  //
  
  public function getLanClients() {
	  $this->client( 'upnp', 'hosts', 'urn:dslforum-org:service:Hosts:1' );
		$hosts = $this->client->GetHostNumberOfEntries();
		$erg = array();
		for ( $i = 0; $i < $hosts; $i++ ) {
			$erg[] = $this->client->GetGenericHostEntry( new SoapParam( $i,'NewIndex' ) );
		}
  	return $erg;
	}
  
  
  /**
	 * Reconnect DSL
	 */
  public function reconnectDSL() {
  	$this->client( 'igdupnp', 'WANIPConn1', 'urn:schemas-upnp-org:service:WANIPConnection:1' );
  	$this->client->ForceTermination();
  }
  
  /**
	 * Reboot FritzBox
	 */
  public function reboot() {
  	$this->client( 'upnp', 'deviceconfig', 'urn:dslforum-org:service:DeviceConfig:1' );
  	$this->client->Reboot(); 
  }
	
}

?>
