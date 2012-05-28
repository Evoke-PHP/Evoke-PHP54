<?php
namespace Evoke\View;

/// View to represent a list of records.
class RecordList extends \Evoke\View\Translated
{
	/** @property $contentAttribs
	 *  Attributes @array for the content.
	 */
	protected $contentAttribs;

	/** @property $data
	 *  @array The data for the record list.
	 */
	protected $data;

	/** @property $dataAttribs
	 *  Attributes @array for the data.
	 */
	protected $dataAttribs;

	/** @property $viewRowButtons
	 *  @object View for the row buttons.
	 */
	protected $viewRowButtons;
	
	/** @property $editedRecord
	 *  @array The edited record from the record list.
	 */
	protected $editedRecord;

	/** @property $emptyDataAttribs
	 *  Attributes @array for an empty record list.
	 */
	protected $emptyDataAttribs;

	/** @property $fields
	 *  @array of fields in the record list.
	 */
	protected $fields;

	/** @property $headingSetup
	 *  The setup for the headings.
	 */
	protected $headingSetup;

	/** @property $ignoredFields
	 *  @array Fields to be ignored in the record list.
	 */
	protected $ignoredFields;

	/** @property $labels
	 *  @array Labels.
	 */
	protected $labels;

	/** @property $primaryKeys
	 *  @array The primary keys for the record list.
	 */
	protected $primaryKeys;

	/** @property $rowAttribs
	 *  @array Attributes for the record list rows.
	 */
	protected $rowAttribs;

	/** @property $tableName
	 *  @string The table name for the record data.
	 */
	protected $tableName;

	/** @property $translateLabels
	 *  @bool Whether to translate the labels for the fields.
	 */
	protected $translateLabels;

	/** Construct a RecordList object.
	 *  @param translator      @object Translator.
	 *  @param data            @object Data.
	 *  @param viewButtons     @object ViewButtons.
	 *  @param viewRecord      @object ViewRecord.
	 *  @param fields          @array  Data fields to be displayed.
	 *  @param attribs         @array  Attributes for the record list.
	 *  @param contentAttribs  @array  Attributes for the content.
	 *  @param dataAttribs     @array  DataAttribs.
	 *  @param ignoredFields   @array  IgnoredFields.
	 *  @param labels          @array  Labels.
	 *  @param rowAttribs      @array  RowAttribs.
	 *  @param translateLabels @bool   TranslateLabels.
	 */
	public function __construct(
		Iface\Translator $translator,
		Iface\Model\Data $data,
		Iface\View       $viewButtons,
		Iface\View       $viewRecord,
		Array            $fields,
		Array            $attribs        = array('class' => 'Record_List'),
		Array            $contentAttribs = array('class' => 'Content'),
		Array            $dataAttribs    = array('class' => 'Data'),
		Array            $headings       = array(),			
		Array            $ignoredFields  = array(),
		Array            $rowAttribs     = array('class' => 'Row'))
	{
		if (!is_bool($translateLabels))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires translateLabels as bool');
		}

		parent::__construct($translator);

		$this->contentAttribs = $contentAttribs;
		$this->data           = $data;
		$this->dataAttribs    = $dataAttribs;
		$this->viewButtons    = $viewButtons;
		$this->viewRecord     = $viewRecord;
		$this->fields         = $fields;
		$this->headings       = array_merge(array('Bottom' => false,
		                                          'Every'  => -1,
		                                          'Inline' => false,
		                                          'Top'    => false),
		                                    $headings);
		$this->ignoredFields  = $ignoredFields;
		$this->rowAttribs     = $rowAttribs;
	}

	/******************/
	/* Public Methods */
	/******************/

	public function get()
	{
		$recordListElems = array();
		$fields = array_diff($this->fields, $this->ignoredFields);
		$headingElements = $this->getHeadings($fields);
		$headingRow = $this->buildHeadingRow($headings);

		if ($this->headingSetup['Top'])
		{
			$recordListElems[] = $headingRow;
		}

		$recordListElems[] = $this->buildContent($fields, $headingElements);

		if ($this->headingSetup['Bottom'])
		{
			$recordListElems[] = $headingRow;
		}

		$this->writer->write(array('div', $this->attribs, $recordListElems));
	}
	
	/*********************/
	/* Protected Methods */
	/*********************/

	/** Build the content of the record list including any inline headings,
	 *  data and data buttons. (Not the top or bottom headings).
	 *  @param headings @array Array of headings for use in inline headings.
	 *  @return Return the element containing the content of the record list.
	 */
	protected function buildContent($fields, $headings)
	{
		if ($this->data->isEmpty()))
		{
			$rowElems = array(
				array('div', $this->rowAttribs, $this->buildEmptyData()));
		}
		else
		{
			$rowElems = array();
	 
			foreach ($this->data as $row => $rowData)
			{
				$rowElems[] = array(
					'div',
					$this->rowAttribs,
					array($this->buildRowData(
						      $fields, $row, $rowData, $headings),
					      $this->buildRowButtons($row, $rowData)));
			}
		}

		return array('div', array('class' => 'Content'), $rowElems);
	}

	/** Build the element for an empty record list.
	 *  @param headingElem @object The heading element that may be used.
	 *  @return @array Array of elements for an empty record list.
	 */
	protected function buildEmptyData()
	{
		return array('div',
		             $this->emptyDataAttribs,
		             $this->translator->get('No_Records_Found'));
	}
   
	/** Build the heading row element.
	 *  @param headings @array Array of heading row elements.
	 *  @return @object The heading row element.
	 */
	protected function buildHeadingRow($headings)
	{
		return array('div',
		             array('class' => 'Row'),
		             array(array('div',
		                         $this->dataAttribs,
		                         $headings),
		                   array('div',
		                         $this->headingSetup['Buttons_Attribs'],
		                         $this->getHeadingButtons())));
	}

	/** Build the element holding the buttons in a row.
	 *  @param row @mixed The key for the row.
	 *  @param rowData @array The data for the row.
	 *  @return @array Array of elements that make up the buttons.
	 */
	protected function buildRowButtons($row, $rowData)
	{
		if ($this->rowButtonsAsForm)
		{
			return $this->app->getNew(
				'View_Form_Hidden_Input',
				array('App'            => $this->app,
				      'Attribs'        => array_merge(
					      array('action' => '',
					            'method' => 'post'),
					      $this->rowButtonsAttribs),
				      'Data'           => $rowData,
				      'Encasing'       => false,
				      'Ignored_Fields' => $this->ignoredFields,
				      'Name_Prefix'    => $this->tableName . '.',
				      'Primary_Keys'   => $this->getPrimaryKeys(),
				      'Submit_Buttons' => $this->rowButtons,
				      'Translator'     => $this->translator));
		}
		else
		{
			return array('div',
			             $this->rowButtonsAttribs,
			             $this->getRowButtons($row));
		}
	}
   
	/** Build the element holding the data in a row.
	 *  @param fields  @array The fields for the row.
	 *  @param row     @mixed The key for the row.
	 *  @param rowData @array The data for the row.
	 *  @return @array Array of elements that make up the data.
	 */
	protected function buildRowData($fields, $row, $rowData, $headings)
	{
		try
		{
			$rowElems = array();

			// Add inline headings and row contents to the row.
			if ($this->headingSetup['Inline'])
			{
				foreach($fields as $field)
				{
					$rowElems[] = array(
						'div',
						array('class' => 'Field_Row'),
						array($headings[$field],
						      array('div',
						            array('class' => 'Data_Item ' . $field .
						                  '_Field'),
						            $rowData[$field])));
				}
			}
			else
			{
				foreach($fields as $field)
				{
					$rowElems[] = array(
						'div',
						array('class' => 'Data_Item ' . $field . '_Field'),
						$rowData[$field]);
				}
			}
	 
			return array('div',
			             $this->getDataAttribs($row, $rowData),
			             $rowElems);
		}
		catch (Exception $e)
		{
			throw new \Evoke\Exception(
				__METHOD__,
				' Caught exception processing row data: ' .
				var_export($rowData, true),
				$e);
		}
	}

	/** Get the data attributes for the row.
	 *  @param row @mixed The key of the row.
	 *  @param rowData @array The data for the row.
	 */
	protected function getDataAttribs($row, $rowData)
	{
		$dataAttribs = $this->dataAttribs;

		// Counting from 0 the first row should be odd.
		if ($row%2 == 0)
		{
			$dataAttribs['class'] .= ' Odd';
		}
		else
		{
			$dataAttribs['class'] .= ' Even';
		}

		if ($this->isEditedRecord($rowData))
		{
			$dataAttribs['class'] .= ' Edited_Record';
		}

		return $dataAttribs;
	}
   
	/// Get the heading buttons that appear with Top or Bottom headings.
	protected function getHeadingButtons()
	{
		return $this->headingSetup['Buttons'];
	}
   
	/** Build the headings for each field.
	 *  @param fields @array Array of fields.
	 *  @return @array Associative array of the heading elements keyed by field.
	 */
	protected function getHeadings($fields)
	{
		$headings = array();

		foreach ($fields as $field)
		{
			// Use a different label if one has been specified by the label array
			// or a translation is required.
			if (isset($this->labels[$field]))
			{
				$headingText = $this->labels[$field];
			}
			elseif ($this->translateLabels)
			{
				$headingText = $this->translator->get(
					$this->tableName . '_Field_' . $field);
			}
			else
			{
				$headingText = $field;
			}

			$headings[$field] = array(
				'div',
				array('class' => 'Heading ' . $field . '_Field'),
				$headingText);
		}

		return $headings;
	}

	/// Get the primary keys for a row.
	protected function getPrimaryKeys()
	{
		return $this->primaryKeys;
	}

   
	/** Get the buttons for the row.
	 *  @param row @string The key of the row.
	 *  @return An array of button elements for the row.
	 */
	protected function getRowButtons($row)
	{
		$buttons = array();

		foreach ($this->rowButtons as $button)
		{
			$buttonElem = $this->app->getNew('Element', $button);
			$buttonElem->appendAttrib('name', '_' . $row);

			$buttons[] = $buttonElem;
		}

		return $buttons;
	}
   
	/** Determine if the row data is from the currently edited record.
	 *  @param rowData @array The data for the row.
	 *  @return @bool Whether the row is the currently edited record.
	 */
	protected function isEditedRecord($rowData)
	{
		if (empty($this->editedRecord))
		{
			return false;
		}

		// Check that every primary key matches.
		foreach ($this->editedRecord as $pKey => $pValue)
		{
			if (!isset($rowData[$pKey]) || $rowData[$pKey] !== $pValue)
			{
				return false;
			}
		}

		return true;
	}
}
// EOF