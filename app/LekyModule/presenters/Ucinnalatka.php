<?php


class UcinneLatky {
    const URL = 'https://prehledy.sukl.cz/prehledy/v1/dlp/';

	function getLecivaLatka($url) {
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);  
		
		$json = file_get_contents($url, false, stream_context_create($arrContextOptions));
		$data = json_decode($json, true);
		$ucinneLatky = array();
		if(isset($data) && isset($data['leciveLatky'])){
			foreach ($data['leciveLatky'] as $latka) {
				$ucinneLatky[] = $latka['nazev'];
			}
		}
		return $ucinneLatky;
	}

	public function getInfo($ID_LEKU = null) {
		$url = self::URL . $ID_LEKU;
		$ucinneLatky = $this->getLecivaLatka($url);
		if ($ucinneLatky) {
			return $ucinneLatky;
		} else {
			return array();
		}
	}
}
