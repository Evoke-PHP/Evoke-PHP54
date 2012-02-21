<?php
namespace Evoke\Core\URI;

/// Receive the request and create the correct response for it.
class Router implements \Evoke\Core\Iface\URI\Router
{
	protected $mappings;
	protected $setup;
   
	public function __construct(Array $setup)
	{
		$this->setup = array_merge(array('Factory'         => NULL,
		                                 'Instance_Manager' => NULL,
		                                 'Response_Base'   => NULL),
		                           $setup);

		if (!$this->setup['Factory'] instanceof \Evoke\Core\Factory)
		{
			throw new \InvalidArgumentException(__METHOD__ . ' requires Factory');
		}
      
		if (!$this->setup['Instance_Manager'] instanceof
		    \Evoke\Core\Iface\InstanceManager)
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires InstanceManager');
		}
      
		if (!is_string($this->responseBase))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires Response_Base as string');
		}

		$this->mappings = array();
	}

	public function appendMapper(\Evoke\Core\Iface\URI\Mapper $map)
	{
		$this->mappings[] = $map;
	}

	public function createResponse()
	{
		$uri = $this->getURI();
		$params = array();
		$response = '';
      
		foreach ($this->mappings as $map)
		{
			if ($map->matches($uri))
			{
				$params = $map->getParams($uri);
				$response = $map->getResponse($uri);

				if ($map->isAuthoritative())
				{
					break;
				}
				else
				{
					// The response is an enhanced request if we are using chained
					// URI mappers.
					$uri = $response;
				}	    
			}
		}

		$response = $this->responseBase . $response;

		// Create the response object.
		try
		{
			return $this->setup['Instance_Manager']->create(
				$response,
				array_merge(
					array('Factory'         => $this->setup['Factory'],
					      'Instance_Manager' => $this->setup['Instance_Manager']),
					$params));
		}
		catch (\Exception $e)
		{
			throw new \Exception(
				__METHOD__ . ' unable to create response due to: ' .
				$e->getMessage());
		}
	}
      
	public function prependMapper(\Evoke\Core\Iface\URI\Mapper $map)
	{
		array_unshift($this->mappings, $map);
	}

	/*********************/
	/* Protected Methods */
	/*********************/

	protected function getURI()
	{
		if (!isset($_SERVER['REQUEST_URI']))
		{
			throw new \RuntimeException(__METHOD__ . ' no REQUEST_URI.');
		}
      
		return $_SERVER['REQUEST_URI'];
	}
}
// EOF
