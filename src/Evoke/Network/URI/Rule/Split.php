<?php
/**
 * Split
 *
 * @package Network\URI\Rule
 */
namespace Evoke\Network\URI\Rule;

use InvalidArgumentException;

/**
 * Split
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2014 Paul Young
 * @license   MIT
 * @package   Network\URI\Rule
 */
class Split extends Rule
{
	protected
		/**
		 * Controller
		 * @var string
		 */
		$controller,

		/**
		 * Parts
		 * @var string[]
		 */
		$parts,

		/**
		 * Prefix string to ignore for breakdown into parts. It must match the
		 * start of the URI for the rule to match.
		 * @var string
		 */
		$prefix,

		/**
		 * Pre-calculated length for usage throughout.
		 * @var int
		 */
		$prefixLen,

		/**
		 * Separator
		 * @var string
		 */
		$separator;

	/**
	 * Construct a Split object.
	 *
	 * @param string   The controller.
	 * @param string[] Parts.
	 * @param string   The prefix to match.
	 * @param string   Separator to use to split the parts.
	 * @param bool     Whether the rule is authoritative.
	 */
	public function __construct(/* String */ $controller,
	                            Array        $parts,
	                            /* String */ $prefix,
	                            /* String */ $separator,
	                            /* Bool   */ $authoritative = true)
	{
		parent::__construct($authoritative);

		if (empty($parts))
		{
			throw new InvalidArgumentException(
				'need parts as non-empty array.');
		}

		if (empty($separator))
		{
			throw new InvalidArgumentException(
				'need separator as non-empty string.');
		}

		$this->controller = $controller;
		$this->parts      = $parts;
		$this->prefix     = $prefix;
		$this->prefixLen  = strlen($prefix);
		$this->separator  = $separator;
	}

	/**
     * Get the controller.
     *
     * @return string The controller.
     */
    public function getController()
    {
	    return $this->controller;
    }

	/**
	 * Return the parameters for the URI.
	 *
	 * @return mixed[] The parameters found using the rule.
	 */
	public function getParams()
	{
		return array_combine(
			$this->parts,
			explode($this->separator, substr($this->uri, $this->prefixLen)));
	}

    /**
     * Check the uri to see if it matches.
     *
     * @return bool Whether the uri is matched.
     */
    public function isMatch()
    {
	    // The prefix matches AND we have the expected number of parts.
	    return strcmp($this->prefix,
	                  substr($this->uri, 0, $this->prefixLen)) === 0 &&
		    (count(explode($this->separator,
		                   substr($this->uri, $this->prefixLen))) ===
		     count($this->parts));
    }
}
// EOF