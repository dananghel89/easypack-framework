<?php
/**
 * Epack Database Class
 */

namespace Epack;

class Database {

	// Database properties.
	protected static $config;
	protected static $source;
	protected static $pdo;

	// Database constructor.
	public function __construct() {
		$vars = \Epack\Config::getVars();

		if ( isset($vars->db) ) {
			self::$config = $vars->db;
			
			$this->setSource();
			$this->setPdo();
		}
	}

	// Database set source method.
	protected function setSource() {
		extract(self::$config, EXTR_SKIP);

		self::$source = new \Delight\Db\PdoDataSource($drive);

		self::$source->setHostname($hostname)
			->setPort($port)
			->setDatabaseName($name)
			->setCharset($charset)
			->setUsername($username)
			->setPassword($password);
	}

	// Database set pdo method.
	protected function setPdo() {
		if ( empty(self::$source) ) {
			return;
		}

		self::$pdo = \Delight\Db\PdoDatabase::fromDataSource(self::$source);
	}

	// Database get instance method.
	static function getInstance() {
		return (object) [
			'source' => self::$source,
			'pdo' => self::$pdo,
			'prefix' => self::$config['prefix'],
		];
	}

}
