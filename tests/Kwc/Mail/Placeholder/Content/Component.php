<?php
class Kwc_Mail_Placeholder_Content_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        if ($renderer && $renderer instanceof Kwf_Component_Renderer_Mail) {
            $user = $renderer->getRecipient();
            $ret['username'] = $user ? $user->getMailLastname() : 'noname';
        }
        return $ret;
    }
}
