<?php
interface Kwf_User_Auth_Interface_Password
{
    public function getRowByIdentity($identity);
    public function validatePassword(Kwf_Model_Row_Interface $row, $password);
    public function setPassword(Kwf_Model_Row_Interface $row, $password);
    public function getActivationCode(Kwf_Model_Row_Interface $row);
    public function sendLostPasswordMail(Kwf_Model_Row_Interface $row, Kwf_User_Row $kwfUserRow);
}