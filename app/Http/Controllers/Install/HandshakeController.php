<?php
namespace App\Http\Controllers\Install;

use App\Models\Handshake;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Lumen\Routing\Controller as BaseController;

class HandshakeController extends BaseController {

    /**
     * @param Request $request
     * @return void
     *
     */
    public function accept(Request $request) {

        $handShakeData[] = URL::current();;
        $handShakeData[] = $request->getContent();;

        $handShakeString = implode('|', $handShakeData);

        $handShakeSecret = env('CCV_SECRET_KEY');

        $sHash = hash_hmac('sha512', $handShakeString, $handShakeSecret);

        if($sHash === $request->header('x-hash')) {

            $handshake = new Handshake();
            $handshake->hash        = $sHash;
            $handshake->language    = $request->language;
            $handshake->api_public  = $request->api_public;
            $handshake->api_secret  = $request->api_secret;
            $handshake->api_root    = $request->api_root;
            $handshake->return_url  = $request->return_url;
            $handshake->save();

            return "ok"; //Requirement from CCV

        }else{
            throw new Exception('Invalid Request');
        }
    }
}
