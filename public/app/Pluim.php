<?php

namespace GHO\Pluim;

/**
 * The main class.
 */
class Pluim {
	private $slack;
	private $api;

	public function __construct( $token, $logfile ) {
		$this->api = new API( $token, $logfile );
		$this->slack = new Slack( $this->api );
	}

	public function create_msg( $balloon_txt, $blocks, $channel, $as_user = false ) {
		$data = [
			'channel' => $channel,
			'text'    => $balloon_txt,
			'blocks'  => $blocks,
		];

		if ( $as_user !== false ) {
			$data['as_user'] = $as_user;
		}

		return $this->slack->send_chat_message( $data );
	}

	public function create_ephemeral( $channel, $blocks, $user, $as_user = true, $text = ' ', $attachments = [] ) {
		$data = [
			'attachments' => $attachments,
			'channel'     => $channel,
			'text'        => $text,
			'user'        => $user,
			'as_user'     => $as_user,
			'blocks'      => $blocks,
		];

		return $this->slack->send_chat_ephemeral( $data );
	}

	public function update_ephemeral( $response_url, $blocks, $text = 'text' ) {
		$data = [
			'response_type'    => 'ephemeral',
			'text'             => $text,
			'replace_original' => true,
			'delete_original'  => true,
			'blocks'           => $blocks,
		];

		return $this->api->send( $response_url, $data );
	}

	public function delete_ephemeral( $response_url ) {
		return $this->update_ephemeral( $response_url, 'verwijderd', [ [ 'type' => 'divider' ] ] );
	}

	public function format_message( $data, $is_rnd = true ) {
		if ( $is_rnd === true ) {
			$img = $this->get_random_image_url();
		} else {
			$img = $this->get_image_by_id( $is_rnd );
		}

		$alt_data = $this->format_alt_text( $img );

		$data[0]['image_url'] = $img;
		$data[0]['alt_text'] = $alt_data['value'];

		foreach ( $data[1]['elements'] as $key => $element ) {
			$data[1]['elements'][ $key ]['value'] = str_replace( '<IMG_ID>', $alt_data['id'], $data[1]['elements'][ $key ]['value'] );
		}

		return $data;
	}

	public function get_image_by_id( $id ) {
		foreach ( $this->get_images() as $item ) {
			if ( strpos( $item, $id . '_' ) === 0 ) {
				return $$this->img_path_to_url( $item );
			}
		}

		return '';
	}

	public function get_images() {
		$dir = 'assets/img'; // @todo this could be an ENV variable.
		$img_array = [];

		$dir_arr = scandir( $dir );
		$arr_files = array_diff( $dir_arr, [ '.', '..' ] );

		foreach ( $arr_files as $file ) {
			$file_path = $dir . '/' . $file;
			$ext = pathinfo( $file_path, PATHINFO_EXTENSION );

			if ( strtolower( $ext ) === 'gif' ) {
				$img_array[] = $file;
			}
		}

		return $img_array;
	}

	public function get_random_image_url( $previous = false ) {
		$img_array = $this->get_images();

		$key = array_rand( $img_array );
		if ( $previous !== false ) {
			while ( $key === $previous ) {
				$key = array_rand( $img_array );
			}
		}

		return $this->img_path_to_url( $img_array[ $key ] );
	}

	public function img_path_to_url( $img_path ) {
		return $_ENV['DOMAIN'] . '/assets/img/' . $img_path;
	}

	public function format_alt_text( $data ) {
		$data = explode( '/', $data );
		$data = end( $data );
		$last_dot = strrpos( $data, '.' );
		$data = substr( $data, 0, $last_dot - 1 ); //remove ext

		$data = explode( '_', $data ); //remove id
		$id = array_shift( $data );
		$data = implode( ' ', $data );
		$data = ucwords( $data );

		return [
			'id'    => $id,
			'value' => $data,
		];
	}

	public function decode_value( $value ) {
		list( $value, $id ) = explode( '_', $value );

		return [
			'value' => $value,
			'id'    => $id,
		];
	}
}
