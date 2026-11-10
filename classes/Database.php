<?php

/**
 * Epack Database Class
 */

namespace Epack;

class Database
{
    // Database properties.
    protected static $config;
    protected static $source;
    protected static $pdo;

    // Database constructor.
    public function __construct()
    {
        $vars = \Epack\Config::getVars();

        if (isset($vars->db)) {
            self::$config = $vars->db;

            $this->setSource();
            $this->setPdo();
        }
    }

    // Database set source method.
    protected function setSource()
    {
        extract(self::$config, EXTR_SKIP);

        self::$source = new \Delight\Db\PdoDataSource($drive);

        // sqlite: `name` is a file path, or `:memory:` for an in-memory database
        if ($drive === \Delight\Db\PdoDataSource::DRIVER_NAME_SQLITE) {
            // clear the network defaults that the data source sets for every driver
            self::$source->setHostname(null)
                ->setPort(null)
                ->setCharset(null);

            if ($name === ':memory:') {
                self::$source->setMemory(true);

            } else {
                self::$source->setFilePath($name);
            }

            return;
        }

        self::$source->setHostname($hostname)
            ->setPort($port)
            ->setDatabaseName($name)
            ->setCharset($charset)
            ->setUsername($username)
            ->setPassword($password);
    }

    // Database set pdo method.
    protected function setPdo()
    {
        if (empty(self::$source)) {
            return;
        }

        self::$pdo = \Delight\Db\PdoDatabase::fromDataSource(self::$source);
    }

    // Database get instance method.
    public static function getInstance()
    {
        return (object) [
            'source' => self::$source,
            'pdo' => self::$pdo,
            'prefix' => self::$config['prefix'],
        ];
    }

}
