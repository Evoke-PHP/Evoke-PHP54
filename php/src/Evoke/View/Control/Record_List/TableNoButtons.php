<?php
namespace Evoke\View\RecordList;

class TableNoButtons extends \Evoke\View\RecordList
{ 
	/*********************/
	/* Protected Methods */
	/*********************/

	/** Build the element holding the buttons in a row (No Buttons).
	 *  @param row \mixed The key for the row.
	 *  @param rowData \array The data for the row.
	 *  \return \array Array of elements that make up the buttons.
	 */    
	protected function buildRowButtons($row, $rowData)
	{
		return array('div', array('class' => 'No_Buttons'));
	}
}
// EOF