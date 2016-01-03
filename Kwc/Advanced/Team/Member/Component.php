<?php
class Kwc_Advanced_Team_Member_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Team member');

        $ret['generators']['child']['component']['image'] =
            'Kwc_Advanced_Team_Member_Image_Component';
        $ret['generators']['child']['component']['data'] =
            'Kwc_Advanced_Team_Member_Data_Component';
        return $ret;
    }
}
