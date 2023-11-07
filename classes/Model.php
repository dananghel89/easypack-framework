<?php
/**
 * Epack Model Class
 */

namespace Epack;

class Model {

	// Model properties.
	protected static $handler;
	protected static $prefix;

	// Model constructor.
	public function __construct() {
		$database = new \Epack\Database();

		$this->setHandler(
			$database::getInstance()
		);
	}

	// Model set handler method.
	protected function setHandler($db) {
		if ( empty($db->conn) ) {
			return;
		}

		self::$handler = \Delight\Db\PdoDatabase::fromPdo($db->conn, true);
		self::$prefix  = $db->prefix;
	}

	// Model pdo method.
	static function pdo() {
		return self::$handler;
	}

	// Model get table method.
	static function getTable($name = '') {
		if ( !empty(static::$table) ) {
			$name = static::$table;
		}

		return self::$prefix . $name;
	}

	// Model get fields method.
	static function getFields($post = [], $all = true) {
		if ( empty(static::$fields) ) {
			return;
		}

		$rows = [];

		foreach ( static::$fields as $key => $value ) {
			if ( strpos($key, '!') === 0 ) {
				$key = substr($key, 1);

				if ( !$all ) {
					continue;
				}
			}

			if ( $value === 'NOW' ) {
				$value = get_current_date();
			}

			$rows[$key] = !empty($post[$key]) ? $post[$key] : $value;
		}

		return $rows;
	}

	// Model select method.
	static function select($name = '') {
		$table = self::getTable($name);

		$sql = "SELECT * FROM $table";

		return self::pdo()->select($sql);
	}

	// Model select row method.
	static function selectRow($id, $name = '') {
		$table = self::getTable($name);

		$sql = "SELECT * FROM $table WHERE id = ?";

		$values = [
			$id,
		];

		return self::pdo()->selectRow($sql, $values);
	}

	// Model insert method.
	static function insert($post = [], $name = '') {
		return self::pdo()->insert(
			self::getTable($name),
			self::getFields($post)
		);
	}

	// Model update method.
	static function update($id, $post = [], $name = '') {
		$where = [
			'id' => $id,
		];

		return self::pdo()->update(
			self::getTable($name),
			self::getFields($post, false),
			$where
		);
	}

	// Model delete method.
	static function delete($id, $name = '') {
		$where = [
			'id' => $id,
		];

		return self::pdo()->delete(
			self::getTable($name), $where
		);
	}

}
