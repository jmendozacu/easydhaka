<?php
class Cmsmart_Onestepcheckout_Model_System_Config_Source_Stylelayout
{
    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray()
    {
        return array(
        	array('value' => 'style3', 'label' => 'Style 3'),
            array('value' => 'style2', 'label' => 'Style 2'),
            array('value' => 'style1', 'label' => 'Style 1'),
        );

    }
}
