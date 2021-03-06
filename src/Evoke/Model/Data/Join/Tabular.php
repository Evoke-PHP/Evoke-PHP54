<?php
/**
 * Tabular Join
 *
 * @package Model\Data\Join
 */
namespace Evoke\Model\Data\Join;

use DomainException;

/**
 * <h1>Tabular Join</h1>
 *
 * Join data by table.
 *
 * Using a hierarchical join structure we can map a flat set of results like we might receive from a database query to a
 * meaningful hierarchical structure.
 *
 * Usage
 * =====
 *
 * Generally joins should be used via \ref Evoke\Model\Data\Data.  It is a complex task to build a join structure and
 * the associated data containers so \ref Evoke\Model\Data\DBDataBuilder can be used to build the multiple tree
 * structures required to muster and represent hierarchical data.
 *
 * Below is an example of using Tabular Joins to obtain a meaningful hierarchical structure from a database. The example
 * is for a list of products which can contain a set of related images.
 *
 * Database Structure
 * ------------------
 *
 * (PK = Primary Key, FK = Foreign Key):
 *
 * <pre>
 *                          +=================+
 *   +============+         | product_images  |
 *   | product    |         +-----------------+         +============+
 *   +------------+         | PK | id         |         | image      |
 *   | PK | id    |-||----|<| FK | product_id |         +------------+
 *   |    | name  |         | FK | image_id   |>|----||-| PK | id    |
 *   +============+         +=================+         |    | name  |
 *                                                      +============+
 * </pre>
 *
 * SQL Statement
 * -------------
 *
 * The following SQL query would be used to get the flat results:
 *
 * <pre><code>
 * SELECT
 *     product.id   AS product_t_id,
 *     product.name AS product_t_name,
 *     image.id     AS image_t_id,
 *     image.name   AS image_t_name
 * FROM
 *     product
 *     LEFT JOIN product_images ON product.id = product_images.product_id
 *     LEFT JOIN image          ON image.id   = product_images.image_id
 * </code></pre>
 *
 * Note: The SQL query forces the results to a tabular format by prepending the result fields with the table name. This
 *       allows the tabular join to identify the tables which the result fields belong to.
 *
 * Join Structure
 * ------------------
 *
 * The Join structure that will help us arrange this data is:
 *
 * <pre><code>
 * $joinStructure = new Tabular('product');
 * $joinStructure->addJoin('image', new Tabular('image'));
 * </code></pre>
 *
 * Example Flat Input Data
 * -----------------------
 *
 * <pre><code>
 * $results = [['product_t_id'   => 1,
 *              'product_t_name' => 'P_One',
 *              'image_t_id'     => NULL,
 *              'image_t_name'   => NULL],
 *             ['product_t_id'   => 2,
 *              'product_t_name' => 'P_Two',
 *              'image_t_id'     => 1,
 *              'image_t_name'   => 'Image.png'],
 *             ['product_t_id'   => 3,
 *              'product_t_name' => 'P_Three',
 *              'image_t_id'     => 2,
 *              'image_t_name'   => 'I_One.png'],
 *             ['product_t_id'   => 3,
 *              'product_t_name' => 'P_Three',
 *              'image_t_id'     => 3,
 *              'image_t_name'   => 'I_Two.png']];
 * </code></pre>
 *
 * Arrange the Data
 * ----------------
 *
 * <pre><code>
 * $joinStructure->arrangeFlatData($results);
 * </code></pre>
 *
 * Below is a pretty version of the hierarchical data from the arrangement:
 *
 * <pre><code>
 * [1 => ['name'       => 'P_One',
 *        'joint_data' => ['image' => []]],
 *  2 => ['name'       => 'P_Two',
 *        'joint_data' => ['image' => [1 => ['name' => 'Image.png']]]],
 *  3 => ['name'       => 'P_Three',
 *        'joint_data' => ['image' => [2 => ['name' => 'I_One.png'],
 *                                     3 => ['name' => 'I_Two.png']]]]];
 * </code></pre>
 *
 * The data has been arranged so that a list of products identified by their primary keys contains their associated
 * image lists correctly identified by their image ID.
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @license   MIT
 * @package   Model\Data\Join
 */
class Tabular extends Join
{
    /**
     * Field to use for joining data in the arranged results.
     * @var string
     */
    protected $jointKey;

    /**
     * Keys used to identify records in the current table.
     * @var string[]
     */
    protected $keys;

    /**
     * Whether all flat result fields must be tabular (able to be identified by their table prefix and separator before
     * their field name).
     * @var bool
     */
    protected $reqAllTabularFields;

    /**
     * Separator between table and fields.
     * @var string
     */
    protected $separator;

    /**
     * Table name for the main records.
     * @var string
     */
    protected $tableName;

    /**
     * Construct the tabular join tree used to arrange the data.
     *
     * @param string   $tableName               Table name for collecting the main results.
     * @param string[] $keys                    Key fields for the records.
     * @param string   $jointKey                Field to use for joining data.
     * @param bool     $requireAllTabularFields Whether all result fields must be tabular.
     * @param string   $separator               Separator between table and fields.
     * @param bool     $useAlphaNumMatch        Can we refer to joins using a case-insensitive alphanumeric match?
     */
    public function __construct(
        $tableName,
        Array $keys = ['id'],
        $jointKey = 'joint_data',
        $requireAllTabularFields = true,
        $separator = '_t_',
        $useAlphaNumMatch = true
    ) {
        parent::__construct($useAlphaNumMatch);

        $this->jointKey            = $jointKey;
        $this->keys                = $keys;
        $this->reqAllTabularFields = $requireAllTabularFields;
        $this->separator           = $separator;
        $this->tableName           = $tableName;
    }

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Arrange a set of results for the database according to the Join tree.
     *
     * @param mixed[] $results The flat result data.
     * @return mixed[] The data arranged into a hierarchy by the joins.
     */
    public function arrangeFlatData(Array $results)
    {
        $splitResults = [];

        foreach ($results as $result) {
            $splitResults[] = $this->splitResultByTables($result);
        }

        return $this->arrangeSplitResults($splitResults);
    }

    /**
     * Arrange the results which have already been split into tables into hierarchical results according to the
     * metadata.
     *
     * @param string[][][] $splitResults
     * @param mixed[]      $data
     * Any hierarchical data that has already been arranged.
     * @return mixed[] The hierarchical results.
     */
    public function arrangeSplitResults(Array $splitResults, Array $data = [])
    {
        foreach ($splitResults as $splitResult) {
            if (!empty($splitResult[$this->tableName]) && $this->isResult($splitResult[$this->tableName])) {
                $rowID  = $this->filterRowID($splitResult[$this->tableName]);
                $result = $this->filterRowFields($splitResult[$this->tableName]);

                if (!isset($rowID)) {
                    // As we don't have a key to identify the row we must check to ensure that the result has not
                    // already been added.
                    $hasBeenAdded = false;

                    foreach ($data as $existingID => $existingEntry) {
                        unset($existingEntry[$this->jointKey]);

                        if (!array_diff_assoc($existingEntry, $result)) {
                            $hasBeenAdded = true;
                            $rowID        = $existingID;
                            break;
                        }
                    }

                    if (!$hasBeenAdded) {
                        $data[] = $result;
                        end($data);
                        $rowID = key($data);
                    }
                } elseif (!isset($data[$rowID])) {
                    $data[$rowID] = $result;
                }

                // If this result could contain information for referenced tables lower in the hierarchy set it in the
                // joint data.
                if (!empty($this->joins)) {
                    if (!isset($data[$rowID][$this->jointKey])) {
                        $data[$rowID][$this->jointKey] = [];
                    }

                    $jointData = &$data[$rowID][$this->jointKey];

                    // Fill in the data for the joins by recursion.
                    foreach ($this->joins as $joinID => $join) {
                        if (!isset($jointData[$joinID])) {
                            $jointData[$joinID] = [];
                        }

                        // Recurse - Arrange the single result (splitResult).
                        $jointData[$joinID] = $join->arrangeSplitResults([$splitResult], $jointData[$joinID]);
                    }
                }
            }
        }

        return $data;
    }

    /*********************/
    /* Protected Methods */
    /*********************/

    /**
     * Get the row identifier for the current row.
     *
     * @param mixed[] $row The data row.
     * @return null|string
     * @throws DomainException If the row does not contain all of the keys.
     */
    protected function filterRowID(Array $row)
    {
        $rowID = null;

        foreach ($this->keys as $key) {
            if (!isset($row[$key])) {
                throw new DomainException('Missing Key: ' . $key . ' for table: ' . $this->tableName);
            }

            $rowID .= (empty($rowID) ? '' : '_') . $row[$key];
        }

        return $rowID;
    }

    /**
     * Get the non-identifying fields from the current row.
     *
     * @param mixed[] $row The data row.
     * @return array
     */
    protected function filterRowFields(Array $row)
    {
        return array_diff_key($row, array_flip($this->keys));
    }

    /**
     * Split a result by the tables that the result data is from.  This can be
     * done thanks to the separator that identifies each table.
     *
     * @param mixed[] $result A flat result that is to be split.
     * @return array
     * @throws DomainException If the result cannot be split.
     */
    protected function splitResultByTables(Array $result)
    {
        $splitResult = [];

        foreach ($result as $field => $value) {
            $separated = explode($this->separator, $field);

            if (count($separated) !== 2) {
                if (!$this->reqAllTabularFields) {
                    // Skip this non tabular field.
                    continue;
                }

                throw new DomainException(
                    'Each flat result field should be able to be split by containing a single table separator.' . "\n" .
                    'Flat result field: ' . var_export($field, true) . "\n" . 'Table separator: ' .
                    var_export($this->separator, true)
                );
            }

            if (!isset($splitResult[$separated[0]])) {
                $splitResult[$separated[0]] = [];
            }

            $splitResult[$separated[0]][$separated[1]] = $value;
        }

        return $splitResult;
    }

    /*******************/
    /* Private Methods */
    /*******************/

    /**
     * Determine whether the result is a result (has data for this table).
     *
     * @param mixed[] $result The result data.
     * @return bool Whether the result contains information for this table.
     */
    private function isResult(Array $result)
    {
        // A non result may be an array with all NULL entries, so we cannot just
        // check that the result array is empty. The easiest way is just to
        // check that there is at least one value that is set.
        foreach ($result as $resultData) {
            if (isset($resultData)) {
                return true;
            }
        }

        return false;
    }
}
// EOF
