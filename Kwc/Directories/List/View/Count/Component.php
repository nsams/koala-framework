<?php
class Kwc_Directories_List_View_Count_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['cssClass'] = 'webStandard';
        $ret['placeholder']['total'] = trlKwfStatic('total').': ';
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['count'] = $this->getData()->parent->getComponent()->getPagingCount();
        if (!$ret['count']) $ret['count'] = 0;
        if ($ret['count'] instanceof Kwf_Model_Select) {
            throw new Kwf_Exception("Not yet implemented, probably not really possible");
        }
        $ret['totalLabel'] = $this->_getPlaceholder('total');
        return $ret;
    }

    public function getViewCacheSettings()
    {
        $ret = parent::getViewCacheSettings();
        $c = $this->getData()->parent;
        if ($c->getComponent()->getPartialClass($c->componentClass) == 'Kwf_Component_Partial_Id')
        {
            $ret['enabled'] = false;
        }
        return $ret;
    }
    
}
