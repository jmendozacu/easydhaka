;
var Cmsmart=Cmsmart||{};

Cmsmart.Onestepcheckout = {
		
		agreements : null,
		saveOrderStatus:false,
		
		initMessages: function(){
			jQuery('.close-message-wrapper, .onestepcheckout-messages-action .button').click(function(){
				jQuery('.onestepcheckout-message-wrapper').hide();
				jQuery('.onestepcheckout-message-container').empty();
			});
		},

		/** CREATE EVENT FOR SAVE ORDER **/
		initSaveOrder: function(){
			
			jQuery(document).on('click', '.onestepcheckout-btn-checkout', function(){
				if (Cmsmart.Onestepcheckout.Checkout.disabledSave==true){
					return;
				}
				var addressForm = new VarienForm('billing-new-address-form');
				if (!addressForm.validator.validate()){				
					return;
				}
				
				if (!jQuery('input[name="billing[use_for_shipping]"]').prop('checked')){
					var addressForm = new VarienForm('onestepcheckout-address-form-shipping');
					if (!addressForm.validator.validate()){				
						return;
					}
				}
				
				Cmsmart.Onestepcheckout.saveOrderStatus = true;
				Cmsmart.Onestepcheckout.Plugin.dispatch('saveOrderBefore');
				if (Cmsmart.Onestepcheckout.Checkout.isVirtual===false){
					Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder();
					Cmsmart.Onestepcheckout.Shipping.saveShippingMethod();
				}else{
					Cmsmart.Onestepcheckout.validatePayment();
				}
			});
			
		},
		
		
		
		/** INIT CHAGE PAYMENT METHOD **/
		initPayment: function(){
			Cmsmart.Onestepcheckout.bindChangePaymentFields();
			jQuery(document).on('click', '#co-payment-form input[type="radio"]', function(event){
				Cmsmart.Onestepcheckout.validatePayment();
			});
		},
		
		/** CHECK PAYMENT IF PAYMENT IF CHECKED AND ALL REQUIRED FIELD ARE FILLED PUSH TO SAVE **/
		validatePayment: function(){	
			
			var vp = payment.validate();
			if(!vp)
			{
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
				return false;
			}

			var paymentMethodForm = new Validation('co-payment-form', { onSubmit : false, stopOnFirst : false, focusOnError : false});
			  	
			if (paymentMethodForm.validate()){					
				Cmsmart.Onestepcheckout.savePayment();
			}else{
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				Cmsmart.Onestepcheckout.bindChangePaymentFields();
			}
			
			
		},
		
		/** BIND CHANGE PAYMENT FIELDS **/ 
		bindChangePaymentFields: function(){
			Cmsmart.Onestepcheckout.unbindChangePaymentFields();
			
			jQuery('#co-payment-form input').keyup(function(event){
				
				if (Cmsmart.Onestepcheckout.Checkout.ajaxProgress!=false){
					clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
				}
				
				Cmsmart.Onestepcheckout.Checkout.ajaxProgress = setTimeout(function(){
					Cmsmart.Onestepcheckout.validatePayment();
				}, 1000);
			});
			
			jQuery('#co-payment-form select').change(function(event){
				if (Cmsmart.Onestepcheckout.Checkout.ajaxProgress!=false){
					clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
				}
				
				Cmsmart.Onestepcheckout.Checkout.ajaxProgress = setTimeout(function(){
					Cmsmart.Onestepcheckout.validatePayment();
				}, 1000);
			});
		},
		
		/** UNBIND CHANGE PAYMENT FIELDS **/
		unbindChangePaymentFields: function(){
			jQuery('#co-payment-form input').unbind('keyup');
			jQuery('#co-payment-form select').unbind('change');
		},
		/*save*/
		/** SAVE PAYMENT **/		
		savePayment: function(){
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			if (Cmsmart.Onestepcheckout.Checkout.xhr!=null){
				Cmsmart.Onestepcheckout.Checkout.xhr.abort();
			}
			Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder();
			if (payment.currentMethod != 'stripe') {
				var form = jQuery('#co-payment-form').serializeArray();
				
				Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/savePayment',form, Cmsmart.Onestepcheckout.preparePaymentResponse,'json');
			}else{
			
				Stripe.createToken({
					
					name: $('stripe_cc_owner').value,
					number: $('stripe_cc_number').value,
					cvc: $('stripe_cc_cvc').value,
					exp_month: $('stripe_cc_expiration_month').value,
					exp_year: $('stripe_cc_expiration_year').value
				}, function(status, response) {
					if (response.error) {
						Cmsmart.Onestepcheckout.Checkout.hideLoader();
						Cmsmart.Onestepcheckout.Checkout.xhr = null;
						Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
						alert(response.error.message);
					} else {
						$('stripe_token').value = response['id'];
						var form = jQuery('#co-payment-form').serializeArray();
						Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/savePayment',form, Cmsmart.Onestepcheckout.preparePaymentResponse,'json');
					}
				});
			}
		},
		
		/** CHECK RESPONSE FROM AJAX AFTER SAVE PAYMENT METHOD **/
		preparePaymentResponse: function(response){
			Cmsmart.Onestepcheckout.Checkout.xhr = null;
			
			Cmsmart.Onestepcheckout.agreements = jQuery('#checkout-agreements').serializeArray();
			Cmsmart.Onestepcheckout.cmsmart_delivery = jQuery('#onestepcheckout-delivery').serializeArray();
			Cmsmart.Onestepcheckout.cmsmart_poll = jQuery('#onestepcheckout-poll').serializeArray();
			
			if (typeof(response.review)!= "undefined" && Cmsmart.Onestepcheckout.saveOrderStatus===false){					
				jQuery('#onestepcheckout-review-block').html(response.review);
				Cmsmart.Onestepcheckout.Checkout.removePrice();
			}
			
			if (typeof(response.error) != "undefined"){
				
				Cmsmart.Onestepcheckout.Plugin.dispatch('error');
				
				jQuery('.onestepcheckout-message-container').html(response.error);
				jQuery('.onestepcheckout-message-wrapper').show();
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				
				return;
			}
			
			Cmsmart.Onestepcheckout.Checkout.hideLoader();
			Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
			
			//SOME PAYMENT METHOD REDIRECT CUSTOMER TO PAYMENT GATEWAY
			if (typeof(response.redirect) != "undefined" && Cmsmart.Onestepcheckout.saveOrderStatus===true){
				Cmsmart.Onestepcheckout.Checkout.xhr = null;
				Cmsmart.Onestepcheckout.Plugin.dispatch('redirectPayment', response.redirect);
				if (Cmsmart.Onestepcheckout.Checkout.xhr==null){
					setLocation(response.redirect);
				}
				return;
			}
			
			if (Cmsmart.Onestepcheckout.saveOrderStatus===true){
				Cmsmart.Onestepcheckout.saveOrder();				
			}
			
			Cmsmart.Onestepcheckout.Plugin.dispatch('savePaymentAfter');
			
			
		}, 
		
		/** SAVE ORDER **/
		saveOrder: function(){
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			var form = jQuery('#co-payment-form').serializeArray();
			form  = Cmsmart.Onestepcheckout.checkAgreement(form);
			form  = Cmsmart.Onestepcheckout.cmsmartdelivery(form);
			form  = Cmsmart.Onestepcheckout.cmsmartpoll(form);
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder();				
			
			if (Cmsmart.Onestepcheckout.Checkout.config.comment!=="0"){
				Cmsmart.Onestepcheckout.saveCustomerComment();
			}
			
			Cmsmart.Onestepcheckout.Plugin.dispatch('saveOrder');
			Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.saveOrderUrl ,form, Cmsmart.Onestepcheckout.prepareOrderResponse,'json');
		},
		
		/** SAVE CUSTOMER COMMNET **/
		saveCustomerComment: function(){
			jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/comment',{"comment": jQuery('#customer_comment').val()});
		},
		
		/** ADD CMSMARTDELIVERY TO ORDER FORM **/
		cmsmartdelivery: function(form){
			jQuery.each(Cmsmart.Onestepcheckout.cmsmart_delivery, function(index, data){
				form.push(data);
			});
			return form;
		}, 
		/** ADD CMSMARTPOLL TO ORDER FORM **/
		cmsmartpoll: function(form){
			jQuery.each(Cmsmart.Onestepcheckout.cmsmart_poll, function(index, data){
				form.push(data);
			});
			return form;
		}, 
		
		/** ADD AGGREMENTS TO ORDER FORM **/
		checkAgreement: function(form){
			jQuery.each(Cmsmart.Onestepcheckout.agreements, function(index, data){
				form.push(data);
			});
			return form;
		},
		
		
		/** CHECK RESPONSE FROM AJAX AFTER SAVE ORDER **/
		prepareOrderResponse: function(response){
			if (typeof(response.error) != "undefined" && response.error!=false){
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();				
				
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				jQuery('.onestepcheckout-message-container').html(response.error);
				jQuery('.onestepcheckout-message-wrapper').show();
				Cmsmart.Onestepcheckout.Plugin.dispatch('error');
				return;
			}
			
			if (typeof(response.error_messages) != "undefined" && response.error_messages!=false){
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();				
				
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				jQuery('.onestepcheckout-message-container').html(response.error_messages);
				jQuery('.onestepcheckout-message-wrapper').show();
				Cmsmart.Onestepcheckout.Plugin.dispatch('error');
				return;
			}
			
		
			if (typeof(response.redirect) !="undefined"){
				if (response.redirect!==false){
					setLocation(response.redirect);
					return;
				}
			}
			
			if (typeof(response.update_section) != "undefined"){
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();				
				
				//create catch for default logic  - for not spam errors to console
				try{
					jQuery('#checkout-' + response.update_section.name + '-load').html(response.update_section.html);
				}catch(e){
					
				}
				
				Cmsmart.Onestepcheckout.prepareExtendPaymentForm();
				jQuery('#payflow-advanced-iframe').show();
				jQuery('#payflow-link-iframe').show();
				jQuery('#hss-iframe').show();
				
			}
			Cmsmart.Onestepcheckout.Checkout.hideLoader();
			Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();				
			
			Cmsmart.Onestepcheckout.Plugin.dispatch('responseSaveOrder', response);
		},
		
		
};



Cmsmart.Onestepcheckout.Checkout = {
		config:null,
		ajaxProgress:false,
		xhr: null,
		isVirtual: false,
		disabledSave: false,
		saveOrderUrl: null,
		
		init:function(){		
			
			if (this.config==null){
				return;
			}
			//base config
			this.config = jQuery.parseJSON(this.config);
			
			Cmsmart.Onestepcheckout.Checkout.saveOrderUrl = Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/saveOrder',
			this.success = Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'checkout/onepage/success',
			
			//DECORATE
			this.clearOnChange();
			this.removePrice();
			
			
			
			//MAIN FUNCTION
			Cmsmart.Onestepcheckout.Billing.init();
			Cmsmart.Onestepcheckout.Shipping.init();	
			Cmsmart.Onestepcheckout.initMessages();
			Cmsmart.Onestepcheckout.initSaveOrder();
			
			
			if (this.config.isLoggedIn===1){
				var addressId = jQuery('#billing-address-select').val();
				if (addressId!='' && addressId!=undefined ){
					Cmsmart.Onestepcheckout.Billing.save();
				}else{
					//FIX FOR MAGENTO 1.8 - NEED LOAD PAYTMENT METHOD BY AJAX
					Cmsmart.Onestepcheckout.Checkout.pullPayments();
				}
			}else{
				//FIX FOR MAGENTO 1.8 - NEED LOAD PAYTMENT METHOD BY AJAX
				Cmsmart.Onestepcheckout.Checkout.pullPayments();
			}
			
			
						
			Cmsmart.Onestepcheckout.initPayment();
		},
		
		 
		
		/** PARSE RESPONSE FROM AJAX SAVE BILLING AND SHIPPING METHOD **/
		prepareAddressResponse: function(response){ 
			Cmsmart.Onestepcheckout.Checkout.xhr = null;
			
			Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
			Cmsmart.Onestepcheckout.Checkout.hideLoader();

			if (typeof(response.error) != "undefined"){
				jQuery('.onestepcheckout-message-container').html(response.message);
				jQuery('.onestepcheckout-message-wrapper').show();
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				return;
			}
			
			/* Cmsmart ADDRESS VALIDATION  */
            if (typeof(response.address_validation) != "undefined"){
                jQuery('#checkout-address-validation-load').empty().html(response.address_validation);
                Cmsmart.Onestepcheckout.Checkout.hideLoader();
                return;
            }
			
			if (typeof(response.shipping) != "undefined"){
				jQuery('#shipping-block-methods').empty().html(response.shipping);
			}
			
			if (typeof(response.payments) != "undefined"){
				jQuery('#checkout-payment-method-load').empty().html(response.payments);
				payment.initWhatIsCvvListeners();//default logic for view "what is this?"
				
			}
			
			if (typeof(response.isVirtual) != "undefined"){
				
				Cmsmart.Onestepcheckout.Checkout.isVirtual = true;
			}
			
			if (Cmsmart.Onestepcheckout.Checkout.isVirtual===false){
				var update_payments = false;
				if (typeof(response.reload_payments) != "undefined")
					update_payments = true;
				
				Cmsmart.Onestepcheckout.Shipping.saveShippingMethod(update_payments);
				
			}else{
				jQuery('.shipping-block').hide();
				jQuery('.payment-block').addClass('clear-margin');
				Cmsmart.Onestepcheckout.Checkout.pullPayments();
			}
		},
		
		/** PARSE RESPONSE FROM AJAX SAVE SHIPPING METHOD **/
		prepareShippingMethodResponse: function(response){
			Cmsmart.Onestepcheckout.Checkout.xhr = null;
			Cmsmart.Onestepcheckout.Checkout.hideLoader();
			Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
			if (typeof(response.error)!="undefined"){
				
				Cmsmart.Onestepcheckout.Plugin.dispatch('error');
				
				jQuery('.onestepcheckout-message-container').html(response.message);
				jQuery('.onestepcheckout-message-wrapper').show();
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
				return;
			}
			
			if (typeof(response.review)!="undefined" && Cmsmart.Onestepcheckout.saveOrderStatus===false){
				try{
					jQuery('#onestepcheckout-review-block').html(response.review);
				}catch(e){
					
				}
				Cmsmart.Onestepcheckout.Checkout.removePrice();
			}
			
			
			
			//IF STATUS TRUE - START SAVE PAYMENT FOR CREATE ORDER
			if (Cmsmart.Onestepcheckout.saveOrderStatus==true){
				Cmsmart.Onestepcheckout.validatePayment();
			}else{
				Cmsmart.Onestepcheckout.Checkout.pullPayments();
			}
		},
		
		
		clearOnChange: function(){
			jQuery('.onestepcheckout-col-left input, .onestepcheckout-col-left select').removeAttr('onclick').removeAttr('onchange');
		},
		removePrice: function(){
			
			jQuery('.onestepcheckout-data-table tr th:nth-child(2)').remove();
			jQuery('.onestepcheckout-data-table tbody tr td:nth-child(3)').remove();
			jQuery('.onestepcheckout-data-table tfoot td').each(function(){
				var colspan = jQuery(this).attr('colspan');
				if (colspan!="" && colspan !=undefined){
					colspan = parseInt(colspan) - 1;
					jQuery(this).attr('colspan', colspan);
				}
			});
			jQuery('.onestepcheckout-data-table tfoot th').each(function(){
				var colspan = jQuery(this).attr('colspan');
				
				if (colspan!="" && colspan !=undefined){
					colspan = parseInt(colspan) - 1;
					jQuery(this).attr('colspan', colspan);
				}
			});
			
		},
		showLoader: function(){
			if (!jQuery.support.leadingWhitespace){
				jQuery('#floatingCirclesG').css('width', '100px');
				jQuery('#floatingCirclesG').css('height', '100px');
				jQuery('#floatingCirclesG div').css('display', 'none');
				jQuery('.onestepcheckout-ajax-loader').css('background', '#333');
				jQuery('.onestepcheckout-ajax-loader').css('-ms-filter', '"alpha(opacity=70)"');
				jQuery('.onestepcheckout-ajax-loader').css('zoom', '1');
				jQuery('.onestepcheckout-ajax-loader').css('opacity', '0.7');
				jQuery('.onestepcheckout-ajax-loader').show();
			}else{
				jQuery('#floatingCirclesG').css('background', 'none');
				jQuery('.onestepcheckout-ajax-loader').show();
			}
		},
		
		hideLoader: function(){
			setTimeout(function(){
				jQuery('.onestepcheckout-ajax-loader').hide();
			},1200);
		},
		
		/** APPLY SHIPPING METHOD FORM TO BILLING FORM **/
		applyShippingMethod: function(form){
			formShippimgMethods = jQuery('#onestepcheckout-co-shipping-method-form').serializeArray();
			jQuery.each(formShippimgMethods, function(index, data){
				form.push(data);
			});
			
			return form;
		},
		
		/** APPLY NEWSLETTER TO BILLING FORM **/
		applySubscribed: function(form){
			if (jQuery('#is_subscribed').length){
				if (jQuery('#is_subscribed').is(':checked')){
					form.push({"name":"is_subscribed", "value":"1"});
				}
			}
			
			return form;
		},
		
		/** PULL REVIEW **/
		pullReview: function(){
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder();
			Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/review',function(response){
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
				if (typeof(response.review)!="undefined"){
					jQuery('#onestepcheckout-review-block').html(response.review);
					Cmsmart.Onestepcheckout.Checkout.removePrice();
				}
			});
		},
		
		/** PULL PAYMENTS METHOD AFTER LOAD PAGE **/
		pullPayments: function(){
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder();
			Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/payments',function(response){
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();
				
				if (typeof(response.error)!="undefined"){
					jQuery('.onestepcheckout-message-container').html(response.error);
					jQuery('.onestepcheckout-message-wrapper').show();
					Cmsmart.Onestepcheckout.saveOrderStatus = false;
					return;
				}
				
				if (typeof(response.payments)!="undefined"){
					jQuery('#checkout-payment-method-load').html(response.payments);
					payment.initWhatIsCvvListeners();
					Cmsmart.Onestepcheckout.bindChangePaymentFields();
				};
				
				Cmsmart.Onestepcheckout.Checkout.pullReview();
				
			},'json');
		},
		
		lockPlaceOrder: function(mode){
			if(typeof(mode)=='undefined' || mode == undefined || !mode)
				mode = 0;
			if(mode == 0)
				jQuery('.onestepcheckout-btn-checkout').addClass('button-disabled');
			Cmsmart.Onestepcheckout.Checkout.disabledSave = true;
		},
		
		unlockPlaceOrder: function(){
			jQuery('.onestepcheckout-btn-checkout').removeClass('button-disabled');
			Cmsmart.Onestepcheckout.Checkout.disabledSave = false;
		}
	
};


Cmsmart.Onestepcheckout.Billing = {
		bill_need_update: true,
		
		init: function(){
			Cmsmart.Onestepcheckout.Billing.bill_need_update = true;
			//set flag use billing for shipping and init change flag
			this.setBillingForShipping(true);
			jQuery('input[name="billing[use_for_shipping]"]').change(function(){
				if (jQuery(this).is(':checked')){
					Cmsmart.Onestepcheckout.Billing.setBillingForShipping(true);
					jQuery('#onestepcheckout-address-form-billing select[name="billing[country_id]"]').change();
					Cmsmart.Onestepcheckout.Billing.validateForm();
				}else{
					Cmsmart.Onestepcheckout.Billing.setBillingForShipping(false);					
				}
			});
			
			
			//update password field
			jQuery('input[name="billing[create_account]"]').click(function(){
				if (jQuery(this).is(':checked')){
					jQuery('#register-customer-password').removeClass('hidden');
					jQuery('input[name="billing[customer_password]"]').addClass('required-entry');
					jQuery('input[name="billing[confirm_password]"]').addClass('required-entry');
				}else{
					jQuery('#register-customer-password').addClass('hidden');
					jQuery('input[name="billing[customer_password]"]').removeClass('required-entry');
					jQuery('input[name="billing[confirm_password]"]').removeClass('required-entry');
					jQuery('#register-customer-password input').val('');
				}
			});
			
			this.initChangeAddress();
			this.initChangeSelectAddress();
			
		},
		
		/** CREATE EVENT FOR UPDATE SHIPPING BLOCK **/
		initChangeAddress: function(){

			jQuery('#onestepcheckout-address-form-billing input').blur(function(){
				if(Cmsmart.Onestepcheckout.Billing.bill_need_update)
					Cmsmart.Onestepcheckout.Billing.validateForm();
			});

			jQuery('#onestepcheckout-address-form-billing').mouseleave(function(){
				if(Cmsmart.Onestepcheckout.Billing.bill_need_update)
					Cmsmart.Onestepcheckout.Billing.validateForm();
			});
			
			jQuery('#onestepcheckout-address-form-billing input').focus(function(){
				Cmsmart.Onestepcheckout.Billing.bill_need_update = true;
				setTimeout(function(){
					clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
				}, 150);
			});
			
			
			jQuery('#onestepcheckout-address-form-billing select').not('#billing-address-select').change(function(){
				Cmsmart.Onestepcheckout.Billing.bill_need_update = true;
				Cmsmart.Onestepcheckout.Billing.validateForm();
			});
			
		},
		
		validateForm: function(){
			setTimeout(function(){
				var valid = Cmsmart.Onestepcheckout.Billing.validateAddressForm();
				if (valid){
					Cmsmart.Onestepcheckout.Billing.save(); 
				}
			},100);
		},
		
		
		/** CREATE EVENT FOR CHANGE ADDRESS TO NEW OR FROM ADDRESS BOOK **/
		initChangeSelectAddress: function(){
			jQuery('#billing-address-select').change(function(){
				if (jQuery(this).val()==''){
					jQuery('#billing-new-address-form').show();
				}else{
					jQuery('#billing-new-address-form').hide();
					Cmsmart.Onestepcheckout.Billing.validateForm();
				}
			});
			
			
		},
		
		/** VALIDATE ADDRESS BEFORE SEND TO SAVE QUOTE**/
		validateAddressForm: function(form){
			
			  var addressForm = new Validation('onestepcheckout-address-form-billing', { onSubmit : false, stopOnFirst : false, focusOnError : false});
			  if (addressForm.validate()){				  		 
				  return true;
			  }else{				 
				  return false;
			  }
		},
		
		/** SET SHIPPING AS BILLING TO TRUE OR FALSE **/
		setBillingForShipping:function(useBilling){
			if (useBilling==true){
				jQuery('input[name="billing[use_for_shipping]"]').prop('checked', true);
				jQuery('input[name="shipping[same_as_billing]"]').prop('checked', true);
				jQuery('#onestepcheckout-address-form-shipping').addClass('hidden');				
			}else{
				this.pushBilingToShipping();	
				jQuery('input[name="billing[use_for_shipping]"]').prop('checked', false);
				jQuery('input[name="shipping[same_as_billing]"]').prop('checked', false);
				jQuery('#onestepcheckout-address-form-shipping').removeClass('hidden');
			}
			
		}, 
		
		/** COPY FIELD FROM BILLING FORM TO SHIPPING **/
		pushBilingToShipping:function(clearShippingForm){
			//pull country
			var valueCountry = jQuery('#billing-new-address-form select[name="billing[country_id]"]').val();
			jQuery('#onestepcheckout-address-form-shipping  select[name="shipping[country_id]"] [value="' + valueCountry + '"]').prop("selected", true);	
			shippingRegionUpdater.update();
			
			
			//pull region id
			var valueRegionId = jQuery('#billing-new-address-form select[name="billing[region_id]"]').val();
			jQuery('#onestepcheckout-address-form-shipping  select[name="shipping[region_id]"] [value="' + valueRegionId + '"]').prop("selected", true);
			
			//pull other fields	
			jQuery('#billing-new-address-form input').not(':hidden, :input[type="checkbox"]').each(function(){
				var name = jQuery(this).attr('name');
				var value = jQuery(this).val();
				var shippingName =  name.replace( /billing/ , 'shipping');
				
				jQuery('#onestepcheckout-address-form-shipping input[name="'+shippingName+'"]').val(value);

			});
			
			//pull address field
			jQuery('#billing-new-address-form input[name="billing[street][]"]').each(function(indexBilling){
				var valueAddress = jQuery(this).val();
				jQuery('#onestepcheckout-address-form-shipping input[name="shipping[street][]"]').each(function(indexShipping){
					if (indexBilling==indexShipping){
						jQuery(this).val(valueAddress);
					}
				});				
			});
			
			//init trigger change shipping form
			jQuery('#onestepcheckout-address-form-shipping select[name="shipping[country_id]"]').change();
		},

		/** METHOD CREATE AJAX REQUEST FOR UPDATE BILLING ADDRESS **/
		save: function(){
			if (Cmsmart.Onestepcheckout.Checkout.ajaxProgress!=false){
				clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
			}
			
			Cmsmart.Onestepcheckout.Checkout.ajaxProgress = setTimeout(function(){
					var form = jQuery('#onestepcheckout-address-form-billing').serializeArray();
					form = Cmsmart.Onestepcheckout.Checkout.applyShippingMethod(form);					
					form = Cmsmart.Onestepcheckout.Checkout.applySubscribed(form); 
					
					if (Cmsmart.Onestepcheckout.Checkout.xhr!=null){
						Cmsmart.Onestepcheckout.Checkout.xhr.abort();
					}
					
					if(jQuery('input[name="billing[use_for_shipping]"]').is(':checked'))
						Cmsmart.Onestepcheckout.Checkout.showLoader();
					else
						Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder(1);
					
					Cmsmart.Onestepcheckout.Billing.bill_need_update = false;		
					Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/saveBilling',form, Cmsmart.Onestepcheckout.Checkout.prepareAddressResponse,'json');
			}, 200);
		},
		
};

Cmsmart.Onestepcheckout.Shipping = {
		ship_need_update: true,
		
		init: function(){
			Cmsmart.Onestepcheckout.Shipping.ship_need_update = true;
			this.initChangeAddress();
			this.initChangeSelectAddress();
			this.initChangeShippingMethod();
		},

		/** CREATE EVENT FOR UPDATE SHIPPING BLOCK **/
		initChangeAddress: function(){
			
			jQuery('#onestepcheckout-address-form-shipping input').blur(function(){
				if(Cmsmart.Onestepcheckout.Shipping.ship_need_update)
					Cmsmart.Onestepcheckout.Shipping.validateForm();
			});

			jQuery('#onestepcheckout-address-form-shipping').mouseleave(function(){
				if(Cmsmart.Onestepcheckout.Shipping.ship_need_update)
					Cmsmart.Onestepcheckout.Shipping.validateForm();
			});
			
			jQuery('#onestepcheckout-address-form-shipping input').focus(function(){
				Cmsmart.Onestepcheckout.Shipping.ship_need_update = true;
				setTimeout(function(){
					clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
				}, 150);
			});
			
			
			jQuery('#onestepcheckout-address-form-shipping select').not('#shipping-address-select').change(function(){
				Cmsmart.Onestepcheckout.Shipping.ship_need_update = true;
				Cmsmart.Onestepcheckout.Shipping.validateForm();
			});
		},
		
		/** CREATE VENT FOR CHANGE ADDRESS TO NEW OR FROM ADDRESS BOOK **/
		initChangeSelectAddress: function(){
			jQuery('#shipping-address-select').change(function(){
				if (jQuery(this).val()==''){
					jQuery('#shipping-new-address-form').show();
				}else{
					jQuery('#shipping-new-address-form').hide();
					Cmsmart.Onestepcheckout.Shipping.validateForm();
				}
			});
			
			
		},
		
		//create observer for change shipping method. 
		initChangeShippingMethod: function(){
			jQuery('.onestepcheckout-wrapper-onestepcheckout #shipping-block-methods').on('change', 'input[type="radio"]', function(){
				Cmsmart.Onestepcheckout.Shipping.saveShippingMethod();
			});
		},
		
		validateForm: function(){
			setTimeout(function(){
				var valid = Cmsmart.Onestepcheckout.Shipping.validateAddressForm();
				if (valid){
					Cmsmart.Onestepcheckout.Shipping.save();
				}
			},100);
		},
		
		/** VALIDATE ADDRESS BEFORE SEND TO SAVE QUOTE**/
		validateAddressForm: function(form){
			
			  var addressForm = new Validation('onestepcheckout-address-form-shipping', { onSubmit : false, stopOnFirst : false, focusOnError : false});
			  if (addressForm.validate()){				  		 
				  return true;
			  }else{				 
				  return false;
			  }
		},
		
		/** METHOD CREATE AJAX REQUEST FOR UPDATE SHIPPIN METHOD **/
		save: function(){
			if (Cmsmart.Onestepcheckout.Checkout.ajaxProgress!=false){
				clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
			}
			
			Cmsmart.Onestepcheckout.Checkout.ajaxProgress = setTimeout(function(){
					var form = jQuery('#onestepcheckout-address-form-shipping').serializeArray();
					form = Cmsmart.Onestepcheckout.Checkout.applyShippingMethod(form);
					if (Cmsmart.Onestepcheckout.Checkout.xhr!=null){
						Cmsmart.Onestepcheckout.Checkout.xhr.abort();
					}
					Cmsmart.Onestepcheckout.Checkout.lockPlaceOrder(1);
					
					Cmsmart.Onestepcheckout.Shipping.ship_need_update = false;
					Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/saveShipping',form, Cmsmart.Onestepcheckout.Checkout.prepareAddressResponse,'json');
			}, 200);
		},
		
		saveShippingMethod: function(update_payments){
			
			if (Cmsmart.Onestepcheckout.Shipping.validateShippingMethod()===false){

				if (Cmsmart.Onestepcheckout.saveOrderStatus){	
					jQuery('.onestepcheckout-message-container').html('Please specify shipping method');
					jQuery('.onestepcheckout-message-wrapper').show();
				}
				Cmsmart.Onestepcheckout.saveOrderStatus = false;
					
				Cmsmart.Onestepcheckout.Checkout.hideLoader();
				
				if(typeof(update_payments) != 'undefined' && update_payments != undefined && update_payments) // if was request to reload payments
					Cmsmart.Onestepcheckout.Checkout.pullPayments();
				else
					Cmsmart.Onestepcheckout.Checkout.unlockPlaceOrder();	
				
				return;
			}
					
			
			if (Cmsmart.Onestepcheckout.Checkout.ajaxProgress!=false){
				clearTimeout(Cmsmart.Onestepcheckout.Checkout.ajaxProgress);
			}
			
			Cmsmart.Onestepcheckout.Checkout.ajaxProgress = setTimeout(function(){
				var form = jQuery('#onestepcheckout-co-shipping-method-form').serializeArray();
				form = Cmsmart.Onestepcheckout.Checkout.applySubscribed(form); 
				if (Cmsmart.Onestepcheckout.Checkout.xhr!=null){
					Cmsmart.Onestepcheckout.Checkout.xhr.abort();
				}
				Cmsmart.Onestepcheckout.Checkout.showLoader();
				Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/saveShippingMethod',form, Cmsmart.Onestepcheckout.Checkout.prepareShippingMethodResponse);
			}, 600);
		},
		
		validateShippingMethod: function(){			
			var shippingChecked = false;
			jQuery('#onestepcheckout-co-shipping-method-form input').each(function(){				
				if (jQuery(this).prop('checked')){							
					shippingChecked =  true;
				}
			});
			
			return shippingChecked;
		}		
};


Cmsmart.Onestepcheckout.Coupon = {
		init: function(){
			
			jQuery(document).on('click', '.apply-coupon', function(){
				Cmsmart.Onestepcheckout.Coupon.applyCoupon(false);
			});
			
			
			jQuery(document).on('click', '.remove-coupon', function(){
				Cmsmart.Onestepcheckout.Coupon.applyCoupon(true);
			});
			
			
			jQuery(document).on('click','.discount-block h3', function(){
				if (jQuery(this).hasClass('open-block')){
					jQuery(this).removeClass('open-block');
					jQuery('#onestepcheckout-discount-coupon-form').hide();
				}else{
					jQuery(this).addClass('open-block');
					jQuery('#onestepcheckout-discount-coupon-form').show();
				}
			});
			
		},
		
		applyCoupon: function(remove){
			
			var form = jQuery('#onestepcheckout-discount-coupon-form').serializeArray();
			if (remove===false){				
				form.push({"name":"remove", "value":"0"});
			}else{
				form.push({"name":"remove", "value":"1"});
			}
			
			Cmsmart.Onestepcheckout.Checkout.showLoader();
			Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/coupon/couponPost',form, Cmsmart.Onestepcheckout.Coupon.prepareResponse,'json');
		},
		
		prepareResponse: function(response){
			Cmsmart.Onestepcheckout.Checkout.hideLoader();
			if (typeof(response.message) != "undefined"){
				jQuery('.onestepcheckout-message-container').html(response.message);
				jQuery('.onestepcheckout-message-wrapper').show();
				
				Cmsmart.Onestepcheckout.Checkout.pullReview();
			}
			if (typeof(response.coupon) != "undefined"){
				jQuery('#onestepcheckout-discount-coupon-form').replaceWith(response.coupon);
			}
			if (typeof(response.payments)!="undefined"){
				jQuery('#checkout-payment-method-load').html(response.payments);
				payment.initWhatIsCvvListeners();
				Cmsmart.Onestepcheckout.bindChangePaymentFields();
			};			
		}
};

Cmsmart.Onestepcheckout.Agreement ={
		
		init: function(){
			
			jQuery(document).on('click', '.view-agreement', function(e){
				jQuery('.md-overlay').css('visibility', 'visible');
				if (!jQuery.support.leadingWhitespace){	
					jQuery('#modal-agreement').css('left', '41%');
				}
				e.preventDefault();
				jQuery('#modal-agreement').addClass('md-show');
				
				var id = jQuery(this).data('id');
				var title = jQuery(this).html();
				var content = jQuery('#agreement-block-'+id).html();
				
				jQuery('#agreement-title').html(title);
				jQuery('#agreement-modal-body').html(content);
			});
			
		}
};

Cmsmart.Onestepcheckout.Login ={
		
		init: function(){
			jQuery('.login-trigger').click(function(e){
				e.preventDefault();
				jQuery('.md-overlay').css('visibility', 'visible');
				jQuery('#modal-login').addClass('md-show');
				if (!jQuery.support.leadingWhitespace){
					jQuery('#modal-login').css('left', '42%');
				}
			});
			
			jQuery(document).on('click','.md-modal .close', function(e){
				e.preventDefault();
				jQuery('.md-overlay').css('visibility', 'hidden');
				jQuery('.md-modal').removeClass('md-show');
				jQuery('#modal-login').css({'left' : '', '42%' : ''});
			});
			
			jQuery(document).on('click', '.restore-account', function(e){
				e.preventDefault();
				jQuery('#login-form').hide();jQuery('#login-button-set').hide();
				jQuery('#form-validate-email').fadeIn();jQuery('#forgotpassword-button-set').show();
			});
			
			
			jQuery('#login-button-set .btn').click(function(){
				jQuery('#login-form').submit();
			});
			
			jQuery('#forgotpassword-button-set .btn').click(function(){
				var form = jQuery('#form-validate-email').serializeArray();
				Cmsmart.Onestepcheckout.Checkout.showLoader();
				Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/forgotpassword',form, Cmsmart.Onestepcheckout.Login.prepareResponse,'json');
			});
			
			
			jQuery('#forgotpassword-button-set .back-link').click(function(e){
				e.preventDefault();
				jQuery('#form-validate-email').hide();jQuery('#forgotpassword-button-set').hide();
				jQuery('#login-form').fadeIn();jQuery('#login-button-set').show();
				
			});
			
		},
		
		prepareResponse: function(response){
			Cmsmart.Onestepcheckout.Checkout.hideLoader();
			if (typeof(response.error)!="undefined"){
				alert(response.message);
			}else{
				alert(response.message);
				jQuery('#forgotpassword-button-set .back-link').click();
			}
		}
};

Cmsmart.Onestepcheckout.Geo = {
		init: function(){
			
			if (Cmsmart.Onestepcheckout.Checkout.config.geoCountry===false){			
				return;
			}else{
				//setup country for billing and than for shipping
				if (jQuery('#onestepcheckout-address-form-billing select[name="billing[country_id]"]').is(":visible")){					
					jQuery('#onestepcheckout-address-form-billing select[name="billing[country_id]"]').val(Cmsmart.Onestepcheckout.Checkout.config.geoCountry);
					billingRegionUpdater .update();
				}				
			}
				
			
			if (Cmsmart.Onestepcheckout.Checkout.config.geoCity===false){			
				return;
			}else{
				jQuery('#onestepcheckout-address-form-billing [name="billing[city]"]').val(Cmsmart.Onestepcheckout.Checkout.config.geoCity);														
			}
			
			
		}
};

Cmsmart.Onestepcheckout.Ajaxupdatecart = {
	updateCartProduct: function(){
		 var form = jQuery('#checkout-review-form').serializeArray();
		Cmsmart.Onestepcheckout.Checkout.showLoader();
		if (Cmsmart.Onestepcheckout.Checkout.xhr!=null){
			Cmsmart.Onestepcheckout.Checkout.xhr.abort();
		}		
		Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/updateProduct',form, Cmsmart.Onestepcheckout.Ajaxupdatecart.prepareResponseAjaxUpdate,'json');
		
	},
	removeProductId: function(idproduct){
		Cmsmart.Onestepcheckout.Checkout.showLoader();
		Cmsmart.Onestepcheckout.Checkout.xhr = jQuery.post(Cmsmart.Onestepcheckout.Checkout.config.baseUrl + 'onepage/json/removeProduct',{'_idproduct': idproduct }, Cmsmart.Onestepcheckout.Ajaxupdatecart.prepareResponseAjaxUpdate,'json');
	},
	prepareResponseAjaxUpdate: function(response){
		if(typeof(response.empty_cart)!="undefined"){
			if(response.empty_cart==1){
				window.location = Cmsmart.Onestepcheckout.Checkout.config.baseUrl+"checkout/cart/"
				jQuery('.onestepcheckout-message-container').html(response.message);
				jQuery('.onestepcheckout-message-wrapper').show();	
				Cmsmart.Onestepcheckout.Checkout.hideLoader();				
			}else{
				jQuery('.onestepcheckout-message-container').html(response.message);
				jQuery('.onestepcheckout-message-wrapper').show();
				// Cmsmart.Onestepcheckout.Shipping.init();
				// Cmsmart.Onestepcheckout.Billing.save();
				var addressForm = new Validation('onestepcheckout-address-form-billing', { onSubmit : false, stopOnFirst : false, focusOnError : false});
				if (addressForm.validate()){		
					Cmsmart.Onestepcheckout.Billing.save();
				}else{				 
					Cmsmart.Onestepcheckout.Checkout.pullPayments();
				}
				// Cmsmart.Onestepcheckout.Shipping.saveShippingMethod(true);
				// Cmsmart.Onestepcheckout.Shipping.saveShippingMethod();
				// Cmsmart.Onestepcheckout.Checkout.pullPayments();
				// Cmsmart.Onestepcheckout.Checkout.pullReview();
				// Cmsmart.Onestepcheckout.Checkout.hideLoader();
			}
		}
	}
}

jQuery(document).ready(function(){
	Cmsmart.Onestepcheckout.Checkout.init();
	Cmsmart.Onestepcheckout.Coupon.init(); 
	Cmsmart.Onestepcheckout.Agreement.init();
	Cmsmart.Onestepcheckout.Login.init();
	Cmsmart.Onestepcheckout.Geo.init();

});


//DUMMY FOR EE CHECKOUT
var checkout =  {
		steps : new Array("login", "billing", "shipping", "shipping_method", "payment", "review"),
		
		gotoSection: function(section){
			Cmsmart.Onestepcheckout.backToOnestepcheckout();
		},
		accordion:{
			
		}
};


Cmsmart.Onestepcheckout.prepareExtendPaymentForm =  function(){
	jQuery('.onestepcheckout-col-left').hide();
	jQuery('.onestepcheckout-col-center').hide();
	jQuery('.onestepcheckout-col-right').addClass('full-page');
	jQuery('#checkout-review-table-wrapper').hide();
	jQuery('#checkout-review-submit').hide();
	jQuery('.onestepcheckout-newsletter').hide();
	jQuery('.text-login').hide();
	
};

Cmsmart.Onestepcheckout.backToOnestepcheckout =  function(){
	jQuery('.onestepcheckout-col-left').show();
	jQuery('.onestepcheckout-col-center').show();
	jQuery('.onestepcheckout-col-right').removeClass('full-page');
	jQuery('#checkout-review-table-wrapper').show();
	jQuery('#checkout-review-submit').show();
	jQuery('#payflow-advanced-iframe').hide();
	jQuery('#payflow-link-iframe').hide();
	jQuery('#hss-iframe').hide();
	jQuery('.onestepcheckout-newsletter').show();
	jQuery('.text-login').show();
	Cmsmart.Onestepcheckout.saveOrderStatus = false;
	
};

Cmsmart.Onestepcheckout.Plugin = {
		
		observer: {},
		
		
		dispatch: function(event, data){
				
			
			if (typeof(Cmsmart.Onestepcheckout.Plugin.observer[event]) !="undefined"){
				
				var callback = Cmsmart.Onestepcheckout.Plugin.observer[event];
				callback(data);
				
			}
		},
		
		event: function(eventName, callback){
			Cmsmart.Onestepcheckout.Plugin.observer[eventName] = callback;
		}
};

