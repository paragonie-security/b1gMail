<?php

/**
 * class for undefined variable object
 * This class defines an object for undefined variable handling
 *
 * @package    Smarty
 * @subpackage Template
 */
class Smarty_Undefined_Variable extends Smarty_Variable
{


    /**
     * Always returns an empty string.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
