<?php

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

require_once('app/api.php');
require_once('app/helpers.php');
require_once('app/slack.php');

setlocale(LC_ALL, 'nl_NL');

pluim::instance();

/**
 * Class pluim
 */
class pluim
{
	/**
	 * Singleton holder
	 */
	private static $instance;

	/**
	 * Get the singleton
	 *
	 * @return pluim
	 */
	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	private function __construct()
	{
		$this->api = new pluim_api();
		$this->helpers = new pluim_helpers();
		$this->slack = new pluim_slack();
	}
}