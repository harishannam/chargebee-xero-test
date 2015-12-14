<?php
	if($_SERVER['HTTP_USER_AGENT'] == "ChargeBee"){
		$body = @file_get_contents('php://input');
		$body_arr = json_decode($body);
		$event_type = $body_arr->event_type;
		$content = $body_arr->content;
		//Process only if the event type is invoice generated
		if($event_type == "invoice_generated" && array_key_exists('invoice', $content)){
			$xml = cb_to_xml($body);
			$xml_pymnts = cb_to_xml_pymnts($body);
			// $file = fopen("cb_body.txt","w");
			// fwrite($file,$body);
			// fclose($file);
			// header('Content-Type: text/xml');
			include '../xero_oauth/public.php';
		}
	}
	// Convert any array to XML
	function array_to_xml( $data, $xml_data ) {
	    foreach( $data as $key => $value ) {
	        if( is_array($value) ) {
	            if(is_numeric($key)){
	    			if($xml_data->getName() == 'LineItems'){
	               		$key = 'LineItem';
	               	} elseif($xml_data->getName() == 'Payments'){
	               		$key = 'Payment';
	               	} 
	            }
	            $subnode = $xml_data->addChild($key);
	            array_to_xml($value, $subnode);
	        } else {
	            $xml_data->addChild("$key",htmlspecialchars("$value"));
	        }
	    }
	}

	// Convert ChargeBee request body to XML
	function cb_to_xml($json){
		$decode = json_decode($json);
		$content = $decode->content;
		$inv = $content->invoice;
		$customer = $inv->billing_address;
		$inv_payments = $inv->linked_transactions;
		$inv_items = $inv->line_items;
		$invoice['Type'] = 'ACCREC';
		$invoice['InvoiceNumber'] = "CB-INV-".$inv->id;
		$invoice['Reference'] = "ChargeBee Subscription";
		$contact['Name'] = $customer->first_name.' '.$customer->last_name;
		$contact['DefaultCurrency'] = $inv->currency_code;
		$invoice['Contact'] = $contact;
		$invoice['Date'] = date("Y-m-d\TH:i:s", ($inv->start_date));
		$invoice['DueDate'] = date("Y-m-d\TH:i:s", ($inv->start_date));
		$invoice['LineAmountTypes'] = 'Inclusive';
		foreach ($inv_items as $inv_item) {
		    $line_item['Description'] = $inv_item->description;
		    $line_item['Quantity'] = $inv_item->quantity;
		    $line_item['UnitAmount'] = $inv_item->unit_amount/100;
		    $line_item['AccountCode'] = $_GET['inv_id'];
		    $line_items[] = $line_item;
		}
		$invoice['LineItems'] = $line_items;
		$invoice['Status'] = 'AUTHORISED';
		$data['Invoice'] = $invoice;
		$xml_data = new SimpleXMLElement('<?xml version="1.0"?><Invoices></Invoices>');
		array_to_xml($data,$xml_data);
		return $xml_data->asXML();
	}

	// Convert Payment details to a Payment XML
	function cb_to_xml_pymnts($json){
		$decode = json_decode($json);
		$content = $decode->content;
		$inv = $content->invoice;
		$inv_payments = $inv->linked_transactions;
		foreach ($inv_payments as $inv_payment) {
		    $payment['Invoice']['InvoiceNumber'] = "CB-INV-".$inv->id;
		    $payment['Account']['Code'] = $_GET['pay_id'];
		    $payment['Date'] = date("Y-m-d\TH:i:s", ($inv_payment->txn_date));
		    $payment['Amount'] = $inv_payment->txn_amount/100;
		    $payments[] = $payment;
		}
		$xml_data = new SimpleXMLElement('<?xml version="1.0"?><Payments></Payments>');
		array_to_xml($payments,$xml_data);
		return $xml_data->asXML();
	}
?>