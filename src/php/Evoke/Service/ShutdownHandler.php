<?php
/**
 * Shutdown Handler
 *
 * @package Service
 */
namespace Evoke\Service;

use Evoke\Network\HTTP\ResponseIface,
	Evoke\View\ErrorIface,
	Evoke\View\MessageBoxIface,
	Evoke\Writer\WriterIface;

/**
 * Shutdown Handler
 *
 * The system shutdown handler called upon every shutdown if it is registered.
 *
 * @author    Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license   MIT
 * @package   Service
 */
class ShutdownHandler
{
	
	/** 
	 * Email address to be listed as a contact, or empty for no-one.
	 * @var string
	 */
	protected $email;
	
	/**
	 * Response object.
	 * @var ResponseIface
	 */
	protected $response;

	/**
	 * Whether to show the error.
	 * @var bool
	 */
	protected $showError;

	/**
	 * Error view.
	 * @var ErrorIface
	 */
	protected $viewError;

	/**
	 * MessageBox view.
	 * @var MessageBoxIface
	 */
	protected $viewMessageBox;
	
	/**
	 * Writer object.
	 * @var WriterIface
	 */
	protected $writer;

	/**
	 * Construct the System Shutdown handler.
	 *
	 * @param string          Email to use as a contact.
	 * @param ResponseIface   Response object.
	 * @param bool            Whether to show the error (You might not want to
	 *                        do this for security reasons).
	 * @param ErrorIface      View for the error.
	 * @param MessageBoxIface View for the message box.
	 * @param WriterIface     The writer object to write the fatal message.
	 */
	public function __construct(/* String */    $email,
	                            ResponseIface   $response,
	                            /* Bool   */    $showError,
	                            ErrorIface      $viewError,
	                            MessageBoxIface $viewMessageBox,
	                            WriterIface     $writer)
	{
		$this->email          = $email;
		$this->response       = $response;
		$this->showError      = $showError;
		$this->viewError      = $viewError;
		$this->viewMessageBox = $viewMessageBox;
		$this->writer         = $writer;
	}

	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Handle the shutdown of the system, recording any fatal errors.
	 */
	public function handler()
	{
		$err = error_get_last();

 
		if (!isset($err) ||
		    !in_array($err, array(E_USER_ERROR, E_ERROR, E_PARSE,
		                          E_CORE_ERROR, E_CORE_WARNING,
		                          E_COMPILE_ERROR, E_COMPILE_WARNING)))
		{
			return;
		}

		$this->viewMessageBox->setTitle('Fatal Error');
		$this->viewMessageBox->addContent(
			array('p',
			      array('class' => 'Description'),
			      'This is an error that we were unable to handle.  Please ' .
			      'tell us any information that could help us avoid this ' .
			      'error in the future.  Useful information such as the ' .
			      'date, time and what you were doing when the error ' .
			      'occurred should help us fix this.'));
		
		if (!empty($this->email))
		{
			$this->viewMessageBox->addContent(
				array('div',
				      array('class' => 'Contact'),
				      'Contact: ' . $this->email));
		}
 
		if ($this->showError)
		{
			$this->viewError->setError($err);
			$this->viewMessageBox->addContent($this->viewError->get());
		}

		
		$this->writer->write($this->viewMessageBox->get());
		$this->writer->writeEnd();

		$this->response->setStatus(500);
		$this->response->setBody((string)$this->writer);
		$this->response->send();
	}

	/**
	 * Register the shutdown handler.
	 */
	public function register()
	{
		register_shutdown_function(array($this, 'handler'));
	}
}
// EOF