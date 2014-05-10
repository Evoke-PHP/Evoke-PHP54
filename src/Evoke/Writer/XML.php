<?php
/**
 * XML Writer
 *
 * @package Writer
 */
namespace Evoke\Writer;

use DomainException,
	InvalidArgumentException,
	XMLWriter;

/**
 * XML Writer
 *
 * Writer for XML elements.
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2014 Paul Young
 * @license   MIT
 * @package   Writer
 */
class XML implements WriterIface
{
	/**
	 * Protected Properties
	 *
	 * @var string    $docType   Document type.
	 * @var bool      $indent    Whether we indent non-inline elements.
	 * @var string    $language  Language of XML being written.
	 * @var mixed[]   $pos       Position of the tag, attribs and children in
	 *                           the element.
	 * @var XMLWriter $xmlWriter XML Writer object.
	 */
	protected $docType, $indent, $language, $pos, $xmlWriter;
	
	/**
	 * Create an XML Writer.
	 *
	 * @param XMLWriter XMLWriter object.
	 * @param string    Document type.
	 * @param string    Language.
	 * @param bool      Whether the XML produced should be indented.
	 * @param string    The string that should be used to indent the XML.
	 * @param int[]     Position of the tag, attribs & children in the element.
	 */
	public function __construct(
		XMLWriter    $xmlWriter,
		/* String */ $docType      = 'XHTML_1_1',
		/* String */ $language     = 'EN',
		/* Bool */   $indent       = TRUE,
		/* String */ $indentString = '   ',
		/* int[]  */ $pos          = array('Attribs'  => 1,
		                                   'Children' => 2,
		                                   'Tag'      => 0))
	{
		$this->docType   = $docType;
		$this->indent    = $indent;
		$this->language  = $language;
		$this->pos       = $pos;
		$this->xmlWriter = $xmlWriter;

		$this->xmlWriter->openMemory();
			
		if ($indent)
		{
			$this->xmlWriter->setIndentString($indentString);
			$this->xmlWriter->setIndent(true);
		}
	}
	
	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Get the XHTML that has been written into the memory buffer (without
	 * resetting it).
	 *
	 * @return string The XHTML from the buffer as a string.
	 */
	public function __toString()
	{
		return $this->xmlWriter->outputMemory(FALSE);
	}

	/**
	 * Reset the buffer that we are writing to.
	 */
	public function clean()
	{
		$this->xmlWriter->outputMemory(TRUE);
	}
	
	/**
	 * Flush the memory buffer containing the XHTML that has been written.
	 */
	public function flush()
	{
		echo $this->xmlWriter->outputMemory(TRUE);
	}
	
	/**
	 * Write XML elements into the memory buffer.
	 *
	 * @param mixed[] Array accessible value for the xml to be written of the
	 *                form: `array($tag, $attributes, $children)`
	 *
	 * An example of this is below with the default values that are used for the
	 * options array. Attributes and options are optional.
	 * <pre><code>
	 * array(0 => tag,
	 *       1 => array('attrib_1' => '1', 'attrib_2' => '2'),
	 *       2 => array($child, 'text', $anotherChild)
	 *      )
	 * </code></pre>
	 */
	public function write($xml)
	{
		if (empty($xml[$this->pos['Tag']]) ||
		    !is_string($xml[$this->pos['Tag']]))
		{
			throw new InvalidArgumentException(
				'bad tag: ' . var_export($xml, true));
		}

		if (isset($xml[$this->pos['Attribs']]) &&
		    !is_array($xml[$this->pos['Attribs']]))
		{
			throw new InvalidArgumentException(
				'bad attributes: ' . var_export($xml, true));
		}

		if (isset($xml[$this->pos['Children']]) &&
		    !is_array($xml[$this->pos['Children']]))
		{
			$xml[$this->pos['Children']]
				= array($xml[$this->pos['Children']]);
		}
			
		$tag      = $xml[$this->pos['Tag']];
		$attribs  = isset($xml[$this->pos['Attribs']]) ?
			$xml[$this->pos['Attribs']] : array();
		$children = isset($xml[$this->pos['Children']]) ?
			$xml[$this->pos['Children']] : array();

		// Whether we are normally indenting and we see an element that should
		// be inline.
		$specialInlineElement =
			($this->indent && preg_match('(^(strong|em|pre|code)$)i', $tag));

		// Toggle the indent off.
		if ($specialInlineElement)
		{
			$this->xmlWriter->setIndent(false);
		}
		
		$this->xmlWriter->startElement($tag);

		foreach ($attribs as $attrib => $value)
		{
			$this->xmlWriter->writeAttribute($attrib, $value);
		}

		foreach ($children as $child)
		{
			if (is_scalar($child))
			{
				$this->xmlWriter->text($child);
			}
			elseif (!is_null($child))
			{
				$this->write($child);
			}
		}

		// Some elements should always have a full end tag <div></div> rather
		// than <div/>
		if (preg_match('(^(div|script|textarea)$)i', $tag))
		{
			$this->xmlWriter->fullEndElement();
		}
		else
		{
			$this->xmlWriter->endElement();
		}

		if ($specialInlineElement)
		{
			// Toggle the indent back on.
			$this->xmlWriter->setIndent(true);
		}
	}

	/**
	 * Write the End of the document.
	 */
	public function writeEnd()
	{
		$this->xmlWriter->endDocument();
	}
	
	/**
	 * Write the start of the document based on the doc type.
	 */
	public function writeStart()
	{
		switch (strtoupper($this->docType))
		{
		case 'HTML5':
			$this->xmlWriter->startDTD('html');
			$this->xmlWriter->endDTD();
			$this->xmlWriter->startElement('html');
			break;

		case 'XML':
			$this->xmlWriter->startDocument('1.0', 'UTF-8');
			break;
				
		case 'XHTML_1_1':
			$this->xmlWriter->startDTD(
				'html',
				'-//W3C//DTD XHTML 1.1//EN',
				'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd');
			$this->xmlWriter->endDTD();

			$this->xmlWriter->startElementNS(
				null, 'html', 'http://www.w3.org/1999/xhtml');
			$this->xmlWriter->writeAttribute('lang', $this->language);
			$this->xmlWriter->writeAttribute('xml:lang', $this->language);
			break;
			
		default:
			throw new DomainException('Unknown docType');
		}
	}
}
// EOF