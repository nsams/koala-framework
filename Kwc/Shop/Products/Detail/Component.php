<?php
class Kwc_Shop_Products_Detail_Component extends Kwc_Directories_Item_Detail_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component']['content'] = 'Kwc_Paragraphs_Component';
        $ret['generators']['child']['component']['image'] = 'Kwc_Basic_Image_Component';
        $ret['generators']['child']['component']['text'] = 'Kwc_Basic_Text_Component';
        $ret['generators']['addToCart'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Shop_AddToCart_Component'
        );
        $ret['cssClass'] = 'webStandard';
        $ret['placeholder']['back'] = trlKwfStatic('Back');
        $ret['assetsAdmin']['dep'][] = 'KwfFormDateTimeField';
        $ret['editComponents'] = array('content');
        return $ret;
    }

    public static function modifyItemData(Kwf_Component_Data $item)
    {
        $item->previewImage = $item->getChildComponent('-image');
        $item->previewText = $item->getChildComponent('-text');
        $item->currentPrice = $item->row->current_price;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['addToCart'] = $this->getData()->getChildComponent('-addToCart');
        $ret['currentPrice'] = $this->getData()->row->current_price;
        return $ret;
    }

    public function getAddToCartForm()
    {
        return $this->getData()->getChildComponent('-addToCart');
    }

    public function getProductRow()
    {
        return $this->getData()->row;
    }
}
