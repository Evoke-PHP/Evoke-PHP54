<?php
/** SQL wrapper class
 *
 * Provides simple pass-through wrappers for the DB interface functions.
 *
 * Provides wrappers to help the writing of the following SQL statements:
 *   SELECT, UPDATE, DELETE, INSERT
 *
 *   Conditions for WHERE statements are either in string format or as a keyed
 *   array which is AND'ed together and the comparison operator '=' is used.
 *      Example: array(ID => 1, Name => '`Peter`') becomes ID=1 AND Name=`Peter`
 *   A condition passed in as a string is unchanged and can be used for more
 *   complex comparison operations.
 */
class SQL implements Iface_DB
{
   protected $inTransaction = false;

   protected $setup;
   
   /// Class constructor
   public function __construct($setup=array())
   {
      $this->setup = array_merge(array('DB' => NULL), $setup);

      if (!$this->setup['DB'] instanceof Iface_DB)
      {
	 throw new InvalidArgumentException(__METHOD__ . ' needs DB');
      }
  }
   
   /*****************************************/
   /* Public Methods - Transaction Handling */
   /*****************************************/

   /// Begin a transaction or raise an exception if we are already in one.
   public function beginTransaction()
   {
      if ($this->inTransaction)
      {
	 throw new Exception_DB(__METHOD__, 'Already in a transaction.');
      }
      else
      {
	 $this->inTransaction = true;
	 return $this->setup['DB']->beginTransaction();
      }
   }

   /// Commit the current transaction.
   public function commit()
   {
      if ($this->inTransaction)
      {
	 $this->inTransaction = false;
	 return $this->setup['DB']->commit();
      }
      else
      {
	 throw new Exception_DB(__METHOD__, 'Not in a transaction.');
      }
   }

   /// Return whether we are in a trasaction.
   public function inTransaction()
   {
      return $this->inTransaction;
   }
   
   /// Rolls back the current transaction.
   public function rollBack()
   {
      if ($this->inTransaction)
      {
	 $this->inTransaction = false;
	 return $this->setup['DB']->rollBack();
      }
      else
      {
	 throw new Exception_DB(__METHOD__, 'Not in a transaction.');
      }
   }
   
   /*************************************************/
   /* Public Methods - Simple Pass-Through Wrappers */
   /*************************************************/

   /// Return the SQLSTATE.
   public function errorCode()
   {
      return $this->setup['DB']->errorCode();
   }

   /// Get the extended error information associated with the last DB operation.
   public function errorInfo()
   {
      return $this->setup['DB']->errorInfo();
   }

   /// Execute an SQL statement and return the number of rows affected.
   public function exec($statement)
   {
      return $this->setup['DB']->exec($statement);
   }

   /// Get a database connection attribute.
   public function getAttribute($attribute)
   {
      return $this->setup['DB']->getAttribute($attribute);
   }

   /// Get an array of available PDO drivers.
   public function getAvailableDrivers()
   {
      return $this->setup['DB']->getAvailableDrivers();
   }
   
   /// Get the ID of the last inserted row or sequence value.
   public function lastInsertId($name=NULL)
   {
      return $this->setup['DB']->lastInsertId($name);
   }

   /// Prepares a statement for execution and returns a statement object.
   public function prepare($statement, $driverOptions=array())
   {
      try
      {
	 $namedPlaceholders = (strpos($statement, ':') !== false);
	 
	 $this->setAttribute(
	    PDO::ATTR_STATEMENT_CLASS,
	    array('PDOStatement_Extended', array($namedPlaceholders)));
	 
	 return $this->setup['DB']->prepare($statement, $driverOptions);
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(__METHOD__, '', $this->setup['DB'], $e);
      }
   }

   /** Executes an SQL statement, returns a result set as a PDOStatement object.
    *  Any supplied object should be filled as per the fetch options.
    */
   public function query($sql, $fetchMode=0, $into=NULL)
   {
      $namedPlaceholders = (strpos($sql, ':') !== false);

      $this->setAttribute(
	 PDO::ATTR_STATEMENT_CLASS,
	 array('PDOStatement_Extended', array($namedPlaceholders)));

      if ($fetchMode === 0)
      {
	 return $this->setup['DB']->query($sql);
      }
      else
      {
	 return $this->setup['DB']->query($sql, $fetchMode, $into);
      }
   }  

   /// Quotes the input string (if required) and escapes special characters.
   public function quote($string, $parameterType=PDO::PARAM_STR)
   {
      return $this->setup['DB']->quote($string, $parameterType);
   }
   
   /// Sets an attribute on the database
   public function setAttribute($attribute, $value)
   {
      return $this->setup['DB']->setAttribute($attribute, $value);
   }
   
   /*****************************/
   /* Public Methods - Wrappers */
   /*****************************/
   
   /// Get an associative array of results for the sql.
   public function getAssoc($sql, $params=array())
   {
      try
      {
	 $stmt = $this->prepare($sql);
	 $params = is_array($params) ? $params : array($params);
	 $stmt->execute($params);

	 return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Exception Raised for sql: ' . var_export($sql, true) .
	    ' params: ' . var_export($params, true),
	    $this->setup['DB'],
	    $e);
      }
   }

   /** Get a result set which must contain exactly one row and return it.
    *  @param sql \string The sql to get exactly one row.
    *  @param params \array The parmeters for the sql query.
    */
   public function getSingleRow($sql, $params=array())
   {
      // Prepare
      try
      {
	 $stmt = $this->prepare($sql);
	 $params = is_array($params) ? $params : array($params);
	 $stmt->execute($params);
	 $result = $stmt->fetch(PDO::FETCH_ASSOC);

	 // Check if there is more than a single row.
	 if ($stmt->fetch(PDO::FETCH_ASSOC))
	 {
	    throw new Exception('Unexpected Multiple rows received.');
	 }
	 
	 return $result;
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Exception Raised for sql: ' . var_export($sql, true) .
	    ' params: ' . var_export($params, true),
	    $this->setup['DB'],
	    $e);
      }
   }

   /** Get a single value result from an sql statement.
    *  @param sql \string The sql to get exactly one row.
    *  @param params \array The parmeters for the sql query.
    *  @param column \int The column of the row to return the value for.
    */
   public function getSingleValue($sql, $params=array(), $column=0)
   {
      // Prepare
      try
      {
	 $stmt = $this->prepare($sql);
	 $params = is_array($params) ? $params : array($params);
	 $stmt->execute($params);
	 $result = $stmt->fetchColumn($column);

	 // Check if there is more than a single row.
	 if ($stmt->fetchColumn($column))
	 {
	    throw new Exception('Unexpected multiple rows received.');
	 }
	 
	 return $result;
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Exception Raised for sql: ' . var_export($sql, true) .
	    ' params: ' . var_export($params, true),
	    $this->setup['DB'],
	    $e);
      }
   }
   
   /** Simple SQL SELECT statement wrapper.
    *  @param tables \mixed Tables to select from.
    *  @param fields \mixed Fields to select.
    *  @param conditions \mixed Conditions (see class description).
    *  @param order \mixed ORDER BY directives.
    *  @param limit \int Number of records - defaults to unlimited.
    *  @param distinct \bool Return only distinct records.
    *  \returns An associative \array containing the data returned by the query.
    */
   public function select($tables, $fields, $conditions='', $order='', $limit=0,
			  $distinct=false)
   {
      try
      {
	 // SELECT fields FROM tables WHERE conditions ORDER BY order LIMIT lim
	 $q  = 'SELECT ';
	 
	 if ($distinct)
	 {
	    $q .= 'DISTINCT ';
	 }
	 
	 $q .= Utils::expand($fields) . ' FROM ' . Utils::expand($tables);
	 
	 if (!empty($conditions))
	 {
	    $q .= ' WHERE ' . Utils::placeholdersKeyed($conditions);
	 }
	 
	 if (!empty($order))
	 {
	    $q .= ' ORDER BY ' . Utils::placeholdersKeyed($order, ' ', ',');
	 }
	 
	 if (!empty($limit) && $limit !== 0)
	 {
	    $q .= ' LIMIT ' . $limit;
	 }

	 // Prepare
	 $stmt = $this->prepare($q);
	 
	 if (!is_array($conditions))
	 {
	    $conditions = array();
	 }
	 
	 if (!is_array($order))
	 {
	    $order = array();
	 }
	 
	 $params = array_merge($conditions, $order);
	 
	 // Execute and fetch the results as an associated array.
	 $stmt->execute($params);
	 $assoc = $stmt->fetchAll(PDO::FETCH_ASSOC);

	 return $assoc;
      }
      catch(Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Tables: ' . var_export($tables, true) .
	    ' Fields: ' .var_export($fields, true) .
	    ' Conditions: ' . var_export($conditions, true),
	    $this->setup['DB'], $e);
      }	 
   }

   /** Get a single value result from an sql select statement.
    *  @param table \string The table to get the value from.
    *  @param field \string The parmeters for the sql query.
    *  @param conditions \mixed The conditions for the WHERE.
    */
   public function selectSingleValue($table, $field, $conditions)
   {
      try
      {
	 $q  = 'SELECT ' . $field . ' FROM ' . $table;
	 
	 if (!empty($conditions))
	 {
	    $q .= ' WHERE ' . Utils::placeholdersKeyed($conditions);
	 }
	 
	 $stmt = $this->prepare($q);
	 $stmt->execute($conditions);

	 return $stmt->fetchColumn();
      }
      catch(Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Table: ' . var_export($table, true) .
	    ' Field: ' .var_export($field, true) .
	    ' Conditions: ' . var_export($conditions, true),
	    $this->setup['DB'], $e);
      }
   }

   /** Simple SQL UPDATE statement wrapper.
    *  @param tables \mixed Tables to update.
    *  @param setValues \mixed Keyed array of set values.
    *  @param conditions \mixed Conditions (see class description).
    *  @param limit \int Number of records - defaults to unlimited.
    */
   public function update($tables, $setValues, $conditions='', $limit=0)
   {
      $q  = 'UPDATE ' . Utils::expand($tables);
      $q .= ' SET ' . Utils::placeholdersKeyed($setValues, '=', ',');

      if (!empty($conditions))
      {
	 $q .= ' WHERE ' . Utils::placeholdersKeyed($conditions, '=', ' AND ');
      }
      
      if (!empty($limit) && $limit !== 0)
      {
	 $q .= ' LIMIT ' . $limit;
      }

      // Prepare
      try
      {
	 $stmt = $this->prepare($q);
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(__METHOD__, 'Prepare', $this->setup['DB'], $e);
      }

      $params = array_merge(array_values($setValues),
			    array_values($conditions));

      // Execute
      if ($stmt->execute($params) === false)
      {
	 throw new Exception_DB(__METHOD__, 'Execute', $stmt);
      }
      
      return true;
   }

   /** Simple SQL DELETE statement wrapper.
    *  @param tables \mixed Tables to delete from.
    *  @param conditions \mixed Conditions (see class description).
    */
   public function delete($tables, $conditions)
   {
      $q = 'DELETE FROM ' . Utils::expand($tables) . ' WHERE ';

      foreach ($conditions as $field => $value)
      {
	 if (!isset($value))
	 {
	    $q .= $field . ' IS NULL AND ';
	    unset($conditions[$field]);
	 }
	 else
	 {
	    $q .= $field . '=? AND ';
	 }
      }

      $q = rtrim($q, 'AND ');
      
      // Prepare
      try
      {
	 $stmt = $this->prepare($q);
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Prepare query: ' . var_export($q, true),
	    $this->setup['DB'],
	    $e);
      }

      // Execute
      try
      {
	 $stmt->execute($conditions);
      }
      catch (Exception $e)
      {
	 throw new Exception_DB(
	    __METHOD__,
	    'Execute with conditions: ' . var_export($conditions, true),
	    $stmt);
      }
   }


   /** Simple SQL INSERT statement wrapper.
    *  @param table \string Table to insert into.
    *  @param fields \mixed Fields to insert.
    *  @param valArr \array An array specifying one or more record to insert.
    */
   public function insert($table, $fields, $valArr)
   {
      // Prepare
      try
      {
	 $stmt = $this->prepare(
	    'INSERT INTO ' . $table . ' (' . Utils::expand($fields) . ') ' .
	    'VALUES (' . Utils::placeholders($fields) . ')');
      }
      catch (Exception $e)
      {
	 $msg = 'Prepare Table: ' . var_export($table, true) . ' Fields: ' .
	    var_export($fields, true);
	 
	 throw new Exception_DB(__METHOD__, $msg, $this->setup['DB'], $e);
      }

      if (!is_array($valArr))
      {
	 $valArr = array($valArr);
      }
      
      // If the first entry in the values array is an array then we have
      // multiple records that we should be inserting.
      if (is_array(reset($valArr)))
      {
	 try
	 {
	    foreach ($valArr as $entryNum => $entry)
	    {
	       $stmt->execute($entry);
	    }
	 }
	 catch (Exception $e)
	 {
	    throw new Exception_DB(
	       __METHOD__,
	       'Multiple Values: ' . var_export($valArr, true),
	       $this->setup['DB'],
	       $e);
	 }
      }
      else // There should only be one entry to insert.
      {
	 try
	 {
	    $stmt->execute($valArr);
	 }
	 catch (Exception $e)
	 {
	    throw new Exception_DB(
	       __METHOD__,
	       'Single Value: ' . var_export($valArr, true),
	       $this->setup['DB'],
	       $e);
	 }
      }
   }
   
   /** Add a column to a table.
    *  @param table \string The table to add the column to.
    *  @param column \string The column name to add.
    *  @param fieldType \string The data type of the column to add.
    *  \return \bool Whether the add column was successful.
    */
   public function addColumn($table, $column, $fieldType)
   {
      $q = 'ALTER TABLE ' . $table . ' ADD COLUMN `' . $column . '` ' .
	 $fieldType;
      return $this->exec($q);
   }

   /** Drop a column from the table.
    *  @param table \string The table to drop the column from.
    *  @param column \string The column name to drop.
    *  \return \int The number of records modified.
    */
   public function dropColumn($table, $column)
   {
      $q = 'ALTER TABLE ' . $table . ' DROP COLUMN `' . $column . '`';
      return $this->exec($q);
   }

   /** Change a column in the table.
    *  @param table \string The table for the column change.
    *  @param oldCol \string The column name to change.
    *  @param newCol \string The field name to set the column to.
    *  @param fieldType \string The type of field to create.
    *  \return \int The number of records modified.
    */
   public function changeColumn($table, $oldCol, $newCol, $fieldType)
   {
      $q = 'ALTER TABLE ' . $table . ' CHANGE COLUMN `' . $oldCol . '` `' .
        $newCol . '` ' . $fieldType;
      return $this->exec($q);      
   }

}
// EOF