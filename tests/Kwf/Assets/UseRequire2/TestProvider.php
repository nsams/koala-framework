<?php
class Kwf_Assets_UseRequire2_TestProvider extends Kwf_Assets_Provider_Abstract
{
    public function getDependency($dependencyName)
    {
        if ($dependencyName == 'A') {
            return new Kwf_Assets_UseRequire2_TestDependency("A");
        } else if ($dependencyName == 'B') {
            return new Kwf_Assets_UseRequire2_TestDependency("B");
        } else if ($dependencyName == 'C') {
            return new Kwf_Assets_UseRequire2_TestDependency("C");
        } else if ($dependencyName == 'D') {
            return new Kwf_Assets_UseRequire2_TestDependency("D");
        }
    }
    /*
    Build the following dependency tree:
     A
     |
     |
  (uses)
     |
     B                       D
     |                       |
 (requires)              (requires)
     |                       |
     -------------|-----------
                  C

    Desired Order:
    BADC (A right after B), not BDCA (A last)
    */
    public function getDependenciesForDependency(Kwf_Assets_Dependency_Abstract $dependency)
    {
        if ($dependency->getContents('en') == 'A') {
            return array();
        } else if ($dependency->getContents('en') == 'B') {
            return array(
                Kwf_Assets_Dependency_Abstract::DEPENDENCY_TYPE_USES => array(
                    $this->_providerList->findDependency('A')
                )
            );
        } else if ($dependency->getContents('en') == 'C') {
            return array(
                Kwf_Assets_Dependency_Abstract::DEPENDENCY_TYPE_REQUIRES => array(
                    $this->_providerList->findDependency('B'),
                    $this->_providerList->findDependency('D'),
                )
            );
        } else if ($dependency->getContents('en') == 'D') {
            return array(
            );
        }
    }
}
