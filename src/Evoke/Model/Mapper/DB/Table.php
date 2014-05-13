<?php
/**
 * Database Table Mapper using PDO.
 *
 * WARNING
 *
 * This is only to be used for fixed or sanitized data. It should not be used
 * with human input data. This code does not avoid SQL injections.
 *
 * @package Model
 */
namespace Evoke\Model\Mapper\DB;

use PDO;

/**
 * Database Table Mapper.
 *
 * WARNING
 *
 * This is only to be used for fixed or sanitized data. It should not be used
 * with human input data. This code does not avoid SQL injections.
 *
 * Maps a single database table.
 *
 * @author Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2014 Paul Young
 * @license MIT
 * @package Model\Mapper\DB
 */
class Table
{
    /**
     * The PDO database connection.
     * @var PDO
     */
    protected $pdo;

    /**
     * Table name.
     * @var string
     */
    protected $tableName;

    /**
     * Construct a mapper for a database table.
     *
     * @param PDO    Database connection.
     * @param string Table name for the database table we are mapping.
     */
    public function __construct(PDO $pdo, $tableName)
    {
        $this->pdo       = $pdo;
        $this->tableName = $tableName;
    }

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Create a record in the table.
     *
     * @param mixed[] The record to add.
     */
    public function create(Array $record)
    {
        $sql = 'INSERT ' . $this->tableName . ' SET ' .
            $this->placeholdersKeyed($record, '=', ',');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($record);
    }

    /**
     * Create multiple records in the table.
     *
     * @param mixed[] The records to add.
     */
    public function createMultiple(Array $data)
    {
        $sql = 'INSERT ' . $this->tableName . ' SET ' .
            $this->placeholdersKeyed(reset($data), '=', ',');

        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $record)
        {
            $stmt->execute($record);
        }
    }

    /**
     * Delete record(s) from the table.
     *
     * @param mixed[]     Conditions to match.
     * @param string|null Limit of records to delete.
     */
    public function delete(Array $conditions, $limit = NULL)
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' WHERE ' .
            $this->placeholdersKeyed($conditions, '=', ',');

        if (!empty($limit))
        {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);
    }

    /**
     * Read data from the table.
     *
     * @param mixed[]     The fields to read.
     * @param mixed[]     Conditions to match (defaults to match all).
     * @param string|null Order to sort the records by (defaults to no order).
     * @param string|null Limit of records to read (defaults to unlimited).
     *
     * @return mixed[] Array of records from the table.
     */
    public function read(Array        $fields,
                         Array        $conditions = array(),
                         /* String */ $order      = NULL,
                         /* String */ $limit      = NULL)
    {
        $sql = 'SELECT ' . implode($fields, ',') . ' FROM '
            . $this->tableName;

        if (!empty($conditions))
        {
            $sql .= ' WHERE ' .
                $this->placeholdersKeyed($conditions, '=', ' AND ');
        }

        if (!empty($order))
        {
            $sql .= ' ORDER BY ' . $order;
        }

        if (!empty($limit))
        {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->pdo->prepare($sql);

        if (empty($conditions))
        {
            $stmt->execute();
        }
        else
        {
            $stmt->execute($conditions);
        }

        return $stmt->fetchAll(PDO::FETCH_NAMED);
    }

    /**
     * Set the table that we are mapping.
     *
     * @param string The name of the table to map.
     */
    public function setTable(/* String */ $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Update a record in the table.
     *
     * @param mixed[] The record(s) to modify.
     * @param mixed[] The values to set the new record to.
     * @param int     The limit of records to modify.
     */
    public function update(Array $oldMatch, Array $newRecord, $limit = 0)
    {
        $sql  = 'UPDATE ' . $this->tableName . ' SET ' .
            $this->placeholdersKeyed($newRecord, '=', ',') .
            ' WHERE ' . $this->placeholdersKeyed($oldMatch, '=', ' AND ');

        if (!empty($limit))
        {
            $sql .= ' LIMIT ' . $limit;
        }

        $statement = $this->pdo->prepare($sql);
        $params = array_merge(array_values($newRecord),
                              array_values($oldMatch));
        $statement->execute($params);
    }

    /*******************/
    /* Private Methods */
    /*******************/

    /**
     * Implode the array with placeholders inserted for a PDO statement.
     *
     * @param mixed[] The array to implode.
     * @param string  String to place between the key and the placeholder.
     * @param string  String to use as a separator between items in the array.
     */
    private function placeholdersKeyed(
        Array $placeholders, $between, $separator)
    {
        $str = '';

        foreach (array_keys($placeholders) as $key)
        {
            $str .= $key . $between . '?' . $separator;
        }

        return substr($str, 0, -strlen($separator));
    }
}
// EOF
