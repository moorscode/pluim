<?php

namespace GHO\Pluim;

/**
 * Class slack. For communication with Slack
 */
class Slack {
	private $api;

	public function __construct( $api ){
		$this->api = $api;
	}

	public function get_conversations_list($data ) {
		return $this->api->get( 'https://slack.com/api/conversations.list', $data );
	}

	public function send_chat_message($data ) {
		return $this->api->send( 'https://slack.com/api/chat.postMessage', $data );
	}

	public function send_chat_ephemeral($data ) {
		return $this->api->send( 'https://slack.com/api/chat.postEphemeral', $data );
	}

	public function delete_chat($data ) {
		return $this->api->send( 'https://slack.com/api/chat.delete', $data );
	}
}
