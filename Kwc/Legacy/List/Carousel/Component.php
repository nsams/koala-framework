<?php
class Kwc_Legacy_List_Carousel_Component extends Kwc_Abstract_List_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Stage');
        $ret['generators']['child']['component'] = 'Kwc_Legacy_List_Carousel_Image_Component';
        $ret['assetsDefer']['files'][] = 'kwf/Kwc/Legacy/List/Carousel/Carousel.js';
        $ret['assetsDefer']['files'][] = 'kwf/Kwc/Legacy/List/Carousel/NextPreviousLinks.js';
        $ret['assetsDefer']['dep'][] = 'KwfList';
        return $ret;
    }
    
    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        if(count($ret['listItems']) == 2 || count($ret['listItems']) == 3) {
            // Necessary as carousel slider needs at least 4 items to work
            $ret['listItems'] = array_merge($ret['listItems'], $ret['listItems']);
        }
        $ret['config'] = array(
            'contentWidth' => $this->getContentWidth()
        );
        return $ret;
    }
}
