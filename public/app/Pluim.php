<?php

namespace GHO\Pluim;

/**
 * The main class.
 */
class Pluim {
	private $token;
	private $slack;
	private $api;

	public function __construct( $token ) {
		$this->token = $token;
		$this->api   = new API( $this->token );
		$this->slack = new Slack( $this->api );
	}

	// "Validator" for Slack
	public function validator() {
		$input = $this->api->receive();
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

			while ( array_key_exists( 'response_metadata', $response ) && $response['response_metadata']['next_cursor'] != false ) {
				$args['cursor'] = $response['response_metadata']['next_cursor'];

				$response = $this->slack->conversations_list( $args );
				if ( $response && $response['ok'] == true ) {
					foreach ( $response['channels'] as $value ) {
						$data[] = $value;
					}

					$args['cursor'] = $response['response_metadata']['next_cursor'];
				}
			}
		}

		return $data;
	}

	public function delete_msg($channel, $ts){
		$data = [
			'channel' => $channel,
			'ts'      => $ts,
		];

		return $this->slack->chat_delete( $data );
	}

	public function list_connected_users() {
		$data = [];

		$args = [
			'token'            => $this->token,
			'exclude_archived' => true,
			'limit'            => 1000,
			'types'            => 'im',
			'cursor'           => '',
		];

		$response = $this->slack->conversations_list( $args );

		if ( $response && $response['ok'] == true ) {
			foreach ( $response['channels'] as $value ) {
				$data[] = $value['user'];
			}

			while ( array_key_exists( 'response_metadata', $response ) && $response['response_metadata']['next_cursor'] != false ) {
				$args['cursor'] = $response['response_metadata']['next_cursor'];

				$response = $this->slack->conversations_list( $args );

				if ( $response && $response['ok'] == true ) {
					foreach ( $response['channels'] as $value ) {
						$data[] = $value['user'];
					}

					$args['cursor'] = $response['response_metadata']['next_cursor'];
				}
			}
		}

		return $data;
	}

	public function create_msg( $balloon_txt, $blocks, $channel, $as_user = false ) {
		$blocks = json_decode( $blocks, true );

		$data = [
			'channel' => $channel,
			'text'    => $balloon_txt,
			'blocks'  => $blocks,
		];

		if( $as_user !== false ){
			$data['as_user'] = $as_user;
		}

		return $this->slack->chat_postmessage( $data );
	}

	public function create_ephemeral($channel, $blocks, $user, $as_user = true, $text = ' ', $attachments = []) {
		$blocks = json_decode( $blocks, true );

		$data = [
			'attachments' => $attachments,
			'channel' => $channel,
			'text'    => $text,
			'user' => $user,
			'as_user' => $as_user,
			'blocks'  => $blocks,
		];

		return $this->slack->chat_postephemeral( $data );
	}

	public function update_ephemeral($response_url,  $blocks, $text = 'text'){
		$data = [
			'response_type' => 'ephemeral',
			'text' => $text,
			'replace_original' => true,
            'delete_original' =>  true,
			'blocks' => $blocks,
		];

		return $this->api->send($response_url, $data);
	}

	public function delete_ephemeral( $response_url ) {
		return $this->update_ephemeral( $response_url, 'verwijderd', [ [ 'type' => 'divider' ] ] );
	}

	public function create_home( $blocks, $user_id ) {
		$blocks = json_decode( $blocks, true );

		$data = [
			'user_id' => $user_id,
			'view'    => [
				'type'   => 'home',
				'blocks' => $blocks,
			],
		];

		return $this->slack->views_publish( $data );
	}

	public function list_users() {
		$data = [];

		$args = [
			'token' => $this->token,
		];

		$response = $this->slack->users_list( $args );

		if ( $response && $response['ok'] == true ) {
			foreach ( $response['members'] as $value ) {
				if ( $value['deleted'] !== true && $value['is_bot'] !== true ) {
					$data[] = [
						'user_id' => $value['id'],
						'name'    => $value['profile']['real_name'],
						'email'   => $value['profile']['email'],
					];
				}
			}
		}

		return $data;
	}

	public function format_message( $data, $is_rnd = true) {
		if( $is_rnd === true ) {
			$img = $this->get_random_image_url();
		} else {
			$img = $this->get_image_by_id( $is_rnd );
		}

		$alt_data = $this->format_alt_text( $img );

		$search = array(
			'<IMG_ID>',
			'<IMAGE_URL>',
			'<ALT_TEXT>'
		);

		$replace = array(
			$alt_data['id'],
			$img,
			$alt_data['value'],
		);

		return str_replace( $search, $replace, $data );
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

		return $this->img_path_to_url($img);
	}

	public function get_images(){
		$dir       = 'assets/img';
		$img_array = [];
		$dir_arr   = scandir( $dir );
		$arr_files = array_diff( $dir_arr, [ '.', '..' ] );

		foreach ( $arr_files as $file ) {
			$file_path = $dir . '/' . $file;
			$ext       = pathinfo( $file_path, PATHINFO_EXTENSION );

			if ( strtolower( $ext ) === 'gif' ) {
				array_push( $img_array, $file );
			}
		}

		return $img_array;
	}

	public function get_random_image_url( $previous = false ) {
		$img_array = $this->get_images();

		$key = array_rand( $img_array );

		if( $previous !== false ) {
			while( $rand === $previous ) {
				$key = array_rand( $img_array );
			}
		}

		return $this->img_path_to_url( $img_array[ $rand ] );
	}

	public function img_path_to_url($img_path){
		return $_ENV['DOMAIN'] . '/assets/img/' . $img_path;
	}

	public function format_alt_text( $data ){
		$data     = explode('/', $data);
		$data     = end($data);
		$last_dot = strrpos( $data, '.' );
		$data     = substr($data, 0, $last_dot-1); //remove ext

		$data = explode('_', $data); //remove id
		array_shift($data);
		$data = implode(' ', $data);
		$data = ucwords($data);

		return [
			'id' => $id,
			'value' => $data,
		];
	}

	public function decode_value($value){
		list( $value, $id ) = explode( '_', $value );

		return [
			'value' => $value,
			'id' => $id,
		];
	}
}
