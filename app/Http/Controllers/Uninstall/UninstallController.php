<?php
namespace App\Http\Controllers\Uninstall;

use App\Models\Handshake;
use BonSDK\SDKIngest\Services\Businesses\BusinessUninstallService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Lumen\Routing\Controller as BaseController;

class UninstallController extends BaseController {

    /**
     * @param Request $request
     * @return string
     *
     */
    public function uninstall(Request $request) {

        $this->validate($request, [
            'api_public' => 'required|string'
        ]);

        $findBusiness = Handshake::whereApiPublic($request->api_public)->firstOrFail();

        $uninstallBusiness = json_decode((new BusinessUninstallService())->uninstallBusiness('en', $findBusiness->business_uuid));

        return "ok";

    }
}
