<?php
/**
 * Smarty Internal Plugin Compile extend
 * Compiles the {extends} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile extend Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Extends extends Smarty_Internal_Compile_Shared_Inheritance
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('file');

    /**
     * Array of names of optional attribute required by tag
     * use array('_any') if there is no restriction of attributes names
     *
     * @var array
     */
    public $optional_attributes = array('extends_resource');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('file');







    /**
     * Create source code for {extends} from source components array
     *
     * @param \Smarty_Internal_Template $template
     *
     * @return string
     */
    public static function extendsSourceArrayCode(Smarty_Internal_Template $template)
    {
        $resources = array();
        foreach ($template->source->components as $source) {
            $resources[] = $source->resource;
        }
        return $template->smarty->left_delimiter . 'extends file=\'extends:' . join('|', $resources) .
               '\' extends_resource=true' . $template->smarty->right_delimiter;
    }
}
