<?php
namespace Evoke\Element\Form;

class EntryDialog extends Entry
{
   public function __construct($setup=array())
   {
      // By default, dialogs are wrapped by their own form element.
      $setup = array_merge(
	 array('Attribs' => array('class'  => 'Entry Dialog Info',
				  'action' => '',
				  'method' => 'post'),
	       'Options' => array('Finish' => false,
				  'Start' => false)),
	 $setup);
      
      parent::__construct($setup);

      /// \todo Fix this class.
      throw new Exception(__METHOD__ . ' needs update to new element class');
   }
   
   /*********************/
   /* Protected Methods */
   /*********************/

   /// Build the buttons that are contained in the form.
   protected function buildFormButtons()
   {
      if (empty($this->setup['Field_Values']))
      {
	 $submitButtons = array(
	    new Element_Submit(
	       array('class' => 'Dialog_Submit Button Good Small',
		     'name'  => $this->setup['Table_Name'] . '_Add',
		     'value' => $this->setup['Translator']->get('Add'))));
      }
      else
      {
	 $submitButtons = array(
	    new Element_Submit(
	       array('class' => 'Dialog_Submit Button Info Small',
		     'name'  => $this->setup['Table_Name'] . '_Modify',
		     'value' => $this->setup['Translator']->get('Edit'))));
      }
	    
      $submitButtons[] = new Element_Submit(
	 array('class' => 'Dialog_Cancel Button Bad Small',
	       'name'  => $this->setup['Table_Name'] . '_Cancel',
	       'value' => $this->setup['Translator']->get('Cancel')));
      
      $this->addElement(
	 array(
	    'div',
	    $this->setup['Submit_Button_Attribs'],
	    array('Children' => $submitButtons)));
   }
}
// EOF