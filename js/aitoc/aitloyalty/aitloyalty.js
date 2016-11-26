
/**
 * Loyalty Program
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitloyalty
 * @version      2.3.20
 * @license:     n/a
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
function aitloyalty_ActionOnChange()
{
    var sValue = document.getElementById('rule_simple_action').value;
    if ('by_percent_surcharge' == sValue || 'by_fixed_surcharge' == sValue)
    {
        
        document.getElementById('rule_discount_step').value = 0;
        document.getElementById('rule_discount_step').disabled = true;
    } else 
    {
        
        document.getElementById('rule_discount_step').disabled = false;
    }
}
Event.observe(document.getElementById('rule_simple_action'), 'change', aitloyalty_ActionOnChange.bindAsEventListener());
Event.observe(window, 'load', aitloyalty_ActionOnChange.bindAsEventListener());


// new script

    function aitloyalty_ActionOnRuleDisplayChange()
    {
        var oTrCoupon   = $('rule_aitloyalty_customer_display_coupon').up(1);
        var oTrTitle    = $('aitloyalty_customer_display_title').up(1);
        var oInpReq     = $('aitloyalty_customer_display_titles_1');
            
        if ($('rule_aitloyalty_customer_display_enable').value == 1)
        {
            oTrCoupon.show();
            oTrTitle.show();
            
            oInpReq.removeClassName('ignore-validate');
        }
        else
        {
            oTrCoupon.hide();
            oTrTitle.hide();
            
            oInpReq.addClassName('ignore-validate');
        }
        return true;
    }