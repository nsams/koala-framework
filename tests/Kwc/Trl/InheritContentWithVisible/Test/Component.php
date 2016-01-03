<?php
class Kwc_Trl_InheritContentWithVisible_Test_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['test2'] = array(
            'class' => 'Kwf_Component_Generator_Page_Static',
            'component' => 'Kwc_Trl_InheritContentWithVisible_Test_Test2_Component',
            'name' => 'test2'
        );
        return $ret;
    }
}
