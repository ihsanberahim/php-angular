<?php
/*------------------------------------------------------------------------
SECTION : DATATABLE 
------------------------------------------------------------------------*/
function datatable_request($config=array())
{
  $instance = App::getInstance();
  $column_aliases = @$config['column_aliases'] || array();

  /*------------------------------------------------------------------------
  SECTION : DATATABLE INPUT
  ------------------------------------------------------------------------*/
  $start = intval( $instance->httpGet('start', 0) );
  $length = intval( $instance->httpGet('length', 30) );
  $search = $instance->httpGet('search');
  $search_value = $search['value'];
  $order = $instance->httpGet('order');
  $columns = $instance->httpGet('columns');
  /*----------------------------------------------------------------------*/

  /*------------------------------------------------------------------------
  SECTION : FIXES FIELD ALIASES
  ------------------------------------------------------------------------*/
  foreach($columns as $key => $value)
  {
    $keyword = @$columns[$key]['search']['value'];

    if(isset($column_aliases[$key]))
    {
      $columns[$key]['data'] = $column_aliases[$key];
    }
  }
  /*----------------------------------------------------------------------*/

  /*------------------------------------------------------------------------
  SECTION : PARSE ORDER REQUEST
  ------------------------------------------------------------------------*/
  $order_column = @$order[0]['column'];
  $order_direction = @$order[0]['dir'];
  $order_by = @$columns[$order_column]['data'];
  /*----------------------------------------------------------------------*/

  return (object) array(
      'start' => $start,
      'length' => $length,
      'search' => $search,
      'search_value' => $search_value,
      'order' => $order,
      'columns' => $columns,
      'order_column' => $order_column,
      'order_direction' => $order_direction,
      'order_by' => $order_by
    );
}
/*----------------------------------------------------------------------*/

/*----------------------------------------------------------------------
GROUP : PHP HELPER
----------------------------------------------------------------------*/
function sed_cron($id)
{
  $result = false;

  try{
    shell_exec('crontab -l > newcron');
    shell_exec('cat newcron | sed "/'.$id.'/d" newcron > tmp && mv tmp newcron');
    shell_exec('crontab newcron;');
    shell_exec('rm newcron;');

    $result = true;
  }catch(Exception $e)
  {
    console_log('append_cron() ... '.$e->getMessage());
  }

  return $result;
}
function append_cron($command_string)
{
  $result = false;
  $timezone = date_default_timezone_get();

  console_log('append_cron(...) ... timezone ... '.$timezone);
  console_log('append_cron(...) ... command_string ... '.$command_string);

  try{
    shell_exec('crontab -l > newcron;');
    shell_exec('echo "'.$command_string.'" >> newcron;');
    shell_exec('crontab newcron;');
    shell_exec('rm newcron;');

    $result = true;
  }catch(Exception $e)
  {
    console_log('append_cron() ... '.$e->getMessage());
  }

  return $result;
}
function get_last_commit($key)
{
  $row = shell_exec("git log -n 1 | grep '$key' -m 1");
  $result = '';

  switch($key)
  {
    case 'commit':
      $result = str_replace('commit', '', $row);
    break;
    case 'author':
      $result = str_replace('Author:', '', $row);
    break;
    case 'date':
      $result = str_replace('Date:', '', $row);
    break;
  }

  return trim($result);
}
function console_log($message, $log_file_path='console.log')
{
  $date = date('d.m.Y h:i:s'); 

  error_log("\n Date: $date | Message: $message \n\n", 3, $log_file_path);
}
function getPayload()
{
  $payload_object = json_decode(file_get_contents('php://input'));

  return $payload_object;
}

function int_split($string, $delimiter=',')
{
  $array = array_map( 'intval', explode($delimiter,$string) );
  return array_unique( $array );
}
function str2alpn($string, $replace='')
{
  return preg_replace("/[^a-zA-Z0-9]+/", $replace, $string);
}
/*
function is_email($email)
{
  $sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
  return filter_var($sanitized_email, FILTER_VALIDATE_EMAIL);
}
*/
function app_url()
{
    return request_scheme() . '://' . $_SERVER['HTTP_HOST'];
}


function request_url()
{
    return request_scheme() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}


function request_scheme()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
}

function _api($code, $msg, $data=array(), $echo=true)
{

  $json = (object) array(
      'code' => $code,
      'msg' => $msg,
      'data' => $data
    );

  if($echo){
    header('Content-type: application/json');

    if(defined('NO_CACHE'))
    {
      header_remove('Expires');
    }else
    {
      header("Cache-Control: max-age=3600");
      header("Pragma: cache");
    }
    
    http_response_code($code);
    
    if(defined('JSON_NO_FORMAT'))
    {
      echo json_encode($data);
    }else
    {
      echo json_encode($json);
    }

    exit;
  }else
    return $json;
}

function content2src($html){
  $doc = new DOMDocument();
  libxml_use_internal_errors(true);
  $doc->loadHTML( $html );
  $xpath = new DOMXPath($doc);
  $imgs = $xpath->query("//img");
  for ($i=0; $i < $imgs->length; $i++) {
      $img = $imgs->item($i);
      $src = $img->getAttribute("src");
  }
 
  return $src;
}

function hide_email($email) { $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz'; $key = str_shuffle($character_set); $cipher_text = ''; $id = 'e'.rand(1,999999999); for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])]; $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";'; $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));'; $script.= 'document.getElementById("'.$id.'").innerHTML="<a class=\"btn btn-xl\" href=\\"mailto:"+d+"\\">"+d+"</a>"'; $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>'; echo '<span id="'.$id.'">[javascript protected email address]</span>'.$script; } 

/*----------------------------------------------------------------------
GROUP : PAYPAL
----------------------------------------------------------------------*/

function paypal_rate($amount)
{
  //http://www.paymalaysia.com/paypal-malaysia/paypal_fees.htm

  $standart_rate = 3.4;
  if($amount>12000) $standart_rate = 2.9;
  if($amount>40000) $standart_rate = 2.7;
  if($amount>400000) $standart_rate = 2.4;

  return $standart_rate/100;
}
function paypal_object($app=null)
{
  if($app==null || !defined('PAYPAL_MODE') || !defined('PAYPAL_ENV') || !defined('PAYPAL_SERVER')) return null;

  //Expected Paypal Response
  $epr = array(
    "mc_gross"=>"",
    "protection_eligibility"=>"",
    "address_status"=>"",
    "payer_id"=>"",
    "tax"=>"",
    "address_street"=>"",
    "payment_date"=>"",
    "payment_status"=>"",
    "charset"=>"",
    "address_zip"=>"",
    "first_name"=>"",
    "mc_fee"=>"",
    "address_country_code"=>"",
    "address_name"=>"",
    "notify_version"=>"",
    "custom"=>"",
    "payer_status"=>"",
    "business"=>"",
    "address_country"=>"",
    "address_city"=>"",
    "quantity"=>"",
    "payer_email"=>"",
    "verify_sign"=>"",
    "txn_id"=>$app->httpGet('tx',''),
    "payment_type"=>"",
    "last_name"=>"",
    "address_state"=>"",
    "receiver_email"=>"",
    "payment_fee"=>"",
    "receiver_id"=>"",
    "txn_type"=>"",
    "item_name"=>$app->httpGet('item_name',''),
    "mc_currency"=>$app->httpGet('cc',''),
    "item_number"=>"",
    "residence_country"=>"",
    "test_ipn"=>"",
    "handling_amount"=>"",
    "transaction_subject"=>"",
    "payment_gross"=>"",
    "shipping"=>"",
    "auth"=>""
  );

  // return (object) json_decode('{"mc_gross":"4768.74","protection_eligibility":"Eligible","address_status":"confirmed","payer_id":"ZU4RLW5BWPK8E","tax":"0.00","address_street":"1 Main St","payment_date":"08:17:07 Oct 28, 2014 PDT","payment_status":"Completed","charset":"windows-1252","address_zip":"95131","first_name":"buyer","mc_fee":"138.59","address_country_code":"US","address_name":"buyer one","notify_version":"3.8","custom":"","payer_status":"verified","business":"ihsan.berahim-facilitator@fiction-labs.com","address_country":"United States","address_city":"San Jose","quantity":"1","payer_email":"ihsan.berahim@fiction-labs.com","verify_sign":"A1ySrPYq8hs1UorSVOC2vBFCr1k7A1J0mffafO5zrjdI.wN0D3dAosWq","txn_id":"18M865742M540113X","payment_type":"instant","last_name":"one","address_state":"CA","receiver_email":"ihsan.berahim-facilitator@fiction-labs.com","payment_fee":"138.59","receiver_id":"UQBUUNEPXBYJU","txn_type":"web_accept","item_name":"Raihan Kamal Gold Payment (102)","mc_currency":"USD","item_number":"","residence_country":"US","test_ipn":"1","handling_amount":"0.00","transaction_subject":"","payment_gross":"4768.74","shipping":"0.00","auth":"AksFr7qVYVJlsAS4Yo0UgP-Vio5YVpce0yWwsQUaGuHO-PVFTo-jb95LqlSDEP20MBqwG8iXJ-cSSest--omYzA"}');

  return (object) array_merge($epr,$_POST); //pr = paypal response
}
function paypal_notify($app=null)
{
	if($app==null || !defined('PAYPAL_MODE') || !defined('PAYPAL_ENV') || !defined('PAYPAL_SERVER')) return null;

    $pr = paypal_object($app);

    if( $pr->txn_id != '')
    {
        /**
         * Paypal IPN Callback
         */
        // STEP 1: read POST data

        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
          $keyval = explode ('=', $keyval);
          if (count($keyval) == 2)
             $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
           $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
           if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
           } else {
                $value = urlencode($value);
           }
           $req .= "&$key=$value";
        }


        // Step 2: POST IPN data back to PayPal to validate
        $ch = curl_init(PAYPAL_SERVER);

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if( !($res = curl_exec($ch)) ) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);
        /*end: Paypal IPN Callback*/

        $log_content = sprintf("
Process State: %s \n
PDT Received: %s \n
Acknowledged IPN: %s \n
            ",
            $app->httpGet('q'),
            json_encode($pr),
            $res);

        $paypal_log_folder = APP_PATH.'/paypal_log/';
        if(!is_dir($paypal_log_folder)) mkdir($paypal_log_folder,0755,true);

        file_put_contents($paypal_log_folder."paypal_notify_{$pr->txn_id}.log", $log_content, FILE_APPEND | LOCK_EX);
    }

  return $res;
}
