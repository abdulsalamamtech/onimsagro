<?php


namespace App\Helpers;
use Illuminate\Support\Facades\Http;


class Paystack
{

    /**
     * @return api url
     */
    private static function url(){
        return config('services.paystack.url') ?? null;
    }
    /**
     * @return api secret key
     */
    private static function secretKey(){
        return config('services.paystack.secret') ?? null;
    }
    /**
     * GET /paystack
     * @return api response
     */
    private static function get($path = '', $data = []){
        // Implement logic to connect to the Paystack API
        try {
            $response = Http::withToken(self::secretKey())
                ->get(self::url() . '/'. $path, $data);
            // error
            info('paystack response error: ', $response->json());
            $message = "failed to process this action: ";
            if ($response->failed()) {
                return ([
                    'success'=> false,
                    'message' => $message . $response['message']
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            info('paystack exception error: ', [$th->getMessage()]);
            $message = 'failed to generate account: please try again later';
            return ([
                'success'=> false,
                'message' => $message,
            ]);
        }
        // Return the API key as a string
        info('paystack response successful: ', $response->json());
        // successful
        return ([
            'success'=> true,
            'message' =>$response['message'],
            'data' => $response['data'],
        ]);
    }
    /**
     * POST /paystack
     * @return api response
     */
    private static function post($path = '', $data = []){
        // Implement logic to connect to the Paystack API
        try {
            $response = Http::withToken(self::secretKey())
                ->post(self::url() . '/'. $path, $data);
            // error
            info('paystack response error: ', $response->json());
            $message = "failed to process this action: ";
            if ($response->failed()) {
                return ([
                    'success'=> false,
                    'message' => $message . $response['message']
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            info('paystack exception error: ', [$th->getMessage()]);
            $message = 'failed to generate account: please try again later';
            return ([
                'success'=> false,
                'message' => $message,
            ]);
        }
        // Return the API key as a string
        info('paystack response successful: ', $response->json());
        // successful
        return ([
            'success'=> true,
            'message' =>$response['message'],
            'data' => $response['data'],
        ]);
    }


    public static function make(){
        $data = [
            'name' => $payment_data['name'],
            'email' => $payment_data['email'],
            // Convert to kobo
            'amount' => $payment_data['amount'] * 100,
            'subaccount'=> $payment_data['payment_id'],

            // Test payment account information
            // Live Acc = ACCT_gfbo9r4csa29bnx
            // "subaccount" => "ACCT_7ib9ztjvcev66wo",
            // 'subaccount'=> 'ACCT_gfbo9r4csa29bnx',

            'metadata' => [
                // Your unique reference for the order
                'order_id' =>  $payment_data['payment_id'] . '-' . now()->format('Y-m-d H:i:sA'),
            ],
            // Your callback URL to handle payment status
            // 'callback_url' => route('payment.verify'),
            'callback_url' => $payment_data['redirect_url'] ?? url()->previous(),

        ];
        try {
            $response = Http::withToken(config('services.paystack.secret'))
                ->post(config('services.paystack.url') . '/transaction/initialize', $data);

            // return $response;

            if ($response->failed()) {
                return ([
                    'success'=> false,
                    'message' => 'Failed to initialize payment, ' . $response['message']
                ]);
            }

            return ([
                'success'=> true,
                'authorization_url' => $response['data']['authorization_url'],
                'access_code' => $response['data']['access_code'],
                'reference' => $response['data']['reference'],
                'gateway' => 'paystack',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return ([
                'success'=> false,
                'message' => 'Failed to initialize payment, please try again later',
            ]);
        }
    }

    /**
     * Verify the payment.
     */
    public static function verify($reference)
    {

        try {
            $response = Http::withToken(config('services.paystack.secret'))
                ->get(config('services.paystack.url') . '/transaction/verify/' . $reference);

            // dd($response);

            if ($response->failed()) {
                return (['success'=> false, 'message' => 'Failed to verify payment.']);
            }

            // response.data.status
            if ($response['data']['status'] === 'success') {
                return (['success'=> true, 'message' => 'Payment verified successfully.', 'data' => $response['data']]);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return (['success'=> false, 'message' => 'Failed to verify payment, please try again later']);

        }

        return (['success'=> false, 'message' => 'Payment verification failed.']);
    }

    /**
     * Create Customer
     * Create a customer on your integration
     * @param array [email, first_name, last_name, phone]
     * @return array 
     */
    public static function createCustomerAccount($data = []){
        // $data=[
        //     "email"=> "customer@example.com",
        //     "first_name"=> "Zero",
        //     "last_name"=> "Sum",
        //     "phone"=> "+2348123456789"
        // ];
        
        // Implement logic to create a new Paystack account
        // POST url="https://api.paystack.co/customer"
        $result = self::post('customer', $data);
        return $result;
        
        // {
        //     "status": true,
        //     "message": "Customer created",
        //     "data": {
        //       "email": "customer@email.com",
        //       "integration": 100032,
        //       "domain": "test",
        //       "customer_code": "CUS_xnxdt6s1zg1f4nx",
        //       "id": 1173,
        //       "identified": false,
        //       "identifications": null,
        //       "createdAt": "2016-03-29T20:03:09.584Z",
        //       "updatedAt": "2016-03-29T20:03:09.584Z"
        //     }
        //   }
  
    }


    /**
     * List Customer
     * List customers available on your integration.
     * perPage default 50 | page default 1 | from timestamp to timestamp
     * @return array 
     */
    public static function listCustomers(){

        // GET url="https://api.paystack.co/customer"
        $result = self::get('customer');
        return $result;
    }

    /**
     * Fetch Customer
     * Get details of a customer on your integration.
     * @param string $email or $code
     * @return array
     */
    public static function fetchCustomer($email_or_code){

        // GET url="https://api.paystack.co/customer/{email_or_code}"
        $result = self::get('customer/' . $email_or_code);
        return $result;

        // {
        //     "status": true,
        //     "message": "Customer retrieved",
        //     "data": {
        //       "transactions": [],
        //       "subscriptions": [],
        //       "authorizations": [
        //         {
        //           "authorization_code": "AUTH_ekk8t49ogj",
        //           "bin": "408408",
        //           "last4": "4081",
        //           "exp_month": "12",
        //           "exp_year": "2030",
        //           "channel": "card",
        //           "card_type": "visa ",
        //           "bank": "TEST BANK",
        //           "country_code": "NG",
        //           "brand": "visa",
        //           "reusable": true,
        //           "signature": "SIG_yEXu7dLBeqG0kU7g95Ke",
        //           "account_name": null
        //         }
        //       ],
        //       "first_name": null,
        //       "last_name": null,
        //       "email": "dom@gmail.com",
        //       "phone": null,
        //       "metadata": null,
        //       "domain": "test",
        //       "customer_code": "CUS_c6wqvwmvwopw4ms",
        //       "risk_action": "default",
        //       "id": 90758908,
        //       "integration": 463433,
        //       "createdAt": "2022-08-15T13:46:39.000Z",
        //       "updatedAt": "2022-08-15T13:46:39.000Z",
        //       "created_at": "2022-08-15T13:46:39.000Z",
        //       "updated_at": "2022-08-15T13:46:39.000Z",
        //       "total_transactions": 0,
        //       "total_transaction_value": [],
        //       "dedicated_account": null,
        //       "identified": false,
        //       "identifications": null
        //     }
        //   }
    }

    /**
     * Create Virtual Account
     * Create a dedicated account for a customer on your integration.
     * @param string $email
     * @return array
     */
    public static function createVirtualAccount(string $email){

        // $data=[
        //     "customer" => 481193, 
        //     "preferred_bank" => "wema-bank"
        // ];
        // Implement logic to create a new Paystack account
        // Get Customer details from paystack
        $customer = self::fetchCustomer($email);
        if($customer['success'] && $customer['data']['id']){
            // Create a dedicated account for the customer
            // POST url="https://api.paystack.co/dedicated_account"
            $data=[
                "customer" => $customer['data']['customer_code'], 
                "preferred_bank" => "wema-bank"
            ];
            $result = self::post('dedicated_account', $data);
            return $result;
        }else{
            return ['success'=> false,'message' => 'Failed to create virtual account'];
        }
       
    }

    /**
     * Fetch user virtual account details
     * @return array
     */
    public static function fetchVirtualAccount($account_id){
        // Implement logic to fetch a specific Paystack account details
        // Return the account details as an associative array
        // GET url="https://api.paystack.co/dedicated_account/{account_id}"
        $result = self::get('dedicated_account/' . $account_id);
        return $result;
        // {
        //     "status": true,
        //     "message": "NUBAN retrieved",
        //     "data": {
        //       "bank": {
        //         "name": "Wema Bank",
        //         "id": 20,
        //         "slug": "wema-bank"
        //       },
        //       "account_name": "KAROKART / RHODA CHURCH",
        //       "account_number": "9930000737",
        //       "assigned": true,
        //       "currency": "NGN",
        //       "metadata": null,
        //       "active": true,
        //       "id": 253,
        //       "created_at": "2019-12-12T12:39:04.000Z",
        //       "updated_at": "2020-01-06T15:51:24.000Z",
        //       "assignment": {
        //         "integration": 100043,
        //         "assignee_id": 7454289,
        //         "assignee_type": "Customer",
        //         "expired": false,
        //         "account_type": "PAY-WITH-TRANSFER-RECURRING",
        //         "assigned_at": "2020-01-06T15:51:24.764Z"
        //       },
        //       "customer": {
        //         "id": 7454289,
        //         "first_name": "RHODA",
        //         "last_name": "CHURCH",
        //         "email": "rhodachurch@email.com",
        //         "customer_code": "CUS_kpb3qj71u1m0rw8",
        //         "phone": "+2349053267565",
        //         "risk_action": "default"
        //       }
        //     }
        //   }

    }

    /**
     * List Customer
     * List customers available on your integration.
     * perPage default 50 | page default 1 | from timestamp to timestamp
     * @return array 
     */
    public static function listVirtualAccount(){

        // GET url="https://api.paystack.co/customer"
        $result = self::get('dedicated_account');
        return $result;
    }    

    public function transferFunds($fromAccount, $toAccount, $amount){
        // Implement logic to transfer funds from one Paystack account to another
        // Return the transaction details as an associative array
    }

    public function getTransactionDetails($transactionId){
        // Implement logic to retrieve transaction details using the transaction ID
        // Return the transaction details as an associative array
    }

    public function getBalance($accountId){
        // Implement logic to retrieve account balance using the account ID
        // Return the account balance as a decimal
    }


}
