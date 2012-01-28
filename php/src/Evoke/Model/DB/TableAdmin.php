<?php
namespace Evoke\Model\DB;
/// Provide a CRUD interface to a database table.
class TableAdmin extends Table implements \Evoke\Core\Iface\Model\Admin
{
   protected $failures;
   protected $info;
   protected $notifications;
   protected $sessionManager;
   
   public function __construct(Array $setup)
   {
      $setup += array('Auto_Fields'    => array('ID'),
		      'Failures'       => NULL,
		      'Info'           => NULL,
		      'Notifications'  => NULL,
		      'SessionManager' => NULL,
		      'Validate'       => true);

      if (!$setup['Failures'] instanceof \Evoke\Core\MessageArray)
      {
	 throw new \InvalidArgumentException(
	    __METHOD__ . ' requires Failures as Message_Array');
      }

      if (!$setup['Info'] instanceof \Evoke\Core\DB\Table\Info)
      {
	 throw new \InvalidArgumentException(
	    __METHOD__ . ' requires DB Table Info');
      }

      if (!$setup['Notifications'] instanceof \Evoke\Core\MessageArray)
      {
	 throw new \InvalidArgumentException(
	    __METHOD__ . ' requires Notifications as Message_Array');
      }

      if (!$setup['SessionManager'] instanceof \Evoke\Core\SessionManager)
      {
	 throw new \InvalidArgumentException(
	    __METHOD__ . ' requires SessionManager');
      }

      parent::__construct($setup);
      
      $this->failures =& $this->setup['Failures'];
      $this->info =& $this->setup['Info'];
      $this->notifications =& $this->setup['Notifications'];
      $this->sessionManager =& $this->setup['SessionManager'];
   }

   /******************/
   /* Public Methods */
   /******************/

   /// Add a record to the table.
   public function add($record)
   {
      $this->failures->reset();

      // Let the database choose the automatic fields.
      foreach ($this->setup['Auto_Fields'] as $auto)
      {
	 unset($record[$auto]);
      }

      if ($this->setup['Validate'])
      {
	 if (!$this->info->isValid($record))
	 {
	    $this->failures = $this->info->getFailures();
	    return false;
	 }
      }
      
      try
      {
	 $this->sql->insert(
	    $this->setup['Table_Name'], array_keys($record), $record);

	 $this->sessionManager->reset();
         $this->notifications->add('Add', 'Successful');
         return true;
      }
      catch (\Exception $e)
      {
	 $msg = 'Table: ' . $this->setup['Table_Name'] . ' Exception: ' .
	    $e->getMessage();
	 
	 $this->em->notify('Log', array('Level'   => LOG_ERR,
					'Message' => $msg,
					'Method'  => __METHOD__));
	 
	 $this->failures->add(
	    'Record could not be added to table: ' . $this->setup['Table_Name'],
	    'System administrator has been notified of error.');

	 return false;
      }
   }

   public function cancel()
   {
      $this->sessionManager->reset();
   }

   /// Begin creating a new record.
   public function createNew()
   {
      $this->sessionManager->set('New_Record', true);
      $this->sessionManager->set('Current_Record', array());
      $this->sessionManager->set('Editing_Record', true);
      $this->sessionManager->set('Edited_Record', array());
   }

   /// Cancel the current delete that was requested.
   public function deleteCancel()
   {
      $this->sessionManager->reset();
   }
   
   // Delete a record from the table.
   public function deleteConfirm(Array $record)
   {
      $this->failures->reset();
      
      try
      {
	 $this->sql->delete($this->setup['Table_Name'], $record);

	 $this->sessionManager->reset();
         $this->notifications->add('Delete', 'Successful');
	 return true;
      }
      catch (\Exception $e)
      {
	 $msg = 'Table: ' . $this->setup['Table_Name'] . ' Exception: ' .
	    $e->getMessage();
	 
	 $this->em->notify('Log', array('Level'   => LOG_ERR,
					'Message' => $msg,
					'Method'  => __METHOD__));
	 
	 $this->failures->add(
	    'Record could not be deleted from table: ' .
	    $this->setup['Table_Name'],
	    'System administrator has been notified of error.');
	 
	 return false;
      }
   }

   public function deleteRequest($conditions)
   {
      $this->sessionManager->set('Delete_Request', true);

      $record = $this->sql->select(
	 $this->setup['Table_Name'], '*', $conditions);
      
      $this->sessionManager->set('Delete_Record', $record);
   }
   
   public function edit($record)
   {
      $result = $this->sql->select($this->setup['Table_Name'], '*', $record);

      if ($result === false)
      {
	 throw new \RuntimeException('Record_Not_Found');
      }
      else
      {
	 $this->sessionManager->set('New_Record', false);
	 $this->sessionManager->set('Current_Record', $result[0]);
	 $this->sessionManager->set('Editing_Record', true);
	 $this->sessionManager->set('Edited_Record', $record);
	 return true;
      }
   }

   /** Get the data.  This is the administration state plus the underlying
    *  from the parent class.
    */
   public function getData()
   {
      return array('Records' => parent::getData(),
		   'State'   => $this->sessionManager->getAccess());
   }
   
   /// Get the currently edited record.
   public function getCurrentRecord()
   {
      return $this->sessionManager->get('Current_Record');      
   }

      /// Whether a record is being edited.
   public function isEditingRecord()
   {
      return $this->sessionManager->is('Editing_Record', true);
   }  

   /// Whether the current record is a new entry.
   public function isNewRecord()
   {
      return $this->sessionManager->is('New_Record', true);
   }

   /** Modify a record in the table.
    *  \returns \bool Whether the modification was successful.
    */
   public function modify($record)
   {
      try
      {
	 $this->failures->reset();

	 if ($this->setup['Validate_Record'])
	 {
	    if (!$this->info->isValid($record))
	    {
	       $this->failures = $this->info->getFailures();
	       return false;
	    }
	 }

	 if ($this->sessionManager->issetKey('Edited_Record'))
	 {
	    $oldRecord = $this->sessionManager->get('Edited_Record');
	    $this->sql->update($this->setup['Table_Name'], $record, $oldRecord);
	 }
	 else
	 {
	    $this->sql->insert(
	       $this->setup['Table_Name'], array_keys($record), $record);
	 }

	 $this->notifications->add('Modify', 'Successful');
	 $this->sessionManager->reset();
	 return true;
      }
      catch (\Exception $e)
      {
	 $msg = 'Table: ' . $this->setup['Table_Name'] . ' Exception: ' .
	    $e->getMessage();
	 
	 $this->em->notify('Log', array('Level'   => LOG_ERR,
					'Message' => $msg,
					'Method'  => __METHOD__));
	 
	 $this->failures->add(
	    'Record could not be modified in table: ' .
	    $this->setup['Table_Name'],
	    'System administrator has been notified of error.');

	 return false;
      }
   }
   
   /*********************/
   /* Protected Methods */
   /*********************/

   /// Get the event map used for connecting events.
   protected function getProcessingEventMap()
   {
      return array_merge(parent::getProcessingEventMap(),
			 array('Add'            => 'add',
			       'Cancel'         => 'cancel',
			       'Create_New'     => 'createNew',
			       'Delete_Cancel'  => 'deleteCancel',
			       'Delete_Confirm' => 'deleteConfirm',
			       'Delete_Request' => 'deleteRequest',
			       'Edit'           => 'edit',
			       'Modify'         => 'modify'));
   }   
}
// EOF