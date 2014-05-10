<?php
/**
 * HTTP Media Type Equivalent Rule
 *
 * @package Network\HTTP\MediaType\Rule
 */
namespace Evoke\Network\HTTP\MediaType\Rule;

use InvalidArgumentException;

/**
 * HTTP Media Type Equivalent Rule
 *
 * A rule that matches equivalent media types.
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2014 Paul Young
 * @license   MIT
 * @package   Network\HTTP\MediaType\Rule
 */
class Equivalent extends Match
{
	/**
	 * Fields that can be ignored in the match.
	 * @var string[]
	 */
	protected $ignoredFields;	

	/**
	 * Construct the Equivalent rule.
	 *
	 * @param string   The output format for the rule.
	 * @param mixed[]  The equivalent match required by the rule.
	 * @param string[] Fields that are to be ignored in the match.
	 */
	public function __construct(
		/* String */ $outputFormat,
		Array        $match,
		Array        $ignoredFields = array('Params', 'Q_Factor'))
	{
		parent::__construct($outputFormat, $match);
		
		$this->ignoredFields = $ignoredFields;
	}

	/******************/
	/* Public Methods */
	/******************/

	/**
	 * Check to see if the rule matches.
	 *
	 * @return bool Whether the rule matches.
	 */
	public function isMatch()
	{
		$mType = $this->mediaType;
		
		foreach ($this->ignoredFields as $ignored)
		{
			unset($mType[$ignored]);
		}

		// Only an equivalent test of == is used as we don't care about types.
		return $mType == $this->match;
	}
}
// EOF