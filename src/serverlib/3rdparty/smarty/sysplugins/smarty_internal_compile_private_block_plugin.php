<?php
/**
 * Smarty Internal Plugin Compile Block Plugin
 * Compiles code for the execution of block plugin
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Block Plugin Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Block_Plugin extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * nesting level
     *
     * @var int
     */
    public $nesting = 0;




}
