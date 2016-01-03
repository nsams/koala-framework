<?php
class Kwc_Events_Detail_Component extends Kwc_News_Detail_Abstract_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['assetsAdmin']['dep'][] = 'KwfFormDateTimeField';
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = Kwc_Directories_Item_Detail_Component::getTemplateVars($renderer);
        $ret['title'] = $this->getData()->row->title;
        $ret['start_date'] = $this->getData()->row->start_date;
        $ret['end_date'] = $this->getData()->row->end_date;
        return $ret;
    }


    public static function modifyItemData(Kwf_Component_Data $new)
    {
        Kwc_Directories_Item_Detail_Component::modifyItemData($new);
        $new->start_date = $new->row->start_date;
        $new->end_date = $new->row->end_date;
    }
}
