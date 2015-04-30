<?php
class Kwf_Component_Generator_DbIdRecursiveChildComponents_Test extends Kwc_TestAbstract
{
    public function setUp()
    {
        parent::setUp('Kwf_Component_Generator_DbIdRecursiveChildComponents_Root');
/*
    - Page                            root_page
        -* Detail with dbIdShortcut   root_page-1                  foo-1
          - Table                     root_page-1-table            foo-1-table
            -* Item                   root_page-1-table-1          foo-1-table-1
                - Page                root_page-1-table-1_page     foo-1-table-1_page
          - Cards                     root_page-2-cards            foo-2-cards
            - Card1                   root_page-2-cards-child      foo-2-cards-child
               - Page                 root_page-2-cards-child_page foo-2-cards-child_page
*/
    }

    public function testGenerators1()
    {
        $this->assertNotNull($this->_root->getComponentById('root_page-1-table-3_page'));
    }

    public function testGenerators2()
    {
        $this->assertNotNull($this->_root->getComponentById('root_page-2-cards-child_page'));
    }

    public function testRecCC1()
    {
        $p = $this->_root->getComponentById('root_page-2');
        $this->assertEquals(1, count($p->getRecursiveChildComponents(array(
            'componentClass' => 'Kwf_Component_Generator_DbIdRecursiveChildComponents_Detail_Table_Item_Component'
        ))));
    }

    public function testRecCC2()
    {
        $p = $this->_root->getComponentById('root_page-2');
        $this->assertEquals(1, count($p->getRecursiveChildComponents(array(
            'componentClass' => 'Kwf_Component_Generator_DbIdRecursiveChildComponents_Detail_Cards_Card_Component'
        ))));
    }

    public function testChildPagesFromDetail1()
    {
        $p = $this->_root->getComponentById('root_page-1');
                          //table + cards
        $this->assertEquals(2+0, count($p->getChildPages()));
    }

    public function testChildPagesFromDetail2()
    {
        $p = $this->_root->getComponentById('root_page-2');
        $this->assertEquals(1+1, count($p->getChildPages()));
    }

    public function testChildPagesFromDetail3()
    {
        $p = $this->_root->getComponentById('root_page-3');
        $this->assertEquals(0+0, count($p->getChildPages()));
    }

    public function testChildPagesFromPage()
    {
        $p = $this->_root->getComponentById('root_page');
        $this->assertEquals(3+1, count($p->getChildPages()));
    }
}
