<?php
class Kwf_Component_Output_C1_Child_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component'] = array(
            'child' => 'Kwf_Component_Output_C1_ChildChild_Component'
        );
        $ret['plugins'] = array('Kwf_Component_Output_Plugin_Plugin', 'Kwf_Component_Output_Plugin_Plugin');
        return $ret;
    }

    public function hasContent()
    {
        return true;
    }
}
?>