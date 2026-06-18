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
    public $slaServiceRelation;

    /**
     * @var \App\Model\Product\Service
     */
    public $service;

    public $licence;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('service.provider');

        // @phpstan-ignore class.notFound
        $sla = new Sla();
        $this->sla = $sla; // @phpstan-ignore property.notFound

        // @phpstan-ignore class.notFound
        $LicencedOrg = new LicencedOrganization();
        $this->LicencedOrg = $LicencedOrg; // @phpstan-ignore property.notFound

        // @phpstan-ignore class.notFound
        $slaServiceRelation = new SlaServiceRelation();
        $this->slaServiceRelation = $slaServiceRelation;

        $service = new Service();
        $this->service = $service;

        // @phpstan-ignore class.notFound
        $organization = new Organization();
        $this->organization = $organization; // @phpstan-ignore property.notFound

        // @phpstan-ignore class.notFound
        $licence = new Licence();
        $this->licence = $licence;
    }

    public function orders()
    {
        try {
            return view('themes.default1.serviceprovider.orders'); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function sla()
    {
        try {
            return view('themes.default1.serviceprovider.sla'); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function pricing()
    {
        try {
            // @phpstan-ignore class.notFound
            $licence = new Licence();
            $licences = $licence->get(); // @phpstan-ignore class.notFound

            return view('themes.default1.serviceprovider.pricing', compact('licences')); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }
}
