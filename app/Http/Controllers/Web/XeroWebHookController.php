<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Dcblogdev\Xero\Facades\Xero;

class XeroWebHookController extends Controller
{

    public static $invoiceCategory = 'INVOICE';
    public static $insertEvent = 'INSERT';
    public static $updateEvent = 'UPDATE';
    public static $statusPaid = 'PAID';

    public $resourceId = null;
    public $resourceCategory = null;
    public $resourceEventType = null;

    public function index()
    {
        if (Xero::webhooks()->validate()) {
            $response = response()->json(null, 200);
        } else {
            // http_response_code(401);
            $response = response()->json(null, 401);
        }
        $response->setContent(null);
        //return Xero::webhooks()->getEvents();
        $events = Xero::webhooks()->getEvents();

        //Log the webhook call
        $myfile = fopen(storage_path('logs').'/xeroEvents.log', 'w');
        ob_start();
        var_dump($events);
        $output = ob_get_clean();
        fwrite($myfile, '==================================
'. time() . '
==================================
');
        fwrite($myfile, $output);
        fwrite($myfile, '
==================================


');
        fclose($myfile);

        //handling each event - although just one event will be received at a time
        $previousResourceId = null;
        foreach($events as $event) {
            $this->resourceCategory = $event->eventCategory ?? null;
            $this->resourceId = $event->resourceId ?? null;
            $this->resourceEventType = $event->eventType ?? null;
                        
            //is this an invoice event?
            if (
                $previousResourceId != $this->resourceId &&
                $this->resourceCategory == self::$invoiceCategory 
                && $this->resourceEventType == self::$updateEvent
            ) {
                $previousResourceId = $this->resourceId; //don't repeat the same process over and over
                //update the status of the sale in the system if present against xero invoice id
                if ($this->updateSystemSaleStatus()) {
                    //Shoot an email to customer with invoice link
                    $this->emailInvoice();
                }
                
            }
        }
            
            return $response;
        }

    public function updateSystemSaleStatus()
    {
        //check if this is an update against a resource that is mapped within our system
        $sale = Sale::where('xero_invoice_id', $this->resourceId)->first();
        //only proceed if invoice is mapped against a sale
        if (!empty($sale)) {
            $xeroInvoice = Xero::invoices()->find($this->resourceId);
            if (!empty($xeroInvoice)) {
                if ($xeroInvoice['Status'] == self::$statusPaid) {
                    $sale->payment_status = 2;
                    $sale->save();
                    return true;
                }
            }
        }

        return false;
    }

    public function emailInvoice()
    {
        $accessToken = Xero::getAccessToken();

        if (!empty($accessToken) && !empty($this->resourceId)) {
            Xero::post('Invoices/'.$this->resourceId.'/Email');
        }
    }


    /* public function indexOld(Request $request)
    {
                
        // Based on Xero developer document from
        // https://developer.xero.com/documentation/webhooks/overview
        // Returning data in the response body to Xero will cause the webhook
        // verification to fail, to get around this for testing store all the 
        //information we needed into a text file to helps us debug any issues.

        // ----------------------------------------------------------------------------

        // The payload in webhook MUST be read as raw data format,
        // even thought the webhook is sent with the
        // Content-Type header with 'application/json'.
        //
        // Otherwise any preprocess payload could be lead to incorrectly
        // computed signature key.

        // Get payload
        // $rawPayload = file_get_contents('php://input');
        $rawPayload = $request->getContent();

        // ------------------------------------
        // Compute hashed signature key with our webhook key

        // Update your webhooks key here
        $webhookKey = config('xero.webhookKey');

        // Compute the payload with HMACSHA256 with base64 encoding
        $computedSignatureKey = base64_encode(
        hash_hmac('sha256', $rawPayload, $webhookKey, true)
        );

        // Signature key from Xero request
        // $xeroSignatureKey = $_SERVER['HTTP_X_XERO_SIGNATURE'];
        $xeroSignatureKey = $request->headers->get('x-xero-signature');

        // Response HTTP status code when:
        //   200: Correctly signed payload
        //   401: Incorrectly signed payload
        $isEqual = false;
        $response = null;
        if (hash_equals($computedSignatureKey, $xeroSignatureKey)) {
        $isEqual = true;
        // http_response_code(200);
            $response = response()->json(null, 200);
        } else {
        // http_response_code(401);
            $response = response()->json(null, 401);
        }
        $response->setContent(null);

        // ------------------------------------
        // Store information into file
        // IMPORTANT - if you need to set permissions to allow
        // the file to be created - read more in the blog post.
        //https://devblog.xero.com/lets-play-web-hooky-with-php-34a141dcac0a

        // Request Headers
        $filedata = sprintf(
        "%s %s %s\n\n---- Request headers ----\n",
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $_SERVER['SERVER_PROTOCOL']
        );
        foreach ($this->getHeaderList() as $name => $value) {
        $filedata .= $name . ': ' . $value . "\n";
        }

        // Request Body
        $filedata .= "\n---- Request body ----\n";
        $filedata .= $rawPayload . "\n";

        // Signature key
        $filedata .= "\n---- Signature key ----";

        $filedata .= "\nComputed signature key:\n";
        $filedata .= $computedSignatureKey;

        $filedata .= "\nXero signature key:\n";
        $filedata .= $xeroSignatureKey;

        // Result
        $filedata .= "\n\n---- Result ----\n";
        if ($isEqual) {
        $filedata .= "Match";
        } else {
        $filedata .= "Not match";
        }

        // Store to file
        $currentTime = microtime();
        $filename = substr($currentTime, 11) . substr($currentTime, 2, 8);

        $events = $request->input('events');
        $this->resourceCategory = $events['eventCategory'];
        $this->resourceId = $events['resourceId'];
        $this->resourceEventType = $events['eventType'];

        //output to file
        //file_put_contents('/'.$filename.'.txt', $filedata);
        
        $this->fetchResourceFromXero();

        return $response;

    }

    // ----------------------------------------------------------------------------
    // Helper function(s)
    public function getHeaderList() {
        $headerList = [];
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/',$name)) {
            $headerList[$name] = $value;
            }
        }
        return $headerList;
    }
    // ----------------------------------------------------------------------------

    public function fetchResourceFromXero()
    {
        $accessToken = Xero::getAccessToken();

        if (!empty($accessToken) && !empty($this->resourceId)) {
            //is the resource an invoice
            if ($this->resourceCategory == self::$invoiceCategory && $this->resourceEventType == self::$updateEvent) {
                //check if this is an update against a resource that is mapped within our system
                $sale = Sale::where('xero_invoice_id', $this->resourceId)->first();
                //only proceed if invoice is mapped against a sale
                if (!empty($sale)) {
                    $xeroInvoice = Xero::invoices()->find($this->resourceId);
                    if (!empty($xeroInvoice)) {
                        if ($xeroInvoice['Status'] == self::$statusPaid) {
                            $sale->payment_status = 1;
                            $sale->save();
                        }
                    }
                }
            }
        }
    }
 */
    
}
