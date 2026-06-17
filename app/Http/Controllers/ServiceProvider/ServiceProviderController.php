<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Model\licence\Licence;
use App\Model\Licence\LicencedOrganization;
use App\Model\licence\Sla;
use App\Model\licence\SlaServiceRelation;
use App\Model\Product\Service;
use App\Organization;
use Exception;

class ServiceProviderController extends Controller
{
    /**
     * @var \App\Model\licence\SlaServiceRelation
     */
    public $slaServiceRelation;

    /**
     * @var \App\Model\Product\Service
     */
    public $service;

    /**
     * @var \App\Model\licence\Licence
     */
    public $licence;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('service.provider');

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
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function sla()
    {
        try {
            return view('themes.default1.serviceprovider.sla');
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function pricing()
    {
        try {
            $licence = new Licence();
            $licences = $licence->get();

            return view('themes.default1.serviceprovider.pricing', compact('licences'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }
}
