<?php
namespace App\Classes;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CarrierFinderHelper {

    public $carrierString;
    public $trackingCode;
    public $carrierName;
    public $trackingEnabled = false;

    /**
     * @param array $shipmentData
     * @param array $orderData
     * @return array
     */
    public function obtainCarrierDetails(array $shipmentData, array $orderData) : array {

        $this->carrierString = $orderData['shipmentId'] . $orderData['shipmentTitle'] . $orderData['shipmentData']['method'];
        $this->trackingCode  = $shipmentData['trackingCode'];

        Log::info('Tracking Code found: ' . $this->trackingCode);

        $carrierName = $this->findCarrier();

        $returnArray['carrier']             = $carrierName;
        $returnArray['tracking_code']       = $this->trackingCode;
        $returnArray['tracking_enabled']    = $this->trackingEnabled;

        Log::info('Return Data: ' . json_encode($returnArray, JSON_PRETTY_PRINT));

        return $returnArray;
    }

    /**
     * @return string
     */
    private function findCarrier() {

        $this->carrierName = null;
        $carrierFound = false;

        if(!is_null($this->carrierString)) {

            $carrierString = strtolower($this->carrierString);

            foreach ($this->availableCarriers() as $carrier) {

                if (str_contains($carrierString, $carrier)) {

                    $this->carrierName = $carrier;

                    if(strlen($this->trackingCode) > 0) {
                        $this->trackingEnabled = true;
                        $carrierFound = true;
                    }

                    break;
                }
            }

            //Hack for finding the instabox
            if(!$carrierFound && Str::startsWith($this->trackingCode, 'R')){
                $this->carrierName      = 'instabox';
                $this->trackingEnabled  = true;
                $carrierFound           = true;
            }


        } else {
            return false;
        }

        return $this->carrierName;
    }

    /**
     * @return string[]
     */
    private function availableCarriers() : array {
        return [
            'dpd',
            'dhl',
            'postnl',
            'fedex',
            'usps',
            'ups',
            'budbee',
            'instabox'
        ];
    }

}
