<?php
namespace Evoke\View\Control;

use Evoke\Iface;

class Menu extends \Evoke\View
{
	/** @property data
	 *  @object Data
	 */
	protected $data;

	/** Construct a Menu object.
	 *  @param translator @object Translator.
	 *  @param data       @object Data.
	 */
	public function __construct(Iface\Translator      $translator,
	                            Iface\Model\Data\Menu $data)
	{
		parent::__construct($translator);
		
		$this->data = $data;
	}

	
	/******************/
	/* Public Methods */
	/******************/

	/** Set the menu with the menu items.
	 *  @param menuItems \array The menu items.
	 *  \return \array The menu element data.
	 */
	public function get(Array $params = array())
	{
		$menus = $this->data->getMenu();
		$menusElements = array();
		
		foreach ($menus as $menu)
		{
			$menusElements[] = array(
				'ul',
				array('class' => 'Menu ' . $menu['Name']),
				$this->buildMenu($menu['Items'][0]['Children']));
		}

		return (count($menusElements) > 1) ?
			array('div', array('class' => 'Menus'), $menusElements) :
			reset($menusElements);
	}
   
	/*******************/
	/* Private Methods */
	/*******************/

	private function buildMenu($data, $level = 0)
	{
		$lang = $this->translator->getLanguage();
		$menu = array();

		foreach ($data as $menuItem)
		{
			if (!empty($menuItem['Children']))
			{
				$menu[] = array(
					'li',
					array('class' => 'Level_' . $level),
					array(array('a',
					            array('href' => $menuItem['Href']),
					            $menuItem['Text_' . $lang]),
					      array('ul',
					            array(),
					            $this->buildMenu(
						            $menuItem['Children'], ++$level))));
			}
			else
			{
				$menu[] = array(
					'li',
					array('class' => 'Level_' . $level),
					array(array('a',
					            array('href' => $menuItem['Href']),
					            $menuItem['Text_' . $lang])));
			}
		}
      
		return $menu;
	}
}
// EOF