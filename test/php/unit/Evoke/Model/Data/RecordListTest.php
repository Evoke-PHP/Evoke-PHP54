<?php
namespace Evoke_Test\Model\Data;

use Evoke\Model\Data\RecordList,
	PHPUnit_Framework_TestCase;

class RecordListTest extends PHPUnit_Framework_TestCase
{
	/******************/
	/* Data Providers */
	/******************/

	/*********/
	/* Tests */
	/*********/

	/**
	 * @covers Evoke\Model\Data\RecordList::hasSelectedRecord
	 * @covers Evoke\Model\Data\RecordList::isSelectedRecord
	 * @covers Evoke\Model\Data\RecordList::selectRecord	 
	 */
	public function testFirstRecordSelected()
	{
		$rawData = [['ID' => 1, 'Text' => 'First'],
		            ['ID' => 2, 'Text' => 'Second']];
		$dIndex = 0;
		$dataMock = $this->getMock('Evoke\Model\Data\DataIface');

		$dataMock
			->expects($this->at($dIndex++))
			->method('setData')
			->with($rawData);
		$dataMock
			->expects($this->at($dIndex++))
			->method('getRecord')
			->with()
			->will($this->returnValue($rawData[0]));

		$obj = new RecordList($dataMock);
		$obj->setData($rawData);
		$obj->selectRecord($rawData[0]);

		$this->assertTrue($obj->hasSelectedRecord(),
		                  'Should have selected record.');
		$this->assertTrue($obj->isSelectedRecord(),
		                  'Should be the selected record.');

		
	}

	/**
	 * @covers Evoke\Model\Data\RecordList::clearSelectedRecords
	 */
	public function testClearSelectedRecords()
	{
		$dataMock = $this->getMock('Evoke\Model\Data\DataIface');
		$obj = new RecordList($dataMock);

		$obj->selectRecord(['ID' => 1]);
		$obj->selectRecord(['ID' => 2]);
		$obj->clearSelectedRecords();

		$this->assertFalse($obj->hasSelectedRecord());
	}


	/**
	 * @covers Evoke\Model\Data\RecordList::clearSelectedRecord
	 * @covers Evoke\Model\Data\RecordList::hasSelectedRecord
	 * @covers Evoke\Model\Data\RecordList::selectRecord
	 */
	public function testClearSelectedRecord()
	{
		$dataMock = $this->getMock('Evoke\Model\Data\DataIface');
		$obj = new RecordList($dataMock);

		$obj->selectRecord(['ID' => 1]);
		$obj->selectRecord(['ID' => 2]);
		$obj->clearSelectedRecord(['ID' => 2]);

		$this->assertTrue($obj->hasSelectedRecord());
	}

	/**
	 * @covers Evoke\Model\Data\RecordList::clearSelectedRecord
	 * @covers Evoke\Model\Data\RecordList::hasSelectedRecord
	 */
	public function testClearSelectedRecordNotPreviouslyAddedIsOK()
	{
		$dataMock = $this->getMock('Evoke\Model\Data\DataIface');
		$obj = new RecordList($dataMock);

		$obj->selectRecord(['ID' => 1]);
		$obj->clearSelectedRecord(['ID' => 3]);

		$this->assertTrue($obj->hasSelectedRecord());

	}
}
// EOF