<?php

namespace GHO;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable( '../' );
$dotenv->load();

setlocale( LC_ALL, 'nl_NL' );

$pluim = new Pluim\Pluim( $_ENV['SLACK_TOKEN'] );

$receive = $_POST;
if ( ! $receive ) {
	exit;
}

if ( array_key_exists( 'command', $receive ) && $receive['command'] === '/pluim' ) {
	$block = file_get_contents( 'dialog.json' );
	$msg   = $pluim->format_message( $block, true );
	$data  = $pluim->create_ephemeral( $receive['channel_id'], $msg, $receive['user_id'] );
} elseif ( array_key_exists( 'payload', $receive ) ) {
	$payload = json_decode( $receive['payload'], true );

	$value        = $payload['actions'][0]['value'];
	$response_url = $payload['response_url'];

	$decode = $pluim->decode_value( $value );

	switch( $decode[ 'value' ] ){
		case 'send':
			$send_block = json_encode(
				[
					[
						'type' => 'image',
						'image_url' => '<IMAGE_URL>',
						'alt_text' => '<ALT_TEXT>',
					],
					[
						'type' => 'context',
						'elements' => [
							[
								'type' => 'mrkdwn',
								'text' => 'Gestuurd via `/pluim`, een werkinnovatie van *<https://ghocommunicatie.nl|GH+O communicatie>*'
							]
						],
					]
				]
			);

			$blocks  = $pluim->format_message( $send_block, $decode['id'] );
			$channel = $payload['channel']['id'];

			$pluim->delete_ephemeral( $response_url );
            $pluim->create_msg( $_ENV['BALLOON_TXT'], $blocks, $channel );

			break;
		case 'shuffle':
			$block = file_get_contents( 'dialog.json' );
			$msg   = $pluim->format_message( $block, true );

			$pluim->update_ephemeral( $response_url, $msg );
			break;
		case 'cancel':
			$pluim->delete_ephemeral( $response_url );
			break;
	}
}
