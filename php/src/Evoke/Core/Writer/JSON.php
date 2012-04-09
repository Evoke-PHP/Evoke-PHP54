<?php
namespace Evoke\Core\Writer;

/** Writer for JSON (buffered).
 */
class JSON extends Base
{	
	/******************/
	/* Public Methods */
	/******************/

	/** Write the JSON data into the buffer.
	 *  @param data \mixed PHP data to be converted to JSON for writing.
	 */
	public function write($data)
	{
		$this->buffer .= json_encode($data);
	}
}
// EOF