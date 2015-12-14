<?php
if (isset($_REQUEST)) {
    if (!isset($_REQUEST['where']))
        $_REQUEST['where'] = "";
}

if (isset($_REQUEST['wipe'])) {
    session_destroy();
    header("Location: {$here}");
    
    // already got some credentials stored?
} elseif (isset($_REQUEST['refresh'])) {
    $response = $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['oauth_session_handle']);
    if ($XeroOAuth->response['code'] == 200) {
        $session      = persistSession($response);
        $oauthSession = retrieveSession();
    } else {
        outputError($XeroOAuth);
        if ($XeroOAuth->response['helper'] == "TokenExpired")
            $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
    }
    
} elseif (isset($oauthSession['oauth_token'])) {
    $XeroOAuth->config['access_token']        = $oauthSession['oauth_token'];
    $XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
    $XeroOAuth->config['session_handle']      = $oauthSession['oauth_session_handle'];
    if(isset($_REQUEST['cbid']) && isset($event_type)){
        if ($event_type == "invoice_generated") {
            $response = $XeroOAuth->request('POST', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
            if ($XeroOAuth->response['code'] == 200) {
                $invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                $response = $XeroOAuth->request('PUT', $XeroOAuth->url('Payments', 'core'), array(), $xml_pymnts, 'JSON');
                if ($XeroOAuth->response['code'] == 200) {
                    $payment = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                } else {
                    outputError($XeroOAuth);
                }
            } else {
                outputError($XeroOAuth);
            }
            print_r("Success");
        }
    } if (isset($_REQUEST['cbwhid']) && !isset($_REQUEST['inv_id']) && !isset($_REQUEST['pay_id'])) {

        $html_pay = '<form method="GET" action="'.$PHP_SELF.'">';
        $html_pay .= '<input type="text" style="display:none" name="cbwhid" value="'.$_REQUEST['cbwhid'].'">';
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => 'Type=="REVENUE"'));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            $html_pay .= '<li> Select your Invoice Account <select name="inv_id">';
            foreach($accounts->Accounts->Account as $account){
                $acc_arr = xml2array($account);
                $html_pay .= '<option value="'.$acc_arr['Code'].'">'.$acc_arr['Name'].'</option>';
            }

            $html_pay .= '</select></li>'; 
        } else {
            outputError($XeroOAuth);
        }
        $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => 'Type=="BANK"'));
        if ($XeroOAuth->response['code'] == 200) {
            $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
            $html_pay .= '<li> Select your Payment Bank Account <select name="pay_id">';
            foreach($accounts->Accounts->Account as $account){
                $acc_arr = xml2array($account);
                $html_pay .= '<option value="'.$acc_arr['Code'].'">'.$acc_arr['Name'].'</option>';
            }
            $html_pay .= '</select></li>'; 
        } else {
            outputError($XeroOAuth);
        }
        $html_pay .= '<br /><input type="submit" value="submit" name="submit"></form>';
        echo $html_pay;
    }
}


function xml2array ( $xmlObject, $out = array () )
{
    foreach ( (array) $xmlObject as $index => $node )
        $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

    return $out;
}
