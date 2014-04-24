<?php
/**
 * HTTP Media Type Match Rule
 *
 * @package Network\HTTP\MediaType\Rule
 */
namespace Evoke\Network\HTTP\MediaType\Rule;

/**
 * HTTP Media Type Match Rule
 *
 * A Media Type rule that matches exactly the media type from the accept header.
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2014 Paul Young
 * @license   MIT
 * @package   Network\HTTP\MediaType\Rule
 */
class Match extends Rule
{
	/**
	 * The match for the media type.
	 * @var mixed[]
	 */
	protected $match;

	/**
	 * Construct the Exact rule.
	 *
	 * @param string  The output format for the rule.
	 * @param mixed[] The match required from the media type.
	 */
	public function __construct(/* String */ $outputFormat,
	                            Array        $match)
	{
		parent::__construct($outputFormat, $match);

		$this->match = $match;
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
		return $this->mediaType === $this->match;
	}
}
// EOF
