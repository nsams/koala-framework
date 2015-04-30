<?php
class Kwf_Controller_Action_Cli_Web_ComponentCollectGarbageController extends Kwf_Controller_Action
{
    public static function getHelp()
    {
        return "collect component garbage, execute once a day";
    }

    public function indexAction()
    {
        $model = Kwf_Component_Cache_Mysql::getInstance()->getModel();
        $includesModel = Kwf_Component_Cache_Mysql::getInstance()->getModel('includes');

        $s = new Kwf_Model_Select();
        $s->whereEquals('deleted', true);
        $s->where(new Kwf_Model_Select_Expr_Lower('microtime', (time()-3*24*60*60)*1000));
        $options = array(
            'columns' => array('component_id')
        );
        if ($this->_getParam('debug')) {
            echo "querying for garbage in cache_component...\n";
        }
        foreach ($model->export(Kwf_Model_Abstract::FORMAT_ARRAY, $s, $options) as $row) {
            if ($this->_getParam('debug')) {
                echo "deleting ".$row['component_id']."\n";
            }
            $s = new Kwf_Model_Select();
            $s->whereEquals('component_id', $row['component_id']);
            $model->deleteRows($s);

            $includesModel->deleteRows($s);
        }


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
        exit;
    }
}
