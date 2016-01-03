<?php
class Kwc_Errors_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['accessDenied'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Errors_AccessDenied_Component'
        );
        $ret['generators']['client'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Errors_Client_Component'
        );
        $ret['generators']['notFound'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Errors_NotFound_Component'
        );
        return $ret;
    }
}
