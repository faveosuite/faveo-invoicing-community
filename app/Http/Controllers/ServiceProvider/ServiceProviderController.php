<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CheckoutController;
use App\Model\licence\Licence;
use App\Model\Licence\LicencedOrganization;
use App\Model\licence\Sla;
use App\Model\licence\SlaServiceRelation;
use App\Model\Product\Service;
use App\Organization;
use Exception;

class ServiceProviderController extends Controller
{
    public $slaServiceRelation;

    public $service;

    public $licence;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('service.provider');

        $cart = new CheckoutController();
        $auth = ''; //$cart->GetXdeskAuthOrganization();
        $this->org = $auth;

        $sla = new Sla();
        $this->sla = $sla;

        $LicencedOrg = new LicencedOrganization();
        $this->LicencedOrg = $LicencedOrg;

        $slaServiceRelation = new SlaServiceRelation();
        $this->slaServiceRelation = $slaServiceRelation;

        $service = new Service();
        $this->service = $service;

        $organization = new Organization();
        $this->organization = $organization;

        $licence = new Licence();
        $this->licence = $licence;
    }

    public function orders()
    {
        try {
            return view('themes.default1.serviceprovider.orders');
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    public function sla()
    {
        try {
            return view('themes.default1.serviceprovider.sla');
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    public function pricing()
    {
        try {
            $licence = new Licence();
            $licences = $licence->get();

            return view('themes.default1.serviceprovider.pricing', compact('licences'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }
}
