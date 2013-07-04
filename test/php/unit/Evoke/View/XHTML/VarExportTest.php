<?php
namespace Evoke_Test\View\XHTML;

use Evoke\View\XHTML\VarExport,
	PHPUnit_Framework_TestCase;

class VarExportTest extends PHPUnit_Framework_TestCase
{
	/******************/
	/* Data Providers */
	/******************/

	public function providerVar()
	{
		return [
			'Integer' => [125],
			'Array'   => [['div', [], 'aiofw']],
			'String'  => ['str']];
	}
	
	/*********/
	/* Tests */
	/*********/

	/**
	 * We can set a variable and the view exports it.
	 *
	 * @covers       Evoke\View\XHTML\VarExport::get
	 * @covers       Evoke\View\XHTML\VarExport::setVar
	 * @dataProvider providerVar
	 */
	public function testVarExport()
	{
		$object = new VarExport;
		$object->setVar($value);
		$this->assertEquals(
			['div', ['class' => 'Var_Export'], var_export($value, true)],
			$object->get());
	}
}
// EOF
