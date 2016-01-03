<?php
class Kwc_Events_Archive_Component extends Kwc_Directories_List_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['useDirectorySelect'] = false;
        return $ret;
    }
    public function getSelect()
    {
        $ret = parent::getSelect();
        if (!$ret) return null;
        $ret->where('IF(ISNULL(end_date), start_date, end_date) < NOW()');
        $ret->order('start_date', 'DESC');
        return $ret;
    }

    public static function getItemDirectoryClasses($directoryClass)
    {
        $classes = self::_getParentItemDirectoryClasses($directoryClass, 0);
        $ret = array();
        foreach ($classes as $class) {
            $ret = array_merge($ret, call_user_func(array($class, 'getItemDirectoryClasses'), $class));
        }
        return $ret;
    }

    protected function _getItemDirectory()
    {
        return $this->getData()->parent->getComponent()->getItemDirectory();
    }

    public static function getViewCacheLifetimeForView()
    {
        return mktime(0, 0, 0, date('m'), date('d')+1, date('Y')) - time();
    }
}
