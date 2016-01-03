<?php
class Kwf_Component_Output_Partial_Random_Component extends Kwc_Abstract
    implements Kwf_Component_Partial_Interface
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['contentWidth'] = 600;
        return $ret;
    }

    public static function getPartialClass($componentClass)
    {
        return 'Kwf_Component_Partial_Random';
    }

    public function getPartialVars($partial, $nr, $info)
    {
        return array('item' => 'bar' . $nr);
    }

    public function getPartialCacheVars($nr)
    {
        return array();
    }

    public function getPartialParams()
    {
        return array('count' => 3, 'limit' => 2);
    }
}
?>