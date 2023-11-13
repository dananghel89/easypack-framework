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
	static function getTable() {
		$table = !empty(static::$table) ? static::$table : 'untitled';

		return self::$prefix . $table;
	}

	// Model get fields method.
	static function getFields($post = [], $all = true) {
		$fields = !empty(static::$fields) ? static::$fields : [];

		$rows = [];

		foreach ( $fields as $key => $value ) {
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

	// Model query method.
	static function query($columns = '*', $clause = '', $bind = [], $fetch = '') {
		$table = self::getTable();

		$sql = "
			SELECT $columns FROM $table
		";

		if ( !empty($clause) ) {
			$sql .= " WHERE {$clause}";
		}

		switch ( $fetch ) {
			case 'row':
				return self::db()->selectRow($sql, $bind);
				break;

			case 'value':
				return self::db()->selectValue($sql, $bind);
				break;

			case 'column':
				return self::db()->selectColumn($sql, $bind);
				break;

			default:
				return self::db()->select($sql, $bind);
				break;
		}
	}

	// Model select one method.
	static function select($post = [], $fetch = '') {
		$clause = "";
		$bind = [];

		foreach ( $post as $column => $value ) {
			if ( empty($value) ) continue;

			$clause .= " AND {$column} = ?";
			array_push($bind, $value);
		}

		$clause = substr($clause, 4);

		return self::query('*', $clause, $bind, $fetch);
	}

	// Model insert method.
	static function insert($post = []) {
		return self::db()->insert(
			self::getTable(),
			self::getFields($post)
		);
	}

	// Model update method.
	static function update($id, $post = []) {
		$clause = [
			'id' => $id,
		];

		return self::db()->update(
			self::getTable(),
			self::getFields($post, false),
			$clause
		);
	}

	// Model delete method.
	static function delete($id) {
		$clause = [
			'id' => $id,
		];

		return self::db()->delete(
			self::getTable(),
			$clause
		);
	}

}
