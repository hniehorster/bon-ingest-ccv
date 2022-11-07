<?php
namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\Handshake;
use Exception;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class HandshakeController extends Controller {

    /**
     * @param Request $request
     * @return void
     */
    public function accept(Request $request) {

        Log::info('-------- INCOMING HANDSHAKE --------');
        Log::info('All request data: '. $request->getContent());
        Log::info('All request URL: ' .  URL::current());

        $handShakeData[] = URL::current();;
        $handShakeData[] = $request->getContent();;

        $handShakeString = implode('|', $handShakeData);

        $handShakeSecret = env('CCV_SECRET_KEY');

        $sHash = hash_hmac('sha512', $handShakeString, $handShakeSecret);

        if($sHash === $request->header('x-hash')) {

            $handshake = new Handshake();
            $handshake->hash        = $sHash;
            $handshake->api_public  = $request->api_public;
            $handshake->api_secret  = $request->api_secret;
            $handshake->api_root    = $request->api_root;
            $handshake->return_url  = $request->return_url;
            $handshake->save();

            return "ok";
        }else{
            throw new Exception('Invalid Request');
        }
    }
}
