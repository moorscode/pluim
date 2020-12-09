<?php

/**
 * Class slack. For communication with Slack
 */

class pluim_slack{
	private $api;

	public function __construct(){
		$this->api = new pluim_api();
	}

	public function conversations_list($args){
		$response  = $this->api->get('https://slack.com/api/conversations.list', $args);

		return $response;
	}

	public function chat_postmessage($data){
		$response = $this->api->send('https://slack.com/api/chat.postMessage', $data);

		return $response;
	}

	public function chat_postephemeral($data){
		$response = $this->api->send('https://slack.com/api/chat.postEphemeral', $data);

		return $response;
	}

	public function views_publish($data){
		$response = $this->api->send('https://slack.com/api/views.publish', $data);

		return $response;
	}

	public function users_list($args){
		$response  = $this->api->get('https://slack.com/api/users.list', $args);

		return $response;
	}

	public function chat_delete($data){
		$response = $this->api->send('https://slack.com/api/chat.delete', $data);

		return $response;
	}
}