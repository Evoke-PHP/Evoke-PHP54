<?php
namespace Evoke_Test\Model\Data;

use Evoke\Model\Data\Data,
	PHPUnit_Framework_TestCase;

class DataTest extends PHPUnit_Framework_TestCase
{
	/******************/
	/* Data Providers */
	/******************/

	public function providerCreate()
	{
		$metadata = $this->getMock('Evoke\Model\Data\Metadata\MetadataIface');
		$join = $this->getMock('Evoke\Model\Data\DataIface');

		return ['Simple'          => [$metadata],
		        'Two_Specified'   => [$metadata, ['J' => $join]],
		        'Fully_Specified' => [$metadata, ['J' => $join], 'Join_Key']];
	}

	public function providerGetJointData()
	{
		$simpleMetadata = $this->getMock(
			'Evoke\Model\Data\Metadata\MetadataIface');
		$simpleMetadata
			->expects($this->once())
			->method('getJoinID')
			->with('Join_Name')
			->will($this->returnValue('Found_Join_ID'));
		$simpleJoin = $this->getMock(
			'Evoke\Model\Data\DataIface');

		return ['Simple' =>
		        ['Metdata'   => $simpleMetadata,
		         'Joins'     =>
		         ['Found_Join_ID' => $simpleJoin,
		          'DC'            => $this->getMock(
			          'Evoke\Model\Data\Metadata\MetadataIface')],
		         'Join_Name' => 'Join_Name',
		         'Expected'  => $simpleJoin]];
	}

	/*********/
	/* Tests */
	/*********/

	/**
	 * @covers       Evoke\Model\Data\Data::__construct
	 * @dataProvider providerCreate
	 */
	public function testCreate()
	{
		$args = func_get_args();

		switch (count($args))
		{
		case 1:
			$obj = new Data($args[0]);
			break;
		case 2:
			$obj = new Data($args[0], $args[1]);
			break;
		case 3:
			$obj = new Data($args[0], $args[1], $args[2]);
			break;
		default:
			throw new \RuntimeException(
				'Test failed due to unexpected number of arguments.');
		}

		$this->assertInstanceOf('Evoke\Model\Data\Data', $obj);
	}

	/**
	 * @covers       Evoke\Model\Data\Data::__get
	 * @dataProvider providerGetJointData
	 */
	public function testGetJointData($metadata, $joins, $joinName, $expected)
	{
		$obj = new Data($metadata, $joins);
		$this->assertSame($expected, $obj->$joinName);
	}

	/**
	 * @covers                   Evoke\Model\Data\Data::__get
	 * @expectedException        OutOfBoundsException
	 * @expectedExceptionMessage no data container for join: join
	 */
	public function testGetJointDataException()
	{
		$metadata = $this->getMock('Evoke\Model\Data\Metadata\MetadataIface');
		$metadata
			->expects($this->once())
			->method('getJoinID')
			->with('join')
			->will($this->returnValue('NoMatch'));

		$obj = new Data($metadata);
		$obj->join;
	}

	/**
	 * @covers       Evoke\Model\Data\Data::setRecord
	 */
	public function testSetData()
	{
		$j1Data1 = [['J1_ID' => 1, 'Value' => '1'],
		            ['J1_ID' => 1, 'Value' => 'One']];
		$j1Data2 = [['J1_ID' => 2, 'Value' => '12'],
		            ['J1_ID' => 2, 'Value' => 'OneTwo']];

		$j2Data1 = [['J2_ID' => 1, 'Value' => '21'],
		            ['J2_ID' => 1, 'Value' => 'TwoOne']];
		$j2Data2 = [['J2_ID' => 2, 'Value' => '2'],
		            ['J2_ID' => 2, 'Value' => 'Two']];
		$j3Data = [['J3_ID' => 3, 'Text' => 'Three']];

		$data = [['Main_Record' => 'One',
		          'Joint_Data'  => [
			          'J1' => $j1Data1,
			          'J2' => $j2Data1]],
		         ['Main_Record' => 'Two',
		          'Joint_Data'  => [
			          'J1' => $j1Data2,
			          'J2' => $j2Data2,
			          'J3' => $j3Data]]];

		$j1 = $this->getMock('Evoke\Model\Data\DataIface');
		$j1
			->expects($this->at(0))
			->method('setData')
			->with($j1Data1);
		$j1
			->expects($this->at(1))
			->method('setData')
			->with($j1Data2);

		$j2 = $this->getMock('Evoke\Model\Data\DataIface');
		$j2
			->expects($this->at(0))
			->method('setData')
			->with($j2Data1);
		$j2
			->expects($this->at(1))
			->method('setData')
			->with($j2Data2);

		$j3 = $this->getMock('Evoke\Model\Data\DataIface');
		$j3
			->expects($this->at(0))
			->method('setData')
			->with([]);
		$j3
			->expects($this->at(1))
			->method('setData')
			->with($j3Data);

		$obj = new Data(
			$this->getMock('Evoke\Model\Data\Metadata\MetadataIface'),
			['J1' => $j1, 'J2' => $j2, 'J3' => $j3]);
		$obj->setData($data);
		$obj->next();
	}
}
// EOF