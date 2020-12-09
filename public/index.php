<?php

require_once( 'pluim.class.php' );
$instance = pluim::instance();

$receive = $_POST;

if($receive && array_key_exists('command', $receive) && $receive['command'] === '/pluim') {
	$block = file_get_contents( 'dialog.json' );
	$msg   = $instance->helpers->format_message( $block, true );

	$data = $instance->helpers->create_ephemeral( $receive['channel_id'], $msg, $receive['user_id'] );
}elseif($receive && array_key_exists('payload', $receive)){
	$payload = json_decode($receive['payload'], true);

	$value = $payload['actions'][0]['value'];
	$response_url = $payload['response_url'];

	$decode = $instance->helpers->decode_value($value);

	switch($decode['value']){
		case 'send':
			$send_block =   json_encode(array(
							array(
								'type' => 'image',
								'image_url' => '<IMAGE_URL>',
					            'alt_text' => '<ALT_TEXT>',
							)
						));
			$blocks = $instance->helpers->format_message($send_block, $decode['id']);
			$channel = $payload['channel']['id'];


			$instance->helpers->delete_ephemeral($response_url);
            $instance->helpers->create_msg($_ENV['BALLOON_TXT'], $blocks, $channel);

			break;
		case 'shuffle':
			$block = file_get_contents( 'dialog.json' );
			$msg   = $instance->helpers->format_message( $block, true );

			$instance->helpers->update_ephemeral($response_url, $msg);
			break;
		case 'cancel':

			$instance->helpers->delete_ephemeral($response_url);
			break;
	};
}