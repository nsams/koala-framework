<?php
class Kwc_ArticlesCategory_Directory_Component extends Kwc_Articles_Directory_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['childModel'] = 'Kwc_ArticlesCategory_Directory_Model';
        $ret['generators']['child']['component']['view'] = 'Kwc_ArticlesCategory_Directory_View_Component';
        $ret['generators']['detail']['component'] = 'Kwc_ArticlesCategory_Detail_Component';
        $ret['generators']['categories'] = array(
            'class' => 'Kwf_Component_Generator_PseudoPage_Static',
            'component' => 'Kwc_ArticlesCategory_Category_Directory_Component',
            'name' => trlKwfStatic('Categories'),
            'showInMenu' => false
        );
        return $ret;
    }
}
