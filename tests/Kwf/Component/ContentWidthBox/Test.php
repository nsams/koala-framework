<?php
/**
 * @group Component_ContentWidth
 */
class Kwf_Component_ContentWidthBox_Test extends Kwc_TestAbstract
{
    public function setUp()
    {
        parent::setUp('Kwf_Component_ContentWidthBox_Root_Component');
    }

    public function testPageWithBox()
    {
        $c = $this->_root->getComponentById('root_page');
        $this->assertEquals(700, $c->getComponent()->getContentWidth());
    }

    public function testPageWithEmptyBox()
    {
        $c = $this->_root->getComponentById('root_pageWithEmptyBox');
        $this->assertEquals(800, $c->getComponent()->getContentWidth());
    }

    public function testPageWithEmptyBox2()
    {
        $c = $this->_root->getComponentById('root_page_pageWithEmptyBox');
        $this->assertEquals(800, $c->getComponent()->getContentWidth());
    }
}
