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
			$files = $this->getFilePath($torrent->id);
			$this->closeDownload($torrent->id);
			$url = $this->addToSite($files);
			$this->notifyAddedToSite($files,$url);
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

	private function addToSite($files){
		$urls = array();
		foreach($files as $file){
			$command = "php ".SITE_ROOT.DIRECTORY_SEPARATOR."app/console shv:video:add --remove $file";
			exec($command, $output);
			$id = $this->parseId($output);
			$url = SITE_URL.'video/'.$id;
		}

		return $urls;
	}

	private function closeDownload($id){
		$this->rpc->remove($id);
	}

	private function notifyAddedToSite($file,$urls){
		//TODO
	}

	private function getFilePaths($id){
		//TODO
	}

	private function parseId($output){
		foreach ($output as $line) {
			if(strpos('n°', $line) !== false){
				$line = str_replace("Video n° ", '', $line);
				$line = str_replace(" has been added", '', $line);
				return $line;
			}
		}
	}

}