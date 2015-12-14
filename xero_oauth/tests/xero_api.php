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
    
} elseif (isset($oauthSession['oauth_token']) && isset($_REQUEST)) {
    
    $XeroOAuth->config['access_token']        = $oauthSession['oauth_token'];
    $XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
    $XeroOAuth->config['session_handle']      = $oauthSession['oauth_session_handle'];
    
    if (isset($_REQUEST['invoice'])) {
        if (!isset($_REQUEST['method'])) {
            $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices', 'core'), array(
                'order' => 'Total DESC'
            ));
            if ($XeroOAuth->response['code'] == 200) {
                $invoices = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "There are " . count($invoices->Invoices[0]) . " invoices in this Xero organisation, the first one is: </br>";
                pr($invoices->Invoices[0]->Invoice);
                if ($_REQUEST['invoice'] == "pdf") {
                    $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoice/' . $invoices->Invoices[0]->Invoice->InvoiceID, 'core'), array(), "", 'pdf');
                    if ($XeroOAuth->response['code'] == 200) {
                        $myFile = $invoices->Invoices[0]->Invoice->InvoiceID . ".pdf";
                        $fh = fopen($myFile, 'w') or die("can't open file");
                        fwrite($fh, $XeroOAuth->response['response']);
                        fclose($fh);
                        echo "PDF copy downloaded, check your the directory of this script.</br>";
                    } else {
                        outputError($XeroOAuth);
                    }
                }
            } else {
                outputError($XeroOAuth);
            }
        } elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "put" && $_REQUEST['invoice'] == 1) {
            $xml      = "<Invoices>
                      <Invoice>
                        <Type>ACCREC</Type>
                        <Contact>
                          <Name>Martin Hudson</Name>
                        </Contact>
                        <Date>2013-05-13T00:00:00</Date>
                        <DueDate>2013-05-20T00:00:00</DueDate>
                        <LineAmountTypes>Exclusive</LineAmountTypes>
                        <LineItems>
                          <LineItem>
                            <Description>Monthly rental for property at 56a Wilkins Avenue</Description>
                            <Quantity>4.3400</Quantity>
                            <UnitAmount>395.00</UnitAmount>
                            <AccountCode>200</AccountCode>
                          </LineItem>
                        </LineItems>
                      </Invoice>
                    </Invoices>";
            $response = $XeroOAuth->request('PUT', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
            if ($XeroOAuth->response['code'] == 200) {
                $invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "" . count($invoice->Invoices[0]) . " invoice created in this Xero organisation.";
                if (count($invoice->Invoices[0]) > 0) {
                    echo "The first one is: </br>";
                    pr($invoice->Invoices[0]->Invoice);
                }
            } else {
                outputError($XeroOAuth);
            }
        } elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "post") {
            $xml      = "<Invoices>
                      <Invoice>
                        <Type>ACCREC</Type>
                        <Contact>
                          <Name>Martin Hudson</Name>
                        </Contact>
                        <Date>2013-05-13T00:00:00</Date>
                        <DueDate>2013-05-20T00:00:00</DueDate>
                        <LineAmountTypes>Exclusive</LineAmountTypes>
                        <LineItems>
                          <LineItem>
                            <Description>Monthly rental for property at 56a Wilkins Avenue</Description>
                            <Quantity>4.3400</Quantity>
                            <UnitAmount>395.00</UnitAmount>
                            <AccountCode>200</AccountCode>
                          </LineItem>
                       </LineItems>
                     </Invoice>
                   </Invoices>";
            $response = $XeroOAuth->request('POST', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
            if ($XeroOAuth->response['code'] == 200) {
                $invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
                echo "" . count($invoice->Invoices[0]) . " invoice created in this Xero organisation.";
                if (count($invoice->Invoices[0]) > 0) {
                    echo "The first one is: </br>";
                    pr($invoice->Invoices[0]->Invoice);
                }
            } else {
                outputError($XeroOAuth);
            }
        }
    }
}
