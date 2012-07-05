<?php
namespace Evoke\View\Form\Input;

use Evoke\View\ViewIface;

/**
 * RequiredField
 *
 * @todo Fix to new view interface.
 *
 * @author Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license MIT
 * @package View
 */
class RequiredField implements ViewIface
{
	/**
	 *  Translator \object
	 */
	protected $translator;

	public function __construct(Array $setup)
	{
		/// @todo Fix to new View interface.
		throw new \RuntimeException('Fix to new view interface.');

		$setup += array('Translator' => NULL);

		if (!$translator instanceof \Evoke\Core\Translator)
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires Translator');
		}

		parent::__construct($setup);

		$this->translator = $translator;
	}

	/******************/
	/* Public Methods */
	/******************/

	public function get(Array $params = array())
	{
		return array(
			'div',
			array('class' => 'Required_Field_Instructions'),
			array(array('span',
			            array('class' => 'Required_Field_Instructions_Text'),
			            $this->translator->get(
				            'Required_Field_Instructions')),
			      array('span',
			            array('class' => 'Required'),
			            '*')));
	}
}
// EOF