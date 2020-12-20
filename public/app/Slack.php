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

	public function conversations_list( $data ) {
		return $this->api->get( 'https://slack.com/api/conversations.list', $data );
	}

	public function chat_postmessage( $data ) {
		return $this->api->send( 'https://slack.com/api/chat.postMessage', $data );
	}

	public function chat_postephemeral( $data ) {
		return $this->api->send( 'https://slack.com/api/chat.postEphemeral', $data );
	}

	public function views_publish( $data ) {
		return $this->api->send( 'https://slack.com/api/views.publish', $data );
	}

	public function users_list( $data ) {
		return $this->api->get( 'https://slack.com/api/users.list', $data );
	}

	public function chat_delete( $data ) {
		return $this->api->send( 'https://slack.com/api/chat.delete', $data );
	}
}