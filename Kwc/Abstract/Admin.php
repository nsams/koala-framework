<?php
class Kwc_Abstract_Admin extends Kwf_Component_Abstract_Admin
{
    public function getDuplicateProgressSteps($source)
    {
        $ret = 0;
        $s = array('ignoreVisible'=>true);
        foreach ($source->getChildComponents($s) as $c) {
            if ($c->generator->hasSetting('inherit') && $c->generator->getSetting('inherit')) {
                if ($c->generator->hasSetting('unique') && $c->generator->getSetting('unique')) {
                    continue;
                }
            }
            if ($c->generator->getGeneratorFlag('pageGenerator')) {
                continue;
            }
            $ret += $c->generator->getDuplicateProgressSteps($c);
        }
        return $ret;
    }

    public function duplicate($source, $target, Zend_ProgressBar $progressBar = null)
    {
        Kwf_Component_LogDuplicateModel::getInstance()->import(
            Kwf_Model_Abstract::FORMAT_ARRAY,
            array(
                array('source_component_id' => $source->dbId, 'target_component_id' => $target->dbId)
            )
        );

        if (($model = $source->getComponent()->getOwnModel()) && $source->dbId != $target->dbId) {
            $row = $model->getRow($source->dbId);
            if ($row) {
                $targetRow = $model->getRow($target->dbId);
                if ($targetRow) { $targetRow->delete(); }
                $newRow = $row->duplicate(array(
                    'component_id' => $target->dbId
                ));
            }
        }

        $s = array('ignoreVisible'=>true);
        foreach ($source->getChildComponents($s) as $c) {
            if ($c->generator->hasSetting('inherit') && $c->generator->getSetting('inherit') &&
                $c->generator->hasSetting('unique') && $c->generator->getSetting('unique') &&
                $source->componentId != $c->parent->componentId
            ) {
                continue;
            } else if (!$c->generator->hasSetting('inherit') &&
                !Kwf_Component_Generator_Abstract::hasInstance($target->componentClass, $c->generator->getGeneratorKey())
            ) {
                continue;
            } else if ($c->generator->getGeneratorFlag('pageGenerator')) {
                continue;
            }
            $c->generator->duplicateChild($c, $target, $progressBar);
        }
    }

    /**
     * Called when duplication of a number of components finished
     */
    public function afterDuplicate($rootSource, $rootTarget)
    {
        parent::afterDuplicate($rootSource, $rootTarget);
    }

    public function makeVisible($source)
    {
        foreach ($source->getChildComponents(array('inherit' => false, 'ignoreVisible'=>true)) as $c) {
            $c->generator->makeChildrenVisible($c);
        }
    }

    public function getCardForms()
    {
        $ret = array();
        $title = Kwf_Trl::getInstance()->trlStaticExecute(Kwc_Abstract::getSetting($this->_class, 'componentName'));
        $title = str_replace('.', ' ', $title);
        $ret['form'] = array(
            'form' => Kwc_Abstract_Form::createComponentForm($this->_class, 'child'),
            'title' => $title,
        );
        return $ret;
    }

    public function getVisibleCardForms()
    {
        $ret = array('form');
        return $ret;
    }

    public function getPagePropertiesForm()
    {
        return null;
    }
}
