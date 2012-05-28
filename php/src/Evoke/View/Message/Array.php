<?php
namespace Evoke\View\Message;

class Array extends \Evoke\View
{
	/** @property attribs
	 *  @array Attribs
	 */
	protected $attribs;

	/** @property messageTree
	 *  @object MessageTree
	 */
	protected $messageTree;

	/** Construct a Message Array view.
	 *  @param translator  @object Translator.
	 *  @param messageTree @object MessageTree.
	 *  @param attribs     @array  Attribs.
	 */
	public function __construct(
		Iface\Translator  $translator,
		Iface\MessageTree $messageTree,
		Array             $attribs = array('class' => 'Message'))
	{
		parent::__construct($translator);
		
		$this->attribs     = $attribs;
		$this->messageTree = $messageTree;
	}

	/******************/
	/* Public Methods */
	/******************/

	public function get(Array $params = array())
	{
		$params += array('Start_Level' => 0);
		
		return array('div',
		             $this->attribs,
		             $this->buildElems($this->messageTree,
		                               $params['Start_Level']));
	}
   
	/*********************/
	/* Protected Methods */
	/*********************/

	/// Build the view of the MessageTree recursively.
	protected function buildElems(Iface\MessageTree $messageTree, $level)
	{
		if ($messageTree instanceof Array)
		{
			return $this->buildElems($messageTree->get(), $level);
		}
      
		$msgElems = array();

		if (is_array($messageTree))
		{
			$childLevel = $level + 1;
	 
			foreach ($messageTree as $msg)
			{
				$msgElems[] = array(
					'ul',
					array('class' => ' Level_' . $level),
					array(array_unshift($msg['Title'],
					                    $this->buildElems(
						                    $msg['Message'], $childLevel))));
			}
		} 
		else
		{
			$msgElems[] = array(
				'li',
				array('class' => ' Leaf Level_' . $level),
				$messageTree);
		}

		return $msgElems;
	}
}
// EOF