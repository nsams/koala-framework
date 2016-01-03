<?php
class Kwc_Shop_Cart_Checkout_Form_Success_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $row = $this->getData()->parent->getComponent()->getFormRow();
        $ret['payment'] = $this->getData()->parent->parent->getComponent()->getPayment($row);
        return $ret;
    }
}
