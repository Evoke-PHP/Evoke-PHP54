<?php
namespace Evoke\Element\Control\Record_List;

/// Element to display a list of records from a table.
class Table extends Base
{ 
	public function __construct(Array $setup)
	{
		$setup += array('Data'       => NULL,
		                'Table_Info' => NULL);

		if (!$this->tableInfo instanceof \Evoke\Core\DB\Table\Info)
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires Table_Info');
		}

		// Set specific fields in the setup for a table.
		$fields       = $tableInfo->getFields();
		$primaryKeys = $tableInfo->getPrimaryKeys();
		$tableName   = $tableInfo->getTableName();

		if (!isset($attribs))
		{
			$attribs =
				array('class' => 'Record_List ' . $tableName);
		}
  
		parent::__construct($setup);
	}
}
// EOF