<?php
namespace Evoke\Element\Input;

class Select extends \Evoke\Element
{
	/** @property $appendData
	 *  \array Data to be appended to the options available for selection.  
	 */
	protected $appendData;

	/** @property $optionAttribs
	 *  \array Attributes for each select option.
	 */
	protected $optionAttribs;

	/** @property $prependData
	 *  Data to be prepended to the options available for selection.
	 */
	protected $prependData;

	/** @property $textField
	 *  \string The field to use from the data for the option text.
	 */
	protected $textField;

	/** @property $valueField
	 *  \string The field to use for the value of the options.
	 */
	protected $valueField;

	public function __construct(Array $setup)
	{
		$setup += array('Append_Data'    => array(),
		                'Option_Attribs' => array(),
		                'Prepend_Data'   => array(),
		                'Text_Field'     => NULL,
		                'Value_Field'    => 'ID');

		if (!is_string($setup['Text_Field']))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires Text_Field as string');
		}

		if (!is_string($setup['Value_Field']))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires Value_Field as string');
		}

		parent::__construct($setup);

		$this->appendData    = $setup['Append_Data'];
		$this->optionAttribs = $setup['Option_Attribs'];
		$this->prependData   = $setup['Prepend_Data'];
		$this->textField     = $setup['Text_Field'];
		$this->valueField    = $setup['Value_Field'];
	}
      
	/******************/
	/* Public Methods */
	/******************/

	/** Set the select element.
	 *  @param select \array The select data in the form:
	 *  \verbatim
	 *  array('Data'     => \array records, // Records for the select
	 *        'Selected' => \scalar value); // The value that is selected.
	 *  \endverbatim
	 */    
	public function set(Array $select)
	{
		$select += array('Data'     => array(),
		                 'Selected' => NULL);
		$optionElements = array();

		$fullData = array_merge($this->prependData,
		                        $select['Data'],
		                        $this->appendData);

		foreach ($fullData as $key => $record)
		{
			if (!isset($record[$this->textField]) ||
			    !isset($record[$this->valueField]))
			{
				throw new \InvalidArgumentException(
					__METHOD__ . ' Record: ' . var_export($record, true) .
					' at key: ' . $key . ' does not contain the required fields ' .
					'Text_Field: ' . $this->textField .
					' and Value_Field: ' . $this->valueField);
			}

			$value = $record[$this->valueField];
			$optionAttribs = array_merge($this->optionAttribs,
			                             array('value' => $value));
	 
			if (isset($select['Selected']) && $value == $select['Selected'])
			{
				$optionAttribs['selected'] = 'selected';
			}
	 
			$optionElements[] =
				array('option',
				      $optionAttribs,
				      array('Text' => $record[$this->textField]));
		}

		return parent::set(array('select',
		                         $this->attribs,
		                         array('Children' => $optionElements)));
	}
}
// EOF