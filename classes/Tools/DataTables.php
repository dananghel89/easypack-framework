<?php

/**
 * Epack DataTables Class
 *
 * Server-side processing for DataTables. All helpers (simple(), complex(),
 * limit(), order(), filter(), sql_exec(), ...) come unchanged from the official
 * SSP class in the datatables/datatables package:
 * vendor/datatables/datatables/examples/server_side/scripts/ssp.class.php
 *
 * @see https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
 */

namespace Epack\Tools;

use Composer\InstalledVersions;

if (!class_exists('SSP', false)) {
    $sspPath = InstalledVersions::getInstallPath('datatables/datatables');

    if ($sspPath === null) {
        throw new \RuntimeException('Epack DataTables needs the datatables/datatables package.');
    }

    // ssp.class.php reads $_SERVER['DOCUMENT_ROOT'], which the CLI doesn't set.
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
    $_SERVER['DOCUMENT_ROOT'] = $documentRoot ?? '';

    require_once $sspPath . '/examples/server_side/scripts/ssp.class.php';

    if ($documentRoot === null) {
        unset($_SERVER['DOCUMENT_ROOT']);
    }

    unset($sspPath, $documentRoot);
}

class DataTables extends \SSP
{
    /**
     * Like simple(), but the table isn't quoted, so it can be a join or an
     * alias ("posts p JOIN users u ON u.id = p.user_id"). Never pass user
     * input as $table.
     *
     * @param  array     $request    Data sent to server by DataTables
     * @param  array|\PDO $conn      PDO connection resource or connection parameters array
     * @param  string    $table      SQL table expression to query
     * @param  string    $primaryKey Primary key of the table
     * @param  array     $columns    Column information array
     * @return array                 Server-side processing response array
     */
    public static function extended($request, $conn, $table, $primaryKey, $columns)
    {
        $bindings = [];
        $db = self::db($conn);

        // Build the SQL query string from the request
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns, $bindings);

        // Main query to actually get the data
        $data = self::sql_exec(
            $db,
            $bindings,
            "SELECT `" . implode("`, `", self::pluck($columns, 'db')) . "`
             FROM $table
             $where
             $order
             $limit",
        );

        // Data set length after filtering
        $resFilterLength = self::sql_exec(
            $db,
            $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM $table
             $where",
        );
        $recordsFiltered = $resFilterLength[0][0];

        // Total data set length
        $resTotalLength = self::sql_exec(
            $db,
            "SELECT COUNT(`{$primaryKey}`)
             FROM $table",
        );
        $recordsTotal = $resTotalLength[0][0];

        return [
            'draw'            => isset($request['draw']) ? intval($request['draw']) : 0,
            'recordsTotal'    => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data'            => self::data_output($columns, $data),
        ];
    }
}
