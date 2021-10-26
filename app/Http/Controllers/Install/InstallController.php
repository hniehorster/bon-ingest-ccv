<?php
namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Jobs\FetchShopDataJob;
use Illuminate\Http\Request;

class InstallController extends Controller {

    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     * @throws \Illuminate\Validation\ValidationException
     */
    public function preInstall(Request $request) {

        $rules = [
            'user_uuid' => 'required|uuid'
        ];

        $this->validate($request, $rules);

        return view('shopnumber', [
            'user_uuid' => $request->user_uuid,
            'apiLocale' => $this->apiLocale
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateRedirect(Request $request) {

        $rules = [
            'shop_number'   => 'required|integer',
            'user_uuid'     => 'required|uuid'
        ];

        $this->validate($request, $rules);

        $platform       = "eu1";
        $baseURL        = "https://api.webshopapp.com";
        $platformKey    = env('LIGHTSPEED_API_KEY_EU1');

        if($request->shop_number > 600000) {
            $platform   = "us1";
            $baseURL    = "https://api.shoplightspeed.com";
            $platformKey = env('LIGHTSPEED_API_KEY_US1');
        }

        $fullURL = $baseURL . '/' . $this->apiLocale . '/apps/install?key=' . $platformKey .'&shop_id=' . $request->shop_number . '&cluster=' . $platform . '&user_uuid=' . $request->user_uuid;

        return redirect($fullURL);

    }

    /**
     * @param Request $request
     */
    public function postInstall(Request $request) {

        //Handle all the post install requirements.
        if(config('platform_config.has_webhooks')){
            //create the webhooks
        }

        //1. Create the account


        //2. Create the Business


        //3. Create a job to fetch all orders
        event(new FetchShopDataJob());
    }

}
