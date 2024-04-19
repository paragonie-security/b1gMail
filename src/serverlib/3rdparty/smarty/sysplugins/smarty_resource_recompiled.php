<?php
/**
 * Smarty Resource Plugin
 *
 * @package    Smarty
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 */

/**
 * Smarty Resource Plugin
 * Base implementation for resource plugins that don't compile cache
 *
 * @package    Smarty
 * @subpackage TemplateResources
 */
abstract class Smarty_Resource_Recompiled extends Smarty_Resource
{
    /**
     * Flag that it's an recompiled resource
     *
     * @var bool
     */
    public $recompiled = true;

    /**
     * Resource does implement populateCompiledFilepath() method
     *
     * @var bool
     */
    public $hasCompiledHandler = true;





    /*
       * Disable timestamp checks for recompiled resource.
       *
       * @return bool
       */
    /**
     * @return bool
     */
    public function checkTimestamps()
    {
        return false;
    }
}
