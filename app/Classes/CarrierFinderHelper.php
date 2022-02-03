<?php
namespace App\Classes;


use Illuminate\Support\Facades\Log;
use Sentry\Util\JSON;
use function GuzzleHttp\Psr7\str;

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
        $this->trackingCode = $shipmentData['trackingCode'];

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

        if(!is_null($this->carrierString)) {

            $carrierString = strtolower($this->carrierString);

            foreach ($this->availableCarriers() as $carrier) {

                if (str_contains($carrierString, $carrier)) {

                    $this->carrierName = $carrier;

                    if(strlen($this->trackingCode) > 0) {
                        $this->trackingEnabled = true;
                    }

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
