<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_by_paytm extends CI_Controller {
    public function __construct() {
        parent::__construct();
        @session_start();

        //===================================================
        // Loads Paytm Authorized Files
        //===================================================
        // include APPPATH . 'third_party/stack_web_gateway_paytm_kit/config_paytm.php';
        //===================================================
		header("Pragma: no-cache");
		header("Cache-Control: no-cache");
		header("Expires: 0");

        $this->load->library('Stack_web_gateway_paytm_kit');
    }
    public function index()
    {
    }
    public function payby_paytm()
    {
    	if(!empty($_POST['payby_paytm'])){
    		$posted = $_POST;
    		
    		$paytmParams = array();
    		$paytmParams['ORDER_ID'] 		= $posted['ORDER_ID'];
    		$paytmParams['TXN_AMOUNT'] 		= $posted['TXN_AMOUNT'];
    		$paytmParams["CUST_ID"] 		= $posted['CUST_ID'];
    		$paytmParams["EMAIL"] 			= (!empty($email)) ? $email : "" ;

		    $paytmParams["MID"] 			= PAYTM_MERCHANT_MID;
		    $paytmParams["CHANNEL_ID"] 		= PAYTM_CHANNEL_ID;
		    $paytmParams["WEBSITE"] 		= PAYTM_MERCHANT_WEBSITE;
		    $paytmParams["CALLBACK_URL"] 	= PAYTM_CALLBACK_URL;
		    $paytmParams["INDUSTRY_TYPE_ID"]= PAYTM_INDUSTRY_TYPE_ID;
    		
		    $paytmChecksum = $this->stack_web_gateway_paytm_kit->getChecksumFromArray($paytmParams, PAYTM_MERCHANT_KEY);
		    $paytmParams["CHECKSUMHASH"] = $paytmChecksum;
		    
		    $transactionURL = PAYTM_TXN_URL;
    		// p($posted);
    		// p($paytmParams,1);

    		$data = array();
    		$data['paytmParams'] 	= $paytmParams;
    		$data['transactionURL'] = $transactionURL;
    		
    		$this->load->view('payby_paytm', $data);
    	}
    }

    public function paytm_response(){
    	$paytmChecksum 	= "";
		$paramList 		= array();
		$isValidChecksum= "FALSE";

		$paramList = $_POST;
		$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

		header("Pragma: no-cache");
		header("Cache-Control: no-cache");
		header("Expires: 0");

		//Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
		$isValidChecksum = $this->stack_web_gateway_paytm_kit->verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.


		if($isValidChecksum == "TRUE") {
			echo "<b>Checksum matched and following are the transaction details:</b>" . "<br/>";
			
			echo "<pre>";
			print_r($_POST);
			echo "<pre>";

			if ($_POST["STATUS"] == "TXN_SUCCESS") {
				echo "<b>Transaction status is success</b>" . "<br/>";
				//Process your transaction here as success transaction.
				//Verify amount & order id received from Payment gateway with your application's order id and amount.
			}
			else {
				echo "<b>Transaction status is failure</b>" . "<br/>";
			}

			if (isset($_POST) && count($_POST)>0 )
			{ 
				foreach($_POST as $paramName => $paramValue) {
						echo "<br/>" . $paramName . " = " . $paramValue;
				}
			}
			

		}
		else {
			echo "<b>Checksum mismatched.</b>";
			//Process transaction as suspicious.
		}
    }
}
?>