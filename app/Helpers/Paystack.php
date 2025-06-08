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


    public static function make($payment_data = []){
        $data = [
            'name' => $payment_data['name'],
            'email' => $payment_data['email'],
            // Convert to kobo
            'amount' => $payment_data['amount'] * 100,
            // 'subaccount'=> $payment_data['payment_id'],

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
                'message' => 'Payment initialize, ' . $response['message'],
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



}