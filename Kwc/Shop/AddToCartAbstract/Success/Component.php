<?php
class Kwc_Shop_AddToCartAbstract_Success_Component extends Kwc_Form_Success_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['cart'] = Kwf_Component_Data_Root::getInstance()
            ->getComponentByClass(array('Kwc_Shop_Cart_Component', 'Kwc_Shop_Cart_Trl_Component'), array('ignoreVisible' => true, 'subroot'=>$this->getData()));
        return $ret;
    }
}
