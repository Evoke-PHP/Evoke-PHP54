<?php
/**
 * DB Metadata
 *
 * @package Model
 */
namespace Evoke\Model\Data\Metadata;

use InvalidArgumentException;

/**
 * DB Metadata
 * ===========
 *
 * Describe the data for a database.  Relational databases contain special
 * information that describes the data stored in them.
 *
 * - Primary keys are used to uniquely identify records.
 * - Foreign keys are used to join information between tables.
 *
 * This information shows us how to use the data in a meaningful way. This class
 * is responsible for providing the means to use the data in a meaningful way.
 *
 * Usage
 * =====
 *
 * Below is an example usage of the DB metadata which aims to provide a list of
 * products, each of a particular size with a set of related images.
 *
 * Database Structure
 * ------------------
 *
 * (PK = Primary Key, FK = Foreign Key):
 *
 * <pre>
 *   +====================+     
 *   | Product            |     +===============+
 *   +--------------------+     | Image_List    |
 *   | PK | ID            |     +---------------+     +===============+
 *   |    | Name          |     | PK | ID       |     | Image         |
 *   | FK | Image_List_ID |---->|    | List_ID  |     +---------------+
 *   | FK | Size_ID       |-.   | FK | Image_ID |---->| PK | ID       |
 *   +====================+ |   +===============+     |    | Filename |
 *                          |                         +===============+
 *                          |   +===========+
 *                   	    |   | Size      |
 *                   	    |   +-----------+
 *                   	    `-->| PK | ID   |
 *                   	        |    | Name |
 *                   	        +===========+
 * </pre>
 *
 * SQL Statement
 * -------------
 *
 * The following SQL statement would be used to get the data for our example:
 *
 * <code><pre>
 * 'SELECT * FROM Product
 *  LEFT JOIN Image_List AS IL ON Product.Image_List_ID=IL.List_ID
 *  LEFT JOIN Image      AS I  ON IL.Image_ID=I.ID
 *  LEFT JOIN Size       AS S  ON Product.Size_ID=Size.ID;'
 * </pre></code>
 * 
 * Metadata Structure
 * ------------------
 *
 * The following is the structure required for the metadata objects:
 *
 * - Product metadata
 *
 * <pre><code>
 * [
 *     'Fields'       => ['ID', 'Name', 'Image_List_ID', 'Size_ID'],
 *     'Joins'        =>
 *     [
 *         'Image_List_ID' =>
 *         [
 *             'Field'    => 'List_ID',
 *             'Metadata' => $metadataImageList,
 *             'Table'    => 'Image_List'
 *         ],
 *         'Size_ID'       =>
 *         [
 *             'Field'    => 'ID',
 *             'Metadata' => $metadataSize,
 *             'Table'    => 'Size'
 *         ]
 *     ],
 *     'Primary_Keys' => ['ID'],
 *     'Table_Alias'  => 'Product',
 *     'Table_Name'   => 'Product'
 * ]
 * </code></pre>
 *
 * - Image List metadata
 *
 * <pre><code>
 * [
 *     'Fields'       => ['ID', 'List_ID', 'Image_ID'],
 *     'Joins'        =>
 *     [
 *         'List_ID' =>
 *         [
 *             'Field'    => 'Image_ID'
 *             'Metadata' => $metadaImage,
 *             'Table'    => 'Image'
 *         ]
 *     ],
 *     'Primary_Keys' => ['ID'],
 *     'Table_Alias'  => 'IL',
 *     'Table_Name'   => 'Image_List'
 * ]
 * </code></pre>
 *
 * - Image metadata
 *
 * <pre><code>
 * [
 *     'Fields'       => ['ID', 'Filename'],
 *     'Joins'        => [],
 *     'Primary_Keys' => ['ID'],
 *     'Table_Alias'  => 'I',
 *     'Table_Name'   => 'Image'
 * ]
 * </code></pre>
 *
 * - Size metadata
 *
 * <pre><code>
 * [
 *     'Fields'       => ['ID', 'Name'],
 *     'Joins'        => [],
 *     'Primary_Keys' => ['ID'],
 *     'Table_Alias'  => 'S',
 *     'Table_Name'   => 'Size'
 * ]
 * </code></pre>
 *
 * @author Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license MIT
 * @package Model
 */
class DB implements MetadataIface
{
	protected
		/**
		 * Fields for the database table.
		 * @var string[]
		 */
		$fields,
		
		/**
		 * Joins from the database table.
		 * <pre><code>
		 * [<Parent_Field> => ['Field'    => <Child_Field>,
		 *                     'Metadata' => $metadata,
		 *                     'Table'    => <Child_Table>]]
		 * </code></pre>
		 *
		 * @var mixed[]
		 */
		$joins,

		/**
		 * Primary keys for the database table.
		 * @var string[]
		 */
		$primaryKeys,
		
		/**
		 * TableAlias
		 * @var string
		 */
		$tableAlias,
		
		/**
		 * TableName
		 * @var string
		 */
		$tableName;

	/**
	 * Construct the metadata that describes the database data.
	 *
	 * @param string[] Fields for the databaes table.
	 * @param mixed[]  Joins from the database table.
	 * @param string[] Primary keys for the database table.
	 * @param string   Table Alias.
	 * @param string   Table Name.
	 */
	public function __construct(Array        $fields,
	                            Array        $joins,
	                            Array        $primaryKeys,
	                            /* string */ $tableAlias,
	                            /* string */ $tableName)
	{
		$this->fields      = $fields;
		$this->joins       = $joins;
		$this->primaryKeys = $primaryKeys;
		$this->tableAlias  = $tableAlias;
		$this->tableName   = $tableName;
	}
	
	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Arrange a set of results for the database according to the Join tree.
	 *
	 * @param mixed[] The flat result data.
	 * @param mixed[] The data already processed from the results.
	 *
	 * @returns mixed[] The data arranged into a hierarchy by the joins.
	 */
	public function arrangeFlatData(Array $results, Array $data=array())
	{		
		// Get the data from the current table (this is a recursive function).
		foreach ($results as $rowResult)
		{
			$currentTableResult = $this->filterFields($rowResult);
	 
			// Determine whether the row has data for the current table.
			if ($this->isResult($currentTableResult))
			{
				$rowID = $this->getRowID($currentTableResult);
				
				if (!isset($data[$rowID]))
				{
					$data[$rowID] = $currentTableResult;
				}

				// If this result could contain information for referenced
				// tables lower in the heirachy set it in the joint data.
				if (!empty($this->joins))
				{
					if (!isset($data[$rowID][$this->jointKey]))
					{
						$data[$rowID][$this->jointKey] = array();
					}

					$jointData = &$data[$rowID][$this->jointKey];

					// Fill in the data for the joins by recursion.
					foreach($this->joins as $parentField => $ref)
					{
						if (!isset($jointData[$parentField]))
						{
							$jointData[$parentField] = array();
						}

						// Recurse - Arrange the single result (rowResult).
						$jointData[$parentField]  =
							$ref['Metadata']->arrangeFlatData(
								array($rowResult), $jointData[$parentField]);
					}	  
				}
			}
		}

		return $data;
	}	

	public function getJoins()
	{
		return $this->joins;
	}

	public function getRowID(Array $data)
	{
		$rowID = '';
		
		foreach ($data as $field => $value)
		{
			if (in_array($this->primaryKeys, $field))
			{
				$rowID .= (empty($rowID) ? '' : '_') . $value;
			}
		}		
		
		return $rowID;
	}

	/*******************/
	/* Private Methods */
	/*******************/

	/**
	 * Filter the fields that belong to a table from the list and return the
	 * fields without their table name preceding them.
	 *
	 * @param mixed[] The field list that we are filtering.
	 *
	 * @return mixed[]
	 */
	private function filterFields(Array $fieldList)
	{
		$filteredFields = [];
		$pattern = '/^' . $this->tableAlias . '\./';
		
		foreach ($fieldList as $key => $value)
		{
			if (preg_match($pattern, $key))
			{
				$filteredFields[preg_replace($pattern, '', $key)] = $value;
			}
		}

		return $filteredFields;
	}

	/**
	 * Determine whether the result is a result (has data for this table).
	 *
	 * @param mixed[] The result data.
	 *
	 * @return bool Whether the result contains information for this table.
	 */
	private function isResult(Array $result)
	{
		// A non result may be an array with all NULL entries, so we cannot just
		// check that the result array is empty.  The easiest way is just to
		// check that there is at least one value that is set.
		foreach ($result as $resultData)
		{
			if (isset($resultData))
			{
				return true;
			}
		}
		
		return false;
	}
}
// EOF