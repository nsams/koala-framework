<?php
class Kwc_Blog_List_View_Component extends Kwc_Directories_List_View_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['placeholder']['readMore'] = trlKwfStatic('Read more').' &raquo;';
        return $ret;
    }
    public function getPartialVars($partial, $nr, $info)
    {
        $ret = parent::getPartialVars($partial, $nr, $info);
        $ret['content'] = $ret['item']->getChildComponent('-content');
        return $ret;
    }
}
