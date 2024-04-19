<?php
/**
 * This file is part of Smarty.
 *
 * (c) 2015 Uwe Tews
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Smarty Internal Plugin Compile Child Class
 *
 * @author Uwe Tews <uwe.tews@googlemail.com>
 */
class Smarty_Internal_Compile_Child extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('assign');

    /**
     * Tag name
     *
     * @var string
     */
    public $tag = 'child';

    /**
     * Block type
     *
     * @var string
     */
    public $blockType = 'Child';


}
