<?php
abstract class Kwc_Abstract_Ajax_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['contentSender'] = 'Kwc_Abstract_Ajax_ContentSender';
        return $ret;
    }
}
