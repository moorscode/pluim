<?php

/**
 * Class helpers. Common functionality abstracted.
 */

class pluim_helpers {
	private $token;
	private $slack;
	private $api;

	public function __construct() {
		$this->token = $_ENV['SLACK_TOKEN'];
		$this->slack = new pluim_slack();
		$this->api   = new pluim_api();
	}

	//"Validator" for Slack
	public function validator() {
		$input = file_get_contents( 'php://input' );
		if ( $input ) {
			$decode = json_decode( $input, true );
			if ( isset( $decode ) && array_key_exists( 'challenge', $decode ) ) {
				var_dump( $input );
			}
		}
	}

	public function list_channels() {
		$data = array();

		$args = array(
			'token'            => $this->token,
			'exclude_archived' => true,
			'limit'            => 1000,
			'types'            => 'im',
			'cursor'           => '',
		);

		$response = $this->slack->conversations_list( $args );

		if ( $response && $response['ok'] == true ) {
			foreach ( $response['channels'] as $value ) {
				$data[] = $value;
			}

			unset( $value );

			while ( array_key_exists( 'response_metadata', $response ) && $response['response_metadata']['next_cursor'] != false ) {
				$args['cursor'] = $response['response_metadata']['next_cursor'];

				$response = $this->slack->conversations_list( $args );

				if ( $response && $response['ok'] == true ) {
					foreach ( $response['channels'] as $value ) {
						$data[] = $value;
					}

					unset( $value );

					$args['cursor'] = $response['response_metadata']['next_cursor'];
				}
			}
		}

		return $data;
	}

	public function delete_msg($channel, $ts){
		$data = array(
			'channel' => $channel,
			'ts'    => $ts,
		);

		if ( $data ) {
			$msg = $this->slack->chat_delete( $data );

			return $msg;
		} else {
			$this->api->log( 'Kon geen data maken, dus stuk.' );
		}

		return null;
	}


	public function list_connected_users() {
		$data = array();

		$args = array(
			'token'            => $this->token,
			'exclude_archived' => true,
			'limit'            => 1000,
			'types'            => 'im',
			'cursor'           => '',
		);

		$response = $this->slack->conversations_list( $args );

		if ( $response && $response['ok'] == true ) {
			foreach ( $response['channels'] as $value ) {
				$data[] = $value['user'];
			}

			unset( $value );

			while ( array_key_exists( 'response_metadata', $response ) && $response['response_metadata']['next_cursor'] != false ) {
				$args['cursor'] = $response['response_metadata']['next_cursor'];

				$response = $this->slack->conversations_list( $args );

				if ( $response && $response['ok'] == true ) {
					foreach ( $response['channels'] as $value ) {
						$data[] = $value['user'];
					}

					unset( $value );

					$args['cursor'] = $response['response_metadata']['next_cursor'];
				}
			}
		}

		return $data;
	}

	public function create_msg( $balloon_txt, $blocks, $channel, $as_user = false ) {
		$blocks = json_decode( $blocks, true );

		$data = array(
			'channel' => $channel,
			'text'    => $balloon_txt,
			'blocks'  => $blocks,
		);

		if($as_user !== false){
			$data['as_user'] = $as_user;
		}

		if ( $data ) {
			$msg = $this->slack->chat_postmessage( $data );

			return $msg;
		} else {
			$this->api->log( 'Kon geen data maken, dus stuk.' );
		}

		return null;
	}

	public function create_ephemeral($channel, $blocks, $user, $as_user = true, $text = ' ', $attachments = []) {
		$blocks = json_decode( $blocks, true );

		$data = array(
			'attachments' => $attachments,
			'channel' => $channel,
			'text'    => $text,
			'user' => $user,
			'as_user' => $as_user,
			'blocks'  => $blocks,
		);

		if ( $data ) {
			$msg = $this->slack->chat_postephemeral( $data );

			return $msg;
		} else {
			$this->api->log( 'Kon geen data maken, dus stuk.' );
		}

		return null;
	}

	public function update_ephemeral($response_url,  $blocks, $text = 'text'){
		$data = array(
			'response_type' => 'ephemeral',
			'text' => $text,
			'replace_original' => true,
            'delete_original' =>  true,
			'blocks' => $blocks,
		);

		$response = $this->api->send($response_url, $data);
		return $response;
	}

	public function delete_ephemeral($response_url){
		$response = $this->update_ephemeral($response_url, 'verwijderd', array(array('type' => 'divider')));
		return $response;
	}


	public function create_home( $blocks, $user_id ) {
		$blocks = json_decode( $blocks, true );

		$data = array(
			'user_id' => $user_id,
			'view'    => array(
				'type'   => 'home',
				'blocks' => $blocks,
			),
		);

		if ( $data ) {
			$msg = $this->slack->views_publish( $data );

			return $msg;
		} else {
			$this->api->log( 'Kon geen data maken, dus stuk.' );
		}

		return null;
	}

	public function list_users() {
		$data = array();

		$args = array(
			'token' => $this->token,
		);

		$response = $this->slack->users_list( $args );

		if ( $response && $response['ok'] == true ) {
			foreach ( $response['members'] as $value ) {
				if ( $value['deleted'] !== true && $value['is_bot'] !== true ) {
					$data[] = array(
						'user_id' => $value['id'],
						'name'    => $value['profile']['real_name'],
						'email'   => $value['profile']['email'],
					);
				}
			}
		}

		return $data;
	}

	public function format_message( $data , $is_rnd = true) {
		if($is_rnd === true){
			$img = $this->get_random_image_url();
		}else{
			$img = $this->get_image_by_id($is_rnd);
		}

		$alt_data = $this->format_alt_text($img);

		$search = array(
			'<IMG_ID>',
			'<IMAGE_URL>',
			'<ALT_TEXT>'
		);

		$replace = array(
			$alt_data['id'],
			$img,
//			time()
			$alt_data['value'],
		);

		$text = str_replace( $search, $replace, $data );

//		$text = str_replace('http://localhost', 'https://285e1428f869.ngrok.io', $text); //@todo dev

		return $text;
	}

	public function get_image_by_id($id){
		$img_array = $this->get_images();
		$img = '';
		foreach($img_array as $item){
			if(strpos($item, $id . '_') === 0){
				$img = $item;
				break;
			}
		}

		$url = $this->img_path_to_url($img);

		return $url;
	}

	public function get_images(){
		$dir = 'assets/img';
		$img_array = [];
		$dir_arr = scandir($dir);
		$arr_files = array_diff($dir_arr, array('.','..') );

		foreach ($arr_files as $file) {
			$file_path = $dir."/".$file;
			$ext = pathinfo($file_path, PATHINFO_EXTENSION);
			if ($ext=="gif" || $ext=="GIF") {
				array_push($img_array, $file);
			}

		}

		return $img_array;
	}

	public function get_random_image_url($previous = false){
		$img_array = $this->get_images();

		$count_img_index = count($img_array) - 1;
		if($previous !== false){
			do {
				$rand = rand(0, $count_img_index);

			}while($rand === $previous);
		}else{
			$rand = rand(0, $count_img_index);
		}

		$img_path = $img_array[$rand]; //exclude

		$url = $this->img_path_to_url($img_path);

		return $url;
	}

	public function img_path_to_url($img_path){
		$url = $_ENV['DOMAIN'] . '/assets/img/' . $img_path;
		return $url;
	}


	public function format_alt_text($data){
		$data = explode('/', $data);
		$data = end($data);
		$data = substr($data, 0, -4); //remove ext

		$data = explode('_', $data); //remove id
		$id = array_shift($data);
		$data = implode(' ', $data);
		$data = ucwords($data);

		return array(
			'id' => $id,
			'value' => $data,
			);
	}

	public function decode_value($value){
		$value = explode('_', $value);

		return array(
			'value' => $value[1],
			'id' => $value[2],
			);
	}


}