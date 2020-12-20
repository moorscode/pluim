<?php

namespace GHO\Pluim;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * Class API. For communication with different APIs.
 */
class API {
	private $token;

	public function __construct( $token ) {
		$this->token = $token;
	}

	public function get($uri, $data){
		try{
			$client = new Client();
			$result = $client->get( $uri, [ 'query' => $data ] );
			$body = (string) $result->getBody();
			$body = json_decode( $body, true );

			if( ! empty( $body ) ) {
				return $body;
			}
		}
		catch( TransferException $e ) {
			$this->log( $e );
		}

		return false;
	}

	public function send($uri, $data){
		//Add extra headers for Slack.
		try{
			$client = new Client(
				[
					'headers' => [
						'Content-type' => 'application/json; charset=utf-8',
						'Authorization' => 'Bearer ' . $this->token,
					]
				]
			);

			$result = $client->post( $uri, [ 'json' => $data ] );

			$body = (string) $result->getBody();
			$body = json_decode( $body, true );

			if( ! empty( $body ) ) {
				return $body;
			}
		}
		catch( TransferException $e ){
			$this->log( $e );
		}

		return false;
	}

	/*
	 * Receive POST request
	 */
	public function receive() {
		return file_get_contents( 'php://input' );
	}

	public function log( $error ) {
		$file = $_ENV['LOG_FILE'];

		$handle = fopen( $file, 'a' );
		frwite( $handle, json_encode( $error ) );
		fclose( $handle );
	}
}