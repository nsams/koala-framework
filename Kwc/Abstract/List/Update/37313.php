<?php
class Kwc_Abstract_List_Update_37313 extends Kwf_Update
{
    public function update()
    {
        $db = Kwf_Registry::get('db');
        $db->query("ALTER TABLE  `kwc_composite_list` ADD  `cache_visible_parents` TINYINT NOT NULL AFTER  `visible`");
        $db->query("ALTER TABLE  `kwc_composite_list` ADD INDEX (  `cache_visible_parents` )");
    }

    //must be in postUpdate so we can use components
    public function postUpdate()
    {
        //TODO: add progress
        $db = Kwf_Registry::get('db');
        $root = Kwf_Component_Data_Root::getInstance();
        $rows = $db->query("SELECT id, component_id FROM kwc_composite_list GROUP BY component_id")->fetchAll();
        foreach ($rows as $row) {
            $c = $root->getComponentByDbId($row['component_id'], array('ignoreVisible'=>true));
            if ($c) {
                if ($c->isVisible()) {
                    $db->query("UPDATE kwc_composite_list SET cache_visible_parents=1 WHERE component_id=?", array($row['id']));
                }
            }
        }
    }
}
