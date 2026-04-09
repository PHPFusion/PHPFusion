<?php	
	/**
	* 
	*/
	class eGHL_Hash 
	{
		protected $TransactionType = NULL;

		protected $ServiceID = NULL;
		protected $PaymentID = NULL;
		
		protected $TxnID = NULL;
		protected $TxnStatus = NULL;
		protected $AuthCode = NULL;
		protected $OrderNumber = NULL;

		protected $MerchantReturnURL	 = NULL;
		protected $MerchantApprovalURL	 = NULL;
		protected $MerchantUnApprovalURL = NULL;
		protected $MerchantCallBackURL	 = NULL;

		protected $Amount 		= NULL;
		protected $CurrencyCode = NULL;
		protected $CustIP		= NULL;

		protected $PageTimeout 	= NULL;
		protected $CardNo 		= NULL;
		protected $Token 		= NULL;

		protected $Param6 		= NULL;
		protected $Param7 		= NULL;

		/**
		 * hash value components specifiying if they are mandatory or not
		 *	array ( Param => is_mandatory )
		 */ 
		private function hashComponentList($type,$IsResponse=false)
		{
			if (is_null($type) && empty($type)) {
				echo get_class($this).':'.__LINE__.":TYPE MISSING ";
				return false;
			}
			
			if($IsResponse){
				if (strtoupper($type) == 'HASHVALUE') {
					return array(	
						'TxnID'	=>true,
						'ServiceID'	=>true,
						'PaymentID'	=>true,
						'TxnStatus'	=>true,
						'Amount'	=>true,
						'CurrencyCode'	=>true,
						'AuthCode'	=>true
					);
				}
				elseif (strtoupper($type) == 'HASHVALUE2') {
					return array(	
						'TxnID'	=>true,
						'ServiceID'	=>true,
						'PaymentID'	=>true,
						'TxnStatus'	=>true,
						'Amount'	=>true,
						'CurrencyCode'	=>true,
						'AuthCode'	=>true,
						'OrderNumber'	=>true,
						'Param6'	=> false,
						'Param7'	=> false
					);
				}
			}
			else{
				if (strtoupper($type) == 'SALE') {
					return array(	
						'ServiceID'	=>true,
						'PaymentID'	=>true,

						'MerchantReturnURL'		=> true,
						'MerchantApprovalURL' 	=> false,
						'MerchantUnApprovalURL' => false,
						'MerchantCallBackURL'	=> false,

						'Amount'		=> true,
						'CurrencyCode'	=> true,
						'CustIP'		=> true,

						'PageTimeout'	=> false,
						'CardNo'		=> false,
						'Token'			=> false
					);
				} elseif (in_array(strtoupper($type), array('QUERY', 'REVERSAL', 'CAPTURE', 'REFUND'))) {
					return array(	
							'ServiceID'	=>true,
							'PaymentID'	=>true,

							'Amount'		=> true,
							'CurrencyCode'	=> true,
					);
				} elseif (strtoupper($type) == 'SOP') {
					return array(	
						'ServiceID'	=> true,
						'PaymentID'	=> true,
						'CustIP'	=> true
						);
				} else {
					return false;
				}
			}
		}

		/** 
		 * populate all post variables from $_REQUEST
		 */
		private function getValuesFromRequest(){
			$exempted_attr = array('URL','hash_components','post_args');
			$args = get_object_vars($this);

			foreach ( $args as $ind=>$val){
				if (!in_array($ind,$exempted_attr)) {
					if(isset($_REQUEST[$ind])){
						$this->$ind = $_REQUEST[$ind];
					}
				}
			}
		}

		/// populate all variables from given PaymentInfo
		private function setValuesFromPaymentInfo($paymentInfo){
			if (!is_array($paymentInfo)) {
				return false;
			}

			$args = get_object_vars($this);

			foreach( $args as $ind=>$val){
				if(isset($paymentInfo[$ind])){
					$this->$ind = $paymentInfo[$ind];
				} 
			}

			$args = get_object_vars($this);
		}

		/// validate the mandatory hash components are present
		private function validateMandatoryComponent ($type, $IsResponse=false) {
			foreach($this->hashComponentList($type,$IsResponse) as $component=>$is_mandatory){
				if(is_null($this->$component) && $is_mandatory){
					echo 'A mandatory hash component "'.$component.'" is missing...<br/>';
					return false;
				}
			}

			return true;
		}

		/**
		 * calculate hashing (HashValue)
			must pass the merchant password as argument to this function 
		 */

		private function generateHashStringForPaymentInfo ($type, $mPassword, $IsResponse=false) {
			if (is_null($type) && empty($type)) {
				return false;
			}


			if (is_null($mPassword)) {
				return false;
			}

			if (!$this->validateMandatoryComponent($type,$IsResponse)) {
				return false;
			}

			$hash_str = NULL;

			foreach($this->hashComponentList($type,$IsResponse) as $component=>$is_mandatory){
				$hash_str .= $this->$component;
			}

			return $hash_str;
		}

		public function generateHashValueForPaymentInfo ($type, $mPassword, $IsResponse=false) {
			$hashValue = hash('sha256', $mPassword.$this->generateHashStringForPaymentInfo($type, $mPassword, $IsResponse));
			return $hashValue;

		}

		function __construct($paymentInfo)
		{
			if(is_null($paymentInfo)){
				echo "Payment Info Required";
			}
			else{
				$this->setValuesFromPaymentInfo($paymentInfo);
			}
		}
	}
?>