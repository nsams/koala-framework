<?php
class Kwc_Paragraphs_Generator extends Kwf_Component_Generator_Table
{
    public function getRecursiveDataResolver()
    {
        return 'Kwc_Paragraphs_RecursiveDataResolver';
    }
}
