<?php

require_once('vendor/brycied00d/PHP-Transmission-Class/class/TransmissionRPC.class.php');

class Autotransmission{


	public function __construct(){
		//Retrieve Transmission connection
		$this->rpc = new TransmissionRPC(TRANSMISSION_URL,'transmission','transmission');
	}

	public function closeComplete(){
		$completes = $this->getCompleteDownloads();

		foreach ($completes as $torrent) {
			$file = $this->getFilePath($torrent->id);
			$this->closeDownload($torrent->id);
			$url = $this->addToSite($file);
			$this->notifyAddedToSite($file,$url);
		}
	}

	private function listDownloads(){
		return $this->rpc->get();
	}

	private function getCompleteDownloads(){
		$downloads = $this->listDownloads();
		$torrents = $downloads->arguments->torrents;

		$return = array();

		foreach($torrents as $torrent){
			if($torrent->status == TransmissionRPC::RPC_LT_14_TR_STATUS_SEED){
				$return[] = $torrent;
			}
		}
		return $return;
	}

	private function addToSite($file){

	}

	private function closeDownload($id){
		$this->rpc->remove($id);
	}

	private function notifyAddedToSite($file,$url){

	}

	private function getFilePath($id){

	}

}