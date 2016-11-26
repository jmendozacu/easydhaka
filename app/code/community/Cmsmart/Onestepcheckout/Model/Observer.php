<?php

class Cmsmart_Onestepcheckout_Model_Observer extends Mage_Core_Model_Abstract
{
    public function newsletter($observer)
    {
    	//echo "<pre>"; print_r('aa'); exit;
        $_session = Mage::getSingleton('core/session');

        $newsletterFlag = $_session->getIsSubscribed();
        if ($newsletterFlag == true) {

            $email = $observer->getEvent()->getOrder()->getCustomerEmail();

            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if ($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
                $subscriber->setImportMode(true)->subscribe($email);

                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                $subscriber->sendConfirmationSuccessEmail();
            }

        }

    }
    
    public function cmsmartPoll($pollId,$answerId){
        // $pollId     = intval($this->getRequest()->getParam('poll_id'));
        // $answerId   = intval($this->getRequest()->getParam('vote'));

        /** @var $poll Mage_Poll_Model_Poll */
        $poll = Mage::getModel('poll/poll')->load($pollId);

        /**
         * Check poll data
         */
        if ($poll->getId() && !$poll->getClosed() && !$poll->isVoted()) {
            $vote = Mage::getModel('poll/poll_vote')
                ->setPollAnswerId($answerId)
                ->setIpAddress(Mage::helper('core/http')->getRemoteAddr(true))
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());

            $poll->addVote($vote);
            Mage::getSingleton('core/session')->setJustVotedPoll($pollId);
            Mage::dispatchEvent(
                'poll_vote_add',
                array(
                    'poll'  => $poll,
                    'vote'  => $vote
                )
            );
        }
	}
    public function cmsmartdelivery($observer)
    {

    	$data_delivery = Mage::app()->getFrontController()->getRequest()->getParams();
		$this->cmsmartPoll($data_delivery['poll_id'],$data_delivery['poll_vote']);
		$order = $observer->getOrder();
		$datasave = array(); 	
		$deliverydate_date = substr($data_delivery['selected_date'], 0, 10); 
		$deliverydate_time = substr($data_delivery['selected_date'], 10); 
		$deliverydate_asa  = $data_delivery['deliverydate']; 
		$datasave["deliverydate_asa"]= $deliverydateAsa;
		$datasave["sales_order_id"]= $order->getId();
		$datasave["deliverydate_date"]= $deliverydate_date;
		$datasave["deliverydate_time"]= $deliverydate_time;
		Mage::helper('onestepcheckout')->setDeliverydate($datasave);
    }


    public function applyComment($observer)
    {
		$order = $observer->getData('order');
        $comment = Mage::getSingleton('core/session')->getOnestepcheckoutOrderComment();
        if (!Mage::helper('onestepcheckout')->isShowComment() || empty($comment)) {
            return;
        }
        try {
            $order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(true);
            $order->save();
            $order->sendOrderUpdateEmail(true, $comment);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}