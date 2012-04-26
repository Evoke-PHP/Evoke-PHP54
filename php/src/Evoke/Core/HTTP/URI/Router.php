<?php
namespace Evoke\Core\HTTP\URI;

use Evoke\Iface\Core as ICore;

/// Receive the request and create the correct response for it.
class Router implements ICore\HTTP\URI\Router
{
	/** @property $provider
	 *  @object Provider
	 */
	protected $provider;

	/** @property $request
	 *  @object Request
	 */
	protected $request;

	/** @property $reponse
	 *  @object Response
	 */
	protected $reponse;

	/** @property $rules
	 *  @array of rules that the router uses to route.
	 */
	protected $rules = array();

	/** Create a HTTP URI Router that routes the request to a response.
	 *  @param Provider @object Provider for creating the response.
	 *  @param Request  @object Request object.
	 *  @param Response @object Response object.
	 */
	public function __construct(ICore\Provider      $provider,
	                            ICore\HTTP\Request  $request,
	                            ICore\HTTP\Response $response)
	{
		$this->provider      = $provider;
		$this->request       = $request;
		$this->response      = $response;
	}
	
	/******************/
	/* Public Methods */
	/******************/

	/** Add a rule to the router.
	 *  @param rule @object HTTP URI Rule object.
	 */
	public function addRule(ICore\HTTP\URI\Rule $rule)
	{
		$this->rules[] = $rule;
	}

	/** Create the object that will respond to the routed URI).
	 *  @return @object The object that will respond (generally a Controller).
	 */
	public function route()
	{
		// The classname starts as the request URI and is refined by the mappers
		// until it is able to create the correct classname.
		$classname = $this->request->getURI();
		$params = array();
      
		foreach ($this->rules as $rule)
		{
			if ($rule->isMatch($classname))
			{
				// Set the parameters before updating the classname.
				$params += $rule->getParams($classname);
				$classname = $rule->getClassname($classname);

				if ($rule->isAuthoritative())
				{
					break;
				}
			}
		}

		/** An exception will be thrown if an unknown class is atttempted to be
		 *  built that should be caught at a higher level.
		 */
		return $this->provider->make($classname,
		                             $params,
		                             $this->provider,
		                             $this->request,
		                             $this->response);
	}
}
// EOF
