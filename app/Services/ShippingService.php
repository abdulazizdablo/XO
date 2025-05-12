<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use App\Traits\CloudinaryTrait;
use App\Traits\TranslateFields;
use Illuminate\Support\Carbon;
use App\Services\AramexServices;

class ShippingService
{

    private $aramexServices;

    public function __construct(AramexServices $aramexServices)
    {
        $this->aramexServices = $aramexServices;
    }
    public function prepareShipmentData($shipping_details, $inventory)
    {
        $pickup_date = Carbon::now();
        $pickup_date = $pickup_date->add('1 day');
        $date = Carbon::parse($pickup_date);
        $pickup_date = $date->getTimestampMs();
        // $shipping_details=$request->shipping_details;
        $order_id = $shipping_details['order_id'];
        $countryCode = $shipping_details['countryCode'];
        $city = $shipping_details['city'];
        $first_name = $shipping_details['first_name'];
        $last_name = $shipping_details['last_name'];
        $lat = $shipping_details['lat'];
        $long = $shipping_details['long'];
        $phone = $shipping_details['phone'];
        $email = $shipping_details['email'];
        $apt_number = $shipping_details['apt_number'];
        $apt_name = $shipping_details['apt_name'];
        $address = $shipping_details['address'];
        $alt_phone = $shipping_details['alt_phone'];
        $additional_details = $shipping_details['additional_details'];
        $comments = $shipping_details['comments'];
        $schedule_for = $shipping_details['schedule_for'];

        $warehouseValue = json_decode($inventory->value);
        // $warehouseLat = $warehouseValue->lat;
        // $warehouseLong = $warehouseValue->long;
        $warehouse_city = $warehouseValue->city;
        $warehouse_full_address = $warehouseValue->address;

        $shipmentData = [
            "Shipments" => [
                [
                    "Reference1" => null,
                    "Reference2" => null,
                    "Reference3" => null,
                    "Shipper" => [
                        "Reference1" => null,
                        "Reference2" => null,
                        "AccountNumber" => "71485582",
                        "PartyAddress" => [
                            "Line1" => $warehouse_full_address,
                            "Line2" => null,
                            "Line3" => null,
                            "City" => $warehouse_city,
                            "StateOrProvinceCode" => $warehouse_city,
                            "PostCode" => "",
                            "CountryCode" => $countryCode,
                            "Longitude" => $warehouse_long ?? 0,
                            "Latitude" => $warehouse_lat ?? 0,
                            "BuildingNumber" => null,
                            "BuildingName" => null,
                            "Floor" => null,
                            "Apartment" => null,
                            "POBox" => null,
                            "Description" => null
                        ],
                        "Contact" => [
                            "Department" => null,
                            "PersonName" => "Arabesque",
                            "Title" => null,
                            "CompanyName" => "Arabesque Gallery",
                            "PhoneNumber1" => "971557550852",
                            "PhoneNumber1Ext" => null,
                            "PhoneNumber2" => null,
                            "PhoneNumber2Ext" => null,
                            "FaxNumber" => null,
                            "CellPhone" => "971557550852",
                            "EmailAddress" => "madianterkawi@hotmail.com",
                            "Type" => null
                        ]
                    ],
                    "Consignee" => [
                        "Reference1" => null,
                        "Reference2" => null,
                        "AccountNumber" => null,
                        "PartyAddress" => [
                            "Line1" => $address,
                            "Line2" => null,
                            "Line3" => "",
                            "City" => $city,
                            "StateOrProvinceCode" => $city,
                            "PostCode" => "",
                            "CountryCode" => $countryCode,
                            "Longitude" => $long ?? 0,
                            "Latitude" =>  $lat ?? 0,
                            "BuildingNumber" => $apt_number ?? null,
                            "BuildingName" => $apt_name ?? null,
                            "Floor" => null,
                            "Apartment" => null,
                            "POBox" => null,
                            "Description" => $additional_details ?? null
                        ],
                        "Contact" => [
                            "Department" => null,
                            "PersonName" => $first_name . " " . $last_name,
                            "Title" => null,
                            "CompanyName" => $first_name . " " . $last_name,
                            "PhoneNumber1" => $alt_phone ?? null,
                            "PhoneNumber1Ext" => null,
                            "PhoneNumber2" =>  null,
                            "PhoneNumber2Ext" => null,
                            "FaxNumber" => null,
                            "CellPhone" => $phone,
                            "EmailAddress" => $email,
                            "Type" => null
                        ]
                    ],
                    "ThirdParty" => null,
                    "ShippingDateTime" => "/Date($pickup_date)/",
                    "DueDate" => "/Date($schedule_for)/",
                    "Comments" =>  $comments ?? null,
                    "PickupLocation" => null,
                    "OperationsInstructions" => null,
                    "AccountingInstrcutions" => null,
                    "Details" => [
                        "Dimensions" => null,
                        "ActualWeight" => [
                            "Unit" => "KG",
                            "Value" => 0.5
                        ],
                        "ChargeableWeight" => null,
                        "DescriptionOfGoods" => "Soap or Accessories or couper",
                        "GoodsOriginCountry" => "AE",
                        "NumberOfPieces" => 1,
                        "ProductGroup" => "DOM",
                        "ProductType" => "CDS",
                        "PaymentType" => "P",
                        "PaymentOptions" => null,
                        "CustomsValueAmount" => null,
                        "CashOnDeliveryAmount" => null,
                        "InsuranceAmount" => null,
                        "CashAdditionalAmount" => null,
                        "CashAdditionalAmountDescription" => null,
                        "CollectAmou nt" => null,
                        "Services" => null,
                        "Items" => null,
                        "DeliveryInstructions" => null
                    ],
                    "Attachments" => null,
                    "ForeignHAWB" => null,
                    "TransportType" => 0,
                    "PickupGUID" => null,
                    "Number" => null,
                    "ScheduledDelivery" => null
                ]
            ],
            "LabelInfo" => [
                "ReportID" => 9729,
                "ReportType" => "URL"
            ],
            "ClientInfo" => [
                "Source" => config("app.Aramex_Source"),
                "AccountCountryCode" => config("app.Aramex_AccountCountryCode"),
                "AccountEntity" => config("app.Aramex_AccountEntity"),
                "AccountPin" => config("app.Aramex_AccountPin"),
                "AccountNumber" => config("app.Aramex_AccountNumber"),
                "UserName" => config("app.Aramex_UserName"),
                "Password" => config("app.Aramex_Password"),
                "Version" => config("app.Aramex_Version"),
            ],
            "Transaction" => [
                "Reference1" => "001",
                "Reference2" => "",
                "Reference3" => "",
                "Reference4" => "",
                "Reference5" => ""
            ]
        ];

        return $shipmentData;
    }

    public function createShipments($shipmentData)
    {
        return $this->aramexServices->createShipments("Shipping/Service_1_0.svc/json/", $shipmentData);
    }

//     public function createShipment(Request $request)
// {
//     // Retrieve $shipping_details and $countryCode from $request

//     $warehouse = Setting::where('key', 'LIKE', '%' . $countryCode . '%')->first();

//     $shippingService = new ShippingService();

//     $shipmentData = $shippingService->prepareShipmentData($shipping_details, $warehouse);

//     return $shippingService->createShipments($shipmentData);
// }
}
