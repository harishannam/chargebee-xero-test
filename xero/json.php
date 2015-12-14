<?php
// function defination to convert array to xml
function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
               $key = 'LineItem'; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

$json = '{
    "id": "ev_3Nl8FqWPSrbKv3K",
    "occurred_at": 1344411252,
    "source": "scheduled_job",
    "object": "event",
    "content": {"invoice": {
        "id": "1",
        "customer_id": "3Nl8FqWPSrbKOC1",
        "subscription_id": "3Nl8FqWPSrbKOC1",
        "recurring": true,
        "status": "paid",
        "price_type": "tax_exclusive",
        "start_date": 1341128051,
        "end_date": 1344411251,
        "amount": 995,
        "amount_due": 0,
        "paid_on": 1344411252,
        "object": "invoice",
        "first_invoice": true,
        "currency_code": "USD",
        "sub_total": 995,
        "tax": 0,
        "line_items": [
            {
                "date_from": 1344411251,
                "date_to": 1347089651,
                "unit_amount": 900,
                "quantity": 1,
                "is_taxed": false,
                "tax": 0,
                "object": "line_item",
                "amount": 900,
                "description": "Basic",
                "type": "charge",
                "entity_type": "plan",
                "entity_id": "basic"
            },
            {
                "date_from": 1344411251,
                "date_to": 1347089651,
                "unit_amount": 95,
                "quantity": 1,
                "is_taxed": false,
                "tax": 0,
                "object": "line_item",
                "amount": 95,
                "description": "Data Usage",
                "type": "charge",
                "entity_type": "addon",
                "entity_id": "data_usage"
            }
        ],
        "linked_transactions": [{
            "txn_id": "txn_3Nl8FqWPSrbKrxG",
            "applied_amount": 995,
            "applied_at": 1344411252,
            "txn_type": "payment",
            "txn_status": "success",
            "txn_date": 1344411252,
            "txn_amount": 995
        }],
        "linked_orders": [],
        "billing_address": {
            "first_name": "Benjamin",
            "last_name": "Ross",
            "object": "billing_address"
        }
    }},
    "event_type": "invoice_generated",
    "webhook_status": "not_configured"
}';
$decode = json_decode($json);
$content = $decode->content;
$inv = $content->invoice;
$customer = $inv->billing_address;
$inv_items = $inv->line_items;

$invoice['Type'] = 'ACCREC';
$invoice['InvoiceNumber'] = $decode->id;
$contact['Name'] = $customer->first_name.' '.$customer->last_name;
$contact['DefaultCurrency'] = $inv->currency_code;
$invoice['Contact'] = $contact;
$invoice['Date'] = date("Y-m-d\TH:i:s", ($inv->start_date));
$invoice['DueDate'] = date("Y-m-d\TH:i:s", ($inv->start_date));
$invoice['LineAmountTypes'] = 'Exclusive';
 date("Y-m-d\TH:i:s.000\Z", strtotime("2013-05-07 18:56:57"));


foreach ($inv_items as $inv_item) {
    $line_item['Description'] = $inv_item->description;
    $line_item['Quantity'] = $inv_item->quantity;
    $line_item['UnitAmount'] = $inv_item->unit_amount;
    $line_item['AccountCode'] = '200';
    $line_items[] = $line_item;
}
$invoice['LineItems'] = $line_items;
$invoice['Status'] = 'AUTHORISED';


$data['Invoice'] = $invoice;
header('Content-Type: text/xml');

$xml_data = new SimpleXMLElement('<?xml version="1.0"?><Invoices></Invoices>');
array_to_xml($data,$xml_data);
print_r($xml_data->asXML());

?>