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
	//
	// $all = true (insert): every field, with the given value or its default.
	// $all = false (update): only the given fields, never the "!" ones.
	// Falsy values (0, '0', '', false) are real values, not "missing".
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

			if ( array_key_exists($key, $post) ) {
				$rows[$key] = $post[$key];

			} elseif ( $all ) {
				$rows[$key] = ( $value === 'NOW' ) ? get_current_date() : $value;
			}
		}

		return $rows;
	}

	// Model quote column method.
	static function quoteColumn($column) {
		if ( !is_string($column) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column) ) {
			throw new \InvalidArgumentException('Invalid column name: ' . var_export($column, true));
		}

		return self::db()->quoteIdentifier($column);
	}

	// Model query method.
	//
	// $columns and $clause go into the SQL as they are: write them in code,
	// never from request data. Pass every value through $bind.
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

	// Model select method.
	//
	// Every non-null value is a condition (`column = value`, joined with AND).
	// Use query() without a clause to read a whole table.
	static function select($post = [], $fetch = '') {
		$clause = [];
		$bind = [];

		foreach ( $post as $column => $value ) {
			if ( $value === null ) continue;

			$clause[] = self::quoteColumn($column) . ' = ?';
			$bind[] = $value;
		}

		if ( empty($clause) ) {
			throw new \InvalidArgumentException('Model::select() needs at least one condition; use query() to read the whole table.');
		}

		return self::query('*', implode(' AND ', $clause), $bind, $fetch);
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

		$fields = self::getFields($post, false);

		// nothing to change
		if ( empty($fields) ) {
			return 0;
		}

		return self::db()->update(
			self::getTable(),
			$fields,
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
