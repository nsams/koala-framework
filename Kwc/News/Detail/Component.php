<?php
class Kwc_News_Detail_Component extends Kwc_News_Detail_Abstract_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component']['image'] = 'Kwc_News_Detail_PreviewImage_Component';
        return $ret;
    }

    public static function modifyItemData(Kwf_Component_Data $new)
    {
        parent::modifyItemData($new);
        $new->previewImage = $new->getChildComponent('-image');
    }
}
