<?php
namespace Evoke\Controller;

use DomainException,
	Evoke\HTTP\RequestIface,
	Evoke\HTTP\ResponseIface,
	Evoke\Service\ProviderIface,
	Evoke\Writer\WriterIface;
	
/**
 * Abstract Controller
 *
 * Controllers are responsible for providing and using the correct objects
 * from the processing, model and view layers to execute the desired request
 * from the user.
 *
 * @author Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license MIT
 * @package Controller
 */
abstract class Controller
{
	/**
	 * Output format as an uppercase string (JSON, XHTML, etc.)
	 * @var string
	 */
	protected $outputFormat;
	
	/** 
	 * Setup for the page based output formats (XHTML, HTML5).
	 * @var mixed[]
	 */
	protected $pageSetup;

	/**
	 * Parameters for the Controller.
	 * @var mixed[]
	 */
	protected $params;
	
	/**
	 * Provider Object.
	 * @var Evoke\Service\ProviderIface
	 */
	protected $provider;

	/**
	 * Request Object.
	 * @var Evoke\HTTP\RequestIface
	 */
	protected $request;

	/**
	 * Response Object
	 * @var Evoke\HTTP\ResponseIface
	 */
	protected $response;

	/**
	 * Writer Object
	 * @var Evoke\Writer\WriterIface
	 */
	protected $writer;
	
	/**
	 * Construct the Controller.
	 *
	 * @param string                      The output format to use in uppercase.
	 * @param mixed[]					  Parameters.
	 * @param Evoke\Service\ProviderIface Provider object.
	 * @param Evoke\HTTP\RequestIface     Request object.
	 * @param Evoke\HTTP\ResponseIface 	  Response object.
	 * @param Evoke\Writer\WriterIface 	  Writer object.
	 * @param mixed[]					  Setup for page based output formats.
	 */
	public function __construct(/* String */  $outputFormat,
	                            Array         $params,
	                            ProviderIface $provider,
	                            RequestIface  $request,
	                            ResponseIface $response,
	                            WriterIface   $writer,
	                            Array         $pageSetup = array())
	{
		$this->outputFormat = $outputFormat;
		$this->pageSetup    = $pageSetup;
		$this->params  	    = $params;
		$this->provider	   	= $provider;
		$this->request 	   	= $request;
		$this->response	   	= $response;
		$this->writer  	    = $writer;
	}
	
	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Execute the controller.
	 */
	abstract public function execute();
}
// EOF