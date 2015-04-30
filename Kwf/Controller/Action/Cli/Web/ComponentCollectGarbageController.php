<?php
class Kwf_Controller_Action_Cli_Web_ComponentCollectGarbageController extends Kwf_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "recursively duplicate component data";
    }

    public static function getHelpOptions()
    {
        return array();
    }

    public function indexAction()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        $tables = array(
            'kwc_composite_list',
            'kwc_paragraphs',
            //'kwf_pages',
            'kwc_basic_image',
            'kwc_data',
            'kwc_basic_text',
            'kwc_basic_text_components',
            'kwc_box_select',
            'kwc_basic_cards',
        );
        foreach ($tables as $table) {
            $stmt = Kwf_Registry::get('db')
                ->query("SELECT component_id FROM $table GROUP BY component_id");
            $rows = $stmt->fetchAll();

            $adapter = new Zend_ProgressBar_Adapter_Console();
            $progressBar = new Zend_ProgressBar($adapter, 0, count($rows));

            foreach ($rows as $row) {
                $progressBar->next(1, $table);
                //echo $row['component_id']."\n";
                $c = Kwf_Component_Data_Root::getInstance()->getComponentByDbId($row['component_id'], array('ignoreVisible'=>true, 'limit'=>1));
                if (!$c) {
                    echo "garbage: $row[component_id]\n";
                    //TODO create backup somewhere before deleting (kwc_garbage table?)
                    Kwf_Registry::get('db')->query("DELETE FROM $table WHERE component_id=?", array($row['component_id']));
                }
            }
        }
    }
}
