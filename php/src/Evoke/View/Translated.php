<?php
namespace Evoke\View;

use \Evoke\Core\Iface;

abstract class Translated extends Base
{
	/** @property $Translator
	 *  Translator \object
	 */
	protected $Translator;

	/** Construct the View.
	 *  @param Writer     \object The Writer object.
	 *  @param Translator \object The Translator object.
	 */
	public function __construct(Iface\Writer $Writer,
	                            Iface\Translator $Translator)
	{
		parent::__construct($Writer);
		
		$this->Translator = $Translator;
	}
}
// EOF