<?php
class HTZ_Vendor_Model_System_Config_Source_Enabled
{
	public function toOptionArray()
	{
		return array(
			array('value' => 0, 'label' => 'No'),
			array('value' => 1, 'label' => 'Yes'),
		);
	}
	
}