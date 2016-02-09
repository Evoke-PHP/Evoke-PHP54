<?php
/**
 * RuleTest
 *
 * @package   Evoke_Test\Network\URI\Processing\Rule
 */
namespace Evoke_Test\Network\URI\Processing\Rule;

use Evoke\Network\URI\Processing\Rule\Rule;

/**
 * Concrete Rule
 */
class Concrete extends Rule
{
    public function execute()
    {
        call_user_func_array($this->callable, $this->data);
    }

    public function isMatch()
    {
        return true;
    }
}

/**
 * RuleTest
 *
 * @covers Evoke\Network\URI\Processing\Rule\Rule
 */
class RuleTest extends \PHPUnit_Framework_TestCase
{
    /*********/
    /* Tests */
    /*********/

    public function testConstruct()
    {
        $obj = new Concrete([new \Evoke_Test\Network\URI\Processing\Rule\StubCallback, 'SetArgs']);

        $this->assertInstanceOf('Evoke\Network\URI\Processing\Rule\Rule', $obj);
    }

    public function testSetData()
    {
        $stubCallback = new StubCallback;
        $obj = new Concrete([$stubCallback, 'SetArgs']);
        $args = ['One' => 1, 'Two' => 2];
        $obj->setData($args);
        $obj->execute();

        $this->assertSame(array_values($args), $stubCallback->getArgs());
    }
}