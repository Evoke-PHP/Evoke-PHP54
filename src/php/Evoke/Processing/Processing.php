<?php
/**
 * Abstract Processing
 *
 * @package Processing
 */
namespace Evoke\Processing;

use InvalidArgumentException;

/**
 * The processing class handles the processing of request data ($_GET, $_POST,
 * $_FILE, etc.) using callbacks.
 *
 * Each request is received as an array.  We match the keys of the request to
 * the callback array to determine the processing that should be done.
 */
abstract class Processing implements ProcessingIface
{
	/**
	 * Associative array of request IDs to processing callback.
	 * @var mixed[]
	 */
	protected $callbacks;

	/**
	 * Whether a key is required to match for processing.
	 * @var bool
	 */
	protected $matchRequired;

	/**
	 * The request method that the processing handles.
	 * @var string
	 */
	protected $requestMethod;

	/**
	 * Whether only a single request type can be processed at a time.
	 * @var bool
	 */
	protected $uniqueMatch;

	/**
	 * Construct a Processing object.
	 *
	 * @param mixed[] Associative array of request IDs to processing callback.
	 * @param bool    Whether a match is required.
	 * @param bool    Whether a unique match is required.
	 * @param string  The request method that we are processing.
	 */
	public function __construct(Array              $callbacks,
	                            /* Bool   */       $matchRequired = true,
	                            /* Bool   */       $uniqueMatch   = true,
	                            /* String */       $requestMethod = NULL),
	{

		if (!is_string($requestMethod))
		{
			throw new InvalidArgumentException(
				__METHOD__ . ' requires requestMethod as string');
		}
		
		$this->callbacks     = $callbacks;
		$this->matchRequired = $matchRequired;
		$this->requestMethod = $requestMethod;
		$this->uniqueMatch   = $uniqueMatch;
	}

	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Process the request.
	 */
	public function process()
	{
		if ($this->getRequestMethod() !== strtoupper($this->requestMethod))
		{
			return;
		}
      
		$requestData = $this->getRequest();
		$requestKeyMatches = $this->getRequestMatches($requestData);

		if ($this->checkMatches($requestKeyMatches, $requestData) &&
		    !empty($requestKeyMatches))
		{
			$this->callRequests($requestKeyMatches, $requestData);
		}
	}

	/*********************/
	/* Protected Methods */
	/*********************/
   
	/**
	 * Notify the matches which should now be processed.
	 *
	 * @param mixed[] The callbacks that should be executed.
	 * @param mixed[] The data for the requests.
	 */
	protected function callRequests(Array $callbacks, Array $requestData)
	{
		foreach($callbacks as $requestKey => $callback)
		{
			// We do not need the request key to be passed to the processing.
			$data = $requestData;
			unset($data[$requestKey]);
			call_user_func($callback, $data);
		}
	}

	/**
	 * Check to ensure that the matches we have conform to the expectations for
	 * uniqueness and optionality.
	 *
	 * @param mixed[] The matches found in the request data.
	 * @param mixed   The request data.
	 * @return bool   Whether the matches were as expected.
	 */
	protected function checkMatches(Array $matches, $requestData)
	{
		if ($this->matchRequired && (count($matches) === 0))
		{
			trigger_error(
				'Match_Required for Request_Keys: ' .
				var_export(array_keys($this->requestKeys), true) .
				' with request data: ' . var_export($requestData, true),
				E_USER_WARNING);
			return false;
		}
		elseif ($this->uniqueMatch && count($matches) > 1)
		{
			trigger_error(
				'Unique_Match required for Request_Keys: ' .
				var_export(array_keys($this->requestKeys)) .
				' with request data: ' . var_export($requestData, true),
				E_USER_WARNING);
			return false;
		}

		return true;
	}

	/**
	 * Get the request keys that match the request data.
	 *
	 * @param mixed[] The request data.
	 */
	protected function getRequestMatches($data)
	{
		return array_intersect_key($this->callbacks, $data);
	}

	/**
	 * Get the request method.
	 *
	 * @return string The request method in uppercase.
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	protected function getRequestMethod()
	{
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
}
// EOF