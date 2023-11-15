<?php
/*
 * Copyright (c) 2023 Sura
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace Mozg\classes;

use Sura\Database\Database;

/**
 *
 */
class DB
{
	/**
	 * @var Database|null
	 */
	private static ?Database $database = null;

	/**
	 *
	 */
	protected function __construct()
	{
	}

	/**
	 * @return void
	 */
	protected function __clone()
	{
	}

	/**
	 * @return Database|null
	 */
	public static function getDB(): null|Database
	{
		if (self::$database === null) {
			if (!\is_file(ENGINE_DIR . '/data/db_config.php')) {
				echo 'err';//todo upd
				exit();
			}
			$db_config = require ENGINE_DIR . '/data/db_config.php';
			$dsn = 'mysql:host=' . $db_config['host'] . ';dbname=' . $db_config['name'];
			self::$database = new Database($dsn, $db_config['user'], $db_config['pass']);
		}
		return self::$database;
	}
}