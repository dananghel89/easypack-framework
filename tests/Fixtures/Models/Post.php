<?php

namespace Tests\Fixtures\Models;

class Post extends \Epack\Model {

	protected static $table = 'posts';

	protected static $fields = [
		'title'       => '',
		'status'      => 1,
		'!created_at' => 'NOW',
	];

	public static function createTable() {
		self::db()->exec('CREATE TABLE ep_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, status INTEGER, created_at TEXT)');
	}

	public static function dropTable() {
		self::db()->exec('DROP TABLE IF EXISTS ep_posts');
	}

}
