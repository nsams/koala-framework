<?php
class Kwc_Box_DogearRandom_Dogear_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Dogear');
        $ret['ownModel'] = 'Kwf_Component_FieldModel';

        $ret['generators']['child']['component']['image'] = 'Kwc_Box_DogearRandom_Dogear_Image_Component';
        $ret['generators']['child']['component']['imageSmall'] = 'Kwc_Box_DogearRandom_Dogear_ImageSmall_Component';
        $ret['generators']['child']['component']['linkExtern'] = 'Kwc_Basic_LinkTag_Extern_Component';

        $ret['assets']['dep'][] = 'SwfObject';
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);

        $options = array();
        $options['colors'] = array(
            'color_small_1' => $this->_getRow()->color_small_1,
            'color_small_2' => $this->_getRow()->color_small_2,
            'color_big_1' => $this->_getRow()->color_big_1,
            'color_big_2' => $this->_getRow()->color_big_2
        );

        // images
        $options['urlSmall'] = $ret['imageSmall']->getComponent()->getImageUrl();
        $options['urlBig'] = $ret['image']->getComponent()->getImageUrl();
        // link
        $vars = $ret['linkExtern']->getComponent()->getTemplateVars($renderer);
        $options['linkUrl'] = $vars['data']->url;
        $options['linkOpen'] = $vars['data']->rel;

        $ret['options'] = $options;

        return $ret;
    }
}
