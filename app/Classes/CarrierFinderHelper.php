<?php
namespace App\Classes;


use Illuminate\Support\Facades\Log;
use Sentry\Util\JSON;

class CarrierFinderHelper {

    public $carrierString;
    public $carrierName;
    public $trackingEnabled = false;

    /**
     * @param array $shipmentData
     * @param array $orderData
     * @return array
     */
    public function obtainCarrierDetails(array $shipmentData, array $orderData) : array {

        $this->carrierString = $orderData['shipmentId'] . $orderData['shipmentTitle'] . $orderData['shipmentData']['method'];

        Log::info('Carrier String: ' .$this->carrierString);

        $carrierName = $this->findCarrier();

        $returnArray['carrier']             = $carrierName;
        $returnArray['tracking_code']       = $shipmentData['trackingCode'];
        $returnArray['tracking_enabled']    = $this->trackingEnabled;

        Log::info('Return Data: ' . json_encode($returnArray, JSON_PRETTY_PRINT));

        return $returnArray;
    }

    /**
     * @return string
     */
    private function findCarrier() : string {

        if(!is_null($this->carrierString)) {

            $carrierString = strtolower($this->carrierString);

            foreach ($this->availableCarriers() as $carrier) {

                Log::info('Matching ' . $this->carrierString . ' on ' . $carrier);

                if (strpos($carrierString, $carrier) !== false) {
                    $this->carrierName = $carrier;
                    $this->trackingEnabled = true;
                    break;
                }
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
            'budbee'
        ];
    }

}
