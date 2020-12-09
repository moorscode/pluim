<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * Class API. For communication with different APIs.
 */

class pluim_api{
	private $token;
	private $client;

	public function __construct() {
		$this->token = $_ENV['SLACK_TOKEN'];
		$this->client = new Client();
	}

	public function get($uri, $data){
		$instance = $this->client;

		try{
			$result = $instance->get($uri, ['query' => $data]);
			$body = (string) $result->getBody();
			$body = json_decode($body, true);

			if(!empty($body)){
				return $body;
			}else{
				return false;
			}
		}
		catch(TransferException $e){
			$this->log($e);
		}

		return null;
	}

	public function send($uri, $data){
		//Add extra headers for Slack.
		$instance = new Client(['headers' => array(
			'Content-type' => 'application/json; charset=utf-8',
			'Authorization' => 'Bearer ' . $this->token,
		)]);

		try{
			$result = $instance->post($uri, ['json' => $data]);
			$body = (string) $result->getBody();
			$body = json_decode($body, true);

			if(!empty($body)){
				return $body;
			}else{
				return false;
			}
		}
		catch(TransferException $e){
			$this->log($e);
		}

		return null;
	}

	/*
	 * Receive POST request
	 */
	public function receive(){
		$input = file_get_contents('php://input');

		return $input;
	}

	public function log($e){
		$file = $_ENV['LOG_FILE'];
		$current = file_get_contents($file);
		$current .= serialize($e) . "\n";
		file_put_contents($file, $current);
	}
}