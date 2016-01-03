<?php
class Kwc_Posts_Detail_Delete_Confirmed_Component extends Kwc_Posts_Success_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['placeholder']['success'] = trlKwfStatic('Comment was successfully deleted.');
        $ret['flags']['processInput'] = true;
        return $ret;
    }

    protected function _getTargetPage()
    {
        return $this->getData()->getParentPage()->getParentPage();
    }

    public function processInput($postData)
    {
        $actions = $this->getData()->parent->parent;
        if (!$actions->getComponent()->mayEditPost()) {
            throw new Kwf_Exception_AccessDenied();
        }
    }

    public function postProcessInput($postData)
    {
        $post = $this->getData()->parent->parent->parent;
        $post->row->delete();
    }
}
