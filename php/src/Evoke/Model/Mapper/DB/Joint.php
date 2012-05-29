<?php
namespace Evoke\Model\Mapper\DB;

use Evoke\Iface;

/** Represent the data for a joint set of tables in a database. This provides
 *  read-only access to the data for the specified table and its related data
 *  obtained through the @ref Joins.
 */
class Joint extends \Evoke\Model\Mapper\DB
{
	/** @property $joins
	 *  Joins @object which lists the relationships for the data, allowing it to
	 *  be joint together into a meaningful unit of data.
	 */
	protected $joins;
	
	/** @property $select
	 *  Select @array for the SQL statement.
	 */
	protected $select;
	
	/** @property $tableName
	 * Table name for the data.
	 */
	protected $tableName;

	/** Construct a Model of a joint set of database tables.
	 *  @param sql        @object SQL object.   
	 *  @param tableName  @string The table name where joins start from.
	 *  @param joins      @object Joins object.
	 *  @param select     @array  Select statement settings.
	 */
	public function __construct(Iface\DB\SQL         $sql, 
	                            /* String */         $tableName,
	                            Iface\DB\Table\Joins $joins,
	                            Array                $select = array())
	{
	   
		if (!is_string($tableName))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires tableName as string');
		}
		
		parent::__construct($sql);

		$select += array('Conditions' => '',
		                 'Fields'     => '*',
		                 'Order'      => '',
		                 'Limit'      => 0);
	
		$this->joins     = $joins;
		$this->select    = $select;
		$this->tableName = $tableName;
	}

	/******************/
	/* Public Methods */
	/******************/

	/** Fetch some data from the mapper (specified by params).
	 *  @param params \array The conditions to match in the mapped data.
	 */
	public function fetch(Array $params = array())
	{
		$params += $this->select;
		$tables = $this->tableName . $this->joins->getJoinStatement();

		if ($params['Fields'] === '*')
		{
			$params['Fields'] = $this->joins->getAllFields();
		}

		return $this->joins->arrangeResults(
			$this->sql->select($tables,
			                   $params['Fields'],
			                   $params['Conditions'],
			                   $params['Order'],
			                   $params['Limit']));
	}
}
// EOF