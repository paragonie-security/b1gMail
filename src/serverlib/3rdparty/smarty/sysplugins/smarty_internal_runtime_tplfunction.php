<?php

/**
 * TplFunction Runtime Methods callTemplateFunction
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 **/
class Smarty_Internal_Runtime_TplFunction
{


    /**
     * Register template functions defined by template
     *
     * @param \Smarty|\Smarty_Internal_Template|\Smarty_Internal_TemplateBase $obj
     * @param array                                                           $tplFunctions source information array of
     *                                                                                      template functions defined
     *                                                                                      in template
     * @param bool                                                            $override     if true replace existing
     *                                                                                      functions with same name
     */
    public function registerTplFunctions(Smarty_Internal_TemplateBase $obj, $tplFunctions, $override = true): void
    {
        $obj->tplFunctions =
            $override ? array_merge($obj->tplFunctions, $tplFunctions) : array_merge($tplFunctions, $obj->tplFunctions);
        // make sure that the template functions are known in parent templates
        if ($obj->_isSubTpl()) {
            $obj->smarty->ext->_tplFunction->registerTplFunctions($obj->parent, $tplFunctions, false);
        } else {
            $obj->smarty->tplFunctions = $override ? array_merge($obj->smarty->tplFunctions, $tplFunctions) :
                array_merge($tplFunctions, $obj->smarty->tplFunctions);
        }
    }

    /**
     * Return source parameter array for single or all template functions
     *
     * @param \Smarty_Internal_Template $tpl  template object
     * @param null|string               $name template function name
     *
     * @return array|bool|mixed
     */
    public function getTplFunction(Smarty_Internal_Template $tpl, $name = null)
    {
        if (isset($name)) {
            return isset($tpl->tplFunctions[ $name ]) ? $tpl->tplFunctions[ $name ] :
                (isset($tpl->smarty->tplFunctions[ $name ]) ? $tpl->smarty->tplFunctions[ $name ] : false);
        } else {
            return empty($tpl->tplFunctions) ? $tpl->smarty->tplFunctions : $tpl->tplFunctions;
        }
    }






}
