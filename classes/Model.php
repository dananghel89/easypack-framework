<?php
/**
 * Epack Model Class
 */

namespace Epack;

class Model {

	// Model properties.
	protected static $pdo;
	protected static $prefix;

	// Model constructor.
	public function __construct() {
		$database = \Epack\Database::getInstance();

		self::$pdo = $database->pdo;
		self::$prefix = $database->prefix;
	}

	// Model db method.
	static function db() {
		return self::$pdo;
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

		return self::db()->select($sql);
	}

	// Model select row method.
	static function selectRow($id, $name = '') {
		$table = self::getTable($name);

		$sql = "SELECT * FROM $table WHERE id = ?";

		$values = [
			$id,
		];

		return self::db()->selectRow($sql, $values);
	}

	// Model insert method.
	static function insert($post = [], $name = '') {
		return self::db()->insert(
			self::getTable($name),
			self::getFields($post)
		);
	}

	// Model update method.
	static function update($id, $post = [], $name = '') {
		$where = [
			'id' => $id,
		];

		return self::db()->update(
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

		return self::db()->delete(
			self::getTable($name), $where
		);
	}

}
