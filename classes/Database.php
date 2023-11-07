<?php
/**
 * Epack Database Class
 */

namespace Epack;

class Database {

	// Database properties.
	protected static $config;
	protected static $conn;

	// Database constructor.
	public function __construct($name = 'mysql') {
		$vars = \Epack\Config::getVars();

		if ( isset($vars->{$name}) ) {
			self::$config = $vars->{$name};
			$this->setConn();
		}
	}

	// Database set conn method.
	protected function setConn() {
		extract(self::$config, EXTR_SKIP);

		$options = [
			\PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '{$timezone}'",
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		];

		if ( ! ( empty($ssl_key) || empty($ssl_cert) || empty($ssl_ca) ) ) {
			$options += [
				\PDO::MYSQL_ATTR_SSL_KEY  => $ssl_key,
				\PDO::MYSQL_ATTR_SSL_CERT => $ssl_cert,
				\PDO::MYSQL_ATTR_SSL_CA   => $ssl_ca,
			];
		}

		if ( !empty($database) ) {
			try {
				$dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
				self::$conn = new \PDO($dsn, $username, $password, $options);

			} catch ( \PDOException $e ) {
				throw new \Exception("Connection error: " . $e->getMessage());
			}
		}
	}

	// Database get instance method.
	static function getInstance() {
		return (object) [
			'conn'   => self::$conn,
			'prefix' => self::$config['prefix'],
		];
	}

}
