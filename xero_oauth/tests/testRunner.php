<?php

function testLinks()
{
    echo '<p><ul>';
        if (XRO_APP_TYPE == 'Partner')   echo '<li><a href="?refresh=1">Refresh access token</a></li>';
        if (XRO_APP_TYPE !== 'Private' && isset($_SESSION['access_token'])) {
            echo '<li><a href="?wipe=1">Click to Clear session and start again</a></li>';
        } elseif(XRO_APP_TYPE !== 'Private' && !isset($_REQUEST ['authenticate']) && !isset($_REQUEST['cbwhid']) && !isset($_REQUEST['cbid'])) {
            echo '<li><a href="?authenticate=1"><img style="max-width:160px;" src="connect_xero_button_blue_2x.png" /></a></li>';
        }
        if(isset($_REQUEST['cbwhid']) && isset($_REQUEST['inv_id']) && isset($_REQUEST['pay_id'])){
            echo '<li>Your ChargeBee Webhook URL is - <input type="text" value="http://pg.chipap.com/chargebee/xero/cb_wh.php?cbid='.$_REQUEST['cbwhid'].'&inv_id='.$_REQUEST['inv_id'].'&pay_id='.$_REQUEST['pay_id'].'" disabled size="115" onclick="this.select();" style="font-size:14px;"></li>';
            echo "<li>Please copy the above URL to your ChargeBee Webhook settings. This will auto create invoices in XERO for every invoice that is generated in ChargeBee for next 30mins (XERO allows Public Applications to use OAuth token for 30 minutes ONLY)";
        }


    echo '</ul></p>';

}


/**
 * Persist the OAuth access token and session handle somewhere
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 * @param array $params the response parameters as an array of key=value pairs
 */
function persistSession($response)
{
    if (isset($response)) {
        $_SESSION['access_token']       = $response['oauth_token'];
        $_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
        if(isset($response['oauth_session_handle']))  $_SESSION['session_handle']     = $response['oauth_session_handle'];

        $access_token = $response['oauth_token'];
        $access_secret = $response['oauth_token_secret'];
      	if(isset($response['oauth_session_handle'])) { $session_handle     = $response['oauth_session_handle']; }
        else { $session_handle = ''; }
        $cb_id = saveSession($access_token, $access_secret);
        return $cb_id;
    } else {
        return false;
    }

}

/**
 * Retrieve the OAuth access token and session handle
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 */
function retrieveSession()
{
    if (isset($_SESSION['access_token'])) {
        $response['oauth_token']            =    $_SESSION['access_token'];
        $response['oauth_token_secret']     =    $_SESSION['oauth_token_secret'];
        if(isset($_SESSION['session_handle'])) $response['oauth_session_handle']   =    $_SESSION['session_handle'];
        return $response;
    } else {
        return false;
    }

}


function retrieveSession1(){
    if(isset($_REQUEST['cbid'])){
        $cbid = $_REQUEST['cbid'];
        $sql = "SELECT * FROM xero_oauth WHERE cbid=:cbid";
        try {
            $db = getDB();
            $stmt = $db->prepare($sql);  
            $stmt->bindParam("cbid", $cbid);
            $stmt->execute();
            $db = null;
            if ($stmt->rowCount() > 0) {
                $oauth = $stmt->fetch(PDO::FETCH_ASSOC);
                $response['oauth_token'] = $oauth['access_token'];
                $response['oauth_token_secret'] = $oauth['access_token_secret'];
                $response['oauth_session_handle'] = '';
                return $response;
            } else {
                return false;
            }
        } catch(PDOException $e) {
            // print_r('{"error":{"text":'. $e->getMessage() .', "sql":'.$sql.'}}'); 
            return false;
        }
    }
}

function saveSession($access_token, $access_secret){
    $cb_id = md5(rand());
    $sql = "INSERT INTO xero_oauth (cbid, access_token, access_token_secret) VALUES (:cb_id, :access_token, :access_secret);";
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("cb_id", $cb_id);
        $stmt->bindParam("access_token", $access_token);
        $stmt->bindParam("access_secret", $access_secret);
        $stmt->execute();
        $db = null;
        return $cb_id;
    } catch(PDOException $e) {
        // print_r('{"error":{"text":'. $e->getMessage() .', "sql":'.$sql.'}}'); 
        return false;
    }
}

function outputError($XeroOAuth)
{
    echo 'Error: ' . $XeroOAuth->response['response'] . PHP_EOL;
    pr($XeroOAuth);
}

/**
 * Debug function for printing the content of an object
 *
 * @param mixes $obj
 */
function pr($obj)
{

    if (!is_cli())
        echo '<pre style="word-wrap: break-word">';
    if (is_object($obj))
        print_r($obj);
    elseif (is_array($obj))
        print_r($obj);
    else
        echo $obj;
    if (!is_cli())
        echo '</pre>';
}

function is_cli()
{
    return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
}
