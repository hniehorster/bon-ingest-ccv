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

        $this->carrierString = "";

        if(isset($orderData['shipmentId'])) {
            $this->carrierString .= $orderData['shipmentId'];
        }

        if(isset($orderData['shipmentTitle'])){
            $this->carrierString .= $orderData['shipmentTitle'];
        }

        if(isset($orderData['shipmentData']['method'])){
            $this->carrierString .= $orderData['shipmentData']['method'];
        }

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

            //Check for PostNL
            if(Str::startsWith($this->trackingCode, '3S')) {
                //Must be POSTNL or DHL
                $validateCode = substr($this->trackingCode, 2);

                preg_match_all('/[A-Za-z,]/', $validateCode, $characterCount);

                if(count($characterCount[0]) == 3){
                    $this->carrierName      = 'dhl';
                    $this->trackingEnabled  = true;
                    $carrierFound           = true;
                }elseif(count($characterCount[0]) >= 4){
                    $this->carrierName      = 'postnl';
                    $this->trackingEnabled  = true;
                    $carrierFound           = true;
                }
            }

            //Hack for finding the instabox
            if(Str::startsWith($this->trackingCode, 'R') && !$carrierFound){
                $this->carrierName      = 'instabox';
                $this->trackingEnabled  = true;
                $carrierFound           = true;
            }

            //DPD
            if(is_numeric($this->trackingCode) && Str::length($this->trackingCode) == 14){
                $this->carrierName      = 'dpd';
                $this->trackingEnabled  = true;
                $carrierFound           = true;
            }

            //DHL Expres
            if(
                Str::startsWith($this->trackingCode, 'JVGL') ||
                Str::startsWith($this->trackingCode, 'JJD') ||
                Str::startsWith($this->trackingCode, 'CI')) {
                $this->carrierName      = 'dhl';
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

    /**
     * @param $carrier
     * @return string
     */
    public static function getBonCarrier($carrier) : string {

        $bonCarrier = '';

        $carrierDictionary = [
            'PostNL' => 'postnl',
            'MyParcel' => 'myparcel',
            'DHL_Parcel' => 'dhl',
            'DPD' => 'dpd',
            'GLS' => 'gls',
            'FedEx' => 'fedex',
            'UPS' => 'ups',
            'Sandd' => 'sandd',
            'Bpost' => 'bpost',
        ];

        if(in_array($carrier, $carrierDictionary)) {
            return $carrierDictionary[$carrier];
        }

        return $bonCarrier;

    }

}
