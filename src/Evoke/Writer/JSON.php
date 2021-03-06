<?php
/**
 * JSON Writer
 *
 * @package Writer
 */
namespace Evoke\Writer;

/**
 * JSON Writer
 *
 * A buffered writer for JSON.
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @license   MIT
 * @package   Writer
 */
class JSON extends Writer
{
    /******************/
    /* Public Methods */
    /******************/

    /**
     * Write the data in JSON format into the buffer.
     *
     * @param mixed[] $data PHP data to be encoded into the buffer as JSON.
     */
    public function write($data)
    {
        $this->buffer .= json_encode($data);
    }
}
// EOF
