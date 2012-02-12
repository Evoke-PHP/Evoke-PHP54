<?php
namespace Evoke\Page;
/// The basic definition of a page.
abstract class Base
{
	protected $Factory;
	protected $InstanceManager;
	protected $setup;
   
	public function __construct(Array $setup)
	{
		$this->setup = array_merge(array('Factory'         => NULL,
		                                 'InstanceManager' => NULL),
		                           $setup);

		if (!$this->setup['Factory'] instanceof \Evoke\Core\Factory)
		{
			throw new \InvalidArgumentException(__METHOD__ . ' requires Factory');
		}
      
		if (!$this->setup['InstanceManager'] instanceof
		    \Evoke\Core\Iface\InstanceManager)
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires InstanceManager');
		}

		$this->Factory =& $this->setup['Factory'];
		$this->InstanceManager =& $this->setup['InstanceManager'];
	}

	/********************/
	/* Abstract Methods */
	/********************/

	/// Load the page.
	abstract public function load();
}
// EOF