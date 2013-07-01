<?php
/**
 * Exception Handler
 *
 * @package Service
 */
namespace Evoke\Service;

use Evoke\Netowrk\HTTP\ResponseIface,
	Evoke\View\ExceptionIface as ViewExceptionIface,
	Evoke\Writer\PageIface,
	InvalidArgumentException;

/**
 * Exception Handler
 *
 * The system exception handler.
 *
 * @author    Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license   MIT
 * @package   Service
 */
class ExceptionHandler
{
	/**
	 * Properties for the Exception Handler.
	 *
	 * @var bool            $showException  Whether to display the exception.
	 * @var ResponseIface   $response       Response object.
	 * @var ExceptionIface  $viewException  Exception view.
	 * @var MessageBoxIface $viewMessageBox MessageBox view.
	 * @var PageIface       $writer         Page Writer.
	 */
	protected $response, $showException, $viewException, $viewMessageBox, $writer;

	/**
	 * Construct an Exception Handler object.
	 *
	 * @param ResponseIface   Response object.
	 * @param bool            Whether to show the exception.
	 * @param MessageBoxIface MessageBox view.
	 * @param PageIface       Page Writer object.
	 * @param ExceptionIface  View of the exception (if shown).
	 */
	public function __construct(ResponseIface   $response,
	                            /* Bool */      $showException,
	                            MessageBoxIface $viewMessageBox,
	                            PageIface       $writer,
	                            ExceptionIface  $viewException = NULL)
	{
		if ($showException && !isset($viewException))
		{
			throw new InvalidArgumentException(
				'needs Exception view if we are showing the exception.');
		}
		
		$this->response       = $response;
		$this->showException  = $showException;
		$this->viewException  = $viewException;
		$this->viewMessageBox = $viewMessageBox;
		$this->writer         = $writer;
	}
   
	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Handle uncaught exceptions for the system by logging information and
	 * displaying a generic notice to the user so that they are informaed of an
	 * error without exposing information that could be used for an attack.
	 *
	 * @param \Exception An exception that was not caught in the system.
	 *
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public function handler(\Exception $uncaughtException)
	{
		trigger_error($uncaughtException->getMessage(), E_USER_ERROR);
		$currentBuffer = (string)($this->writer);
		
		if (!empty($currentBuffer))
		{
			trigger_error(
				'Bufffer needs to be flushed in exception handler for ' .
				'clean error page.  Buffer was: ' .	$currentBuffer,
				E_USER_WARNING);
			$this->writer->flush();
		}

		$this->viewMessageBox->addContent(
			array('div',
			      array('class' => 'Description'),
			      'The administrator has been notified.'));

		if ($this->showException)
		{
			$this->viewException->setException($uncaughtException);
			$this->viewMessageBox->addContent($this->viewException->get());
		}
		
		$this->writer->writeStart(
			array('CSS'   => array('/csslib/global.css'),
			      'Title' => '500 Internal Server Error'));
		$this->writer->write($this->viewMessageBox->get());
		$this->writer->writeEnd();
		
		$this->response->setStatus(500);
		$this->response->setBody((string)$this->writer);
		$this->response->send();
	}
}
// EOF