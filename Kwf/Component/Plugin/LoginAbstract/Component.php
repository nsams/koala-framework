<?php
abstract class Kwf_Component_Plugin_LoginAbstract_Component extends Kwf_Component_Plugin_Abstract
    implements Kwf_Component_Plugin_Interface_Login, Kwf_Component_Plugin_Interface_ViewReplace, Kwf_Component_Plugin_Interface_SkipProcessInput
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['loginForm'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => null
        );
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $templateVars = array();
        $templateVars['loginForm'] = Kwf_Component_Data_Root::getInstance()
            ->getComponentById($this->_getComponentId(), array('ignoreVisible' => true))->getChildComponent('-loginForm');
        $templateVars['wrongLogin'] = isset($_POST['login_password']);
        $templateVars['placeholder'] = Kwc_Abstract::getSetting(get_class($this), 'placeholder');
        return $templateVars;
    }

    public function replaceOutput($renderer)
    {
        if ($this->isLoggedIn()) {
            return false;
        }

        $template = Kwc_Admin::getComponentFile($this, 'Component', 'tpl');

        $renderer = new Kwf_Component_Renderer();
        $view = new Kwf_Component_View($renderer);
        $view->assign($this->getTemplateVars($renderer));
        return $renderer->render($view->render($template));
    }

    public function skipProcessInput()
    {
        return !$this->isLoggedIn();
    }

    protected function _getComponentId()
    {
        return $this->_componentId;
    }
}
