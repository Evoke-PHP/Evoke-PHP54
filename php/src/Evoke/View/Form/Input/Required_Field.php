<?php
namespace Evoke\View\Form\Input;

class RequiredField extends \Evoke\View\ViewIface
{
	/** @property $translator
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

	public function get(Array $data)
	{
		return parent::set(
			array('div',
			      array('class' => 'Required_Field_Instructions'),
			      array(array('span',
			                  array('class' => 'Required_Field_Instructions_Text'),
			                  $this->translator->get(
				                  'Required_Field_Instructions')),
			            array('span',
			                  array('class' => 'Required'),
			                  '*'))));
			}
	}
	// EOF