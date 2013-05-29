<?php

require_once('vendor/brycied00d/PHP-Transmission-Class/class/TransmissionRPC.class.php');
require_once('vendor/swiftmailer/swiftmailer/lib/swift_required.php');

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


		$body = "New video added to site.";
		foreach($urls as $url){
			$body .= '<br />'.$url;
		}

		// Create the Transport
		$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT)
		  ->setUsername(SMTP_LOGIN)
		  ->setPassword(SMTP_PASSWORD)
		  ;

		/*
		You could alternatively use a different transport such as Sendmail or Mail:

		// Sendmail
		$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

		// Mail
		$transport = Swift_MailTransport::newInstance();
		*/

		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		// Create a message
		$message = Swift_Message::newInstance('New videao added to site')
		  ->setFrom(array('john@doe.com' => 'Videos'))
		  ->setTo(array(NOTIFICATION_RECIPIENT))
		  ->setBody($body)
		  ;

		// Send the message
		$result = $mailer->send($message);
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