<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentAccountRequest;
use App\Http\Requests\UpdatePaymentAccountRequest;
use App\Http\Resources\ConsultationResource;
use App\Http\Resources\PaymentAccountResource;
use App\Models\Activity;
use App\Models\Consultation;
use App\Models\PaymentAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all payment accounts
        $paymentAccounts = PaymentAccount::latest()->paginate(10);

        // transform data
        $response = PaymentAccountResource::collection($paymentAccounts);

        // return response
        return ApiResponse::success($response, 'successful', 200, $paymentAccounts);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentAccountRequest $request)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();
            // create data
            $data['created_by'] = ActorHelper::getUserId() ?? null;
            $paymentAccount = PaymentAccount::create($data);
            // transform data
            $response = new PaymentAccountResource($paymentAccount);

            // commit transaction and log activity 
            DB::commit();
            info($this, [$paymentAccount]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'created payment account',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Payment account created successfully', 201, $paymentAccount);

        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();

            DB::rollBack();
            Log::error('Error rolling back transaction: ', [$message]);
            return ApiResponse::error($th, $message);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentAccount $paymentAccount)
    {
        // transform data
        $response = new PaymentAccountResource($paymentAccount);

        // return response
        return ApiResponse::success($response, 'successful', 200, $paymentAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();
            // update data
            $paymentAccount->update($data);
            // transform data
            $response = new PaymentAccountResource($paymentAccount);

            // commit transaction and log activity 
            DB::commit();
            info($this, [$paymentAccount]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'updated payment account',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Payment account updated successfully', 200, $paymentAccount);

        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();

            DB::rollBack();
            Log::error('Error rolling back transaction: ', [$message]);
            return ApiResponse::error($th, $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentAccount $paymentAccount)
    {
        // delete data from storage
        $paymentAccount->delete();
        // return response
        return ApiResponse::success([], 'Payment account deleted successfully', 200);

    }
}
