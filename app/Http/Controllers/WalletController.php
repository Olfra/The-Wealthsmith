<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Wallet;

class WalletController extends Controller{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function recurrent_payment(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'risk_level' => 'required',
            'start_amount' => 'required',
            'frequency' => 'required',
            'timeframe' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'success' => false,
                    'status' => 400,
                    'message' => $validator->errors()->all(),
                ]]);
        }
        try {

            //$user_id = app('request')->get('authUser')->id;
            $wallet = new Wallet();
            $wallet->risk_level = $request->risk_level;
            $wallet->start_amount = $request->start_amount;
            $wallet->frequency = $request->frequency;
            $wallet->timeframe = $request->timeframe;
            $wallet->user_id = app('request')->get('authUser')->id;
     
            $wallet->save();

            return json_encode([
                'result' => [
                    'success' => true,
                    'status' => 200,
                    'message' => 'Wallet successfully posted',
                    'wallet_details' => $wallet,
                    
                ]]);

        } catch (\Illuminate\Database\QueryException $ex) {
            return json_encode([
                'status' => 500,
                'registered' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function get_wallet(){
        
    }
}
?>