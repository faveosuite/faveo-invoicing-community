<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Product\Subscription;
use Carbon\Carbon;

class AutorenewalCronController extends Controller
{
    public function __construct()
    {
        $subscription = new Subscription();
        $this->sub = $subscription;
    }

    public function getAutoSubscriptions($allDays)
    {
        $sub = [];
        foreach ($allDays as $allDay) {
            if ($allDay >= 2) {
                if ($this->getAllDaysSubscription($allDay) != []) {
                    array_push($sub, $this->getAllDaysSubscription($allDay));
                }
            } elseif ($allDay == 1) {
                if (count($this->get1DaysUsers()) > 0) {
                    array_push($sub, $this->get1DaysSubscription());
                }
            }
        }

        return $sub;
    }

    public function getAllDaysSubscription($day)
    {
        $users = [];
        $users = $this->getAllDaysExpiryUsers($day);
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getAllDaysExpiryUsers($day)
    {
        $sub = $this->getAllDaysExpiryInfo($day);
        //dd($sub->get());
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getAllDaysExpiryInfo($day)
    {
        $minus1day = new Carbon('+'.($day - 1).' days');
        $plus1day = new Carbon('+'.($day + 1).' days');
        $sub = Subscription::whereNotNull('update_ends_at')
            ->whereBetween('update_ends_at', [$minus1day, $plus1day])
            ->where('is_subscribed', '1');

        return $sub;
    }

    public function get1DaysUsers()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function getOneDayExpiryUsers()
    {
        $sub = $this->getOneDayExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOneDayExpiryInfo()
    {
        $yesterday = new Carbon('-2 days');
        $today = new Carbon('today');
        $sub = Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', '1')
                ->whereBetween('update_ends_at', [$yesterday, $today]);

        return $sub;
    }

    public function get1DaysSubscription()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getPostSubscriptions($days)
    {
        $sub = [];
        foreach ($days as $allDay) {
            if ($allDay >= 2) {
                if ($this->getAllDaysPostSubscription($allDay) != []) {
                    array_push($sub, $this->getAllDaysPostSubscription($allDay));
                }
            } elseif ($allDay == 1) {
                if (count($this->get1DaysPostUsers()) > 0) {
                    array_push($sub, $this->get1DaysPostSubscription());
                }
            }
        }

        return $sub;
    }

    public function getAllDaysPostSubscription($day)
    {
        $users = [];
        $users = $this->getAllDaysPostExpiryUsers($day);
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getAllDaysPostExpiryUsers($day)
    {
        $sub = $this->getAllDaysPostExpiryInfo($day);
        //dd($sub->get());
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getAllDaysPostExpiryInfo($day)
    {
        $dayUtc = new Carbon();
        $now = $dayUtc->toDateTimeString();
        $past = Carbon::now()->subDays($day + 1);

        $sub = Subscription::whereNotNull('update_ends_at')
               ->whereBetween('update_ends_at', [$past, $now]);

        return $sub;
    }

    public function get1DaysPostUsers()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get1DaysPostSubscription()
    {
        $users = [];
        $users = $this->getOneDayPostExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getOneDayPostExpiryUsers()
    {
        $sub = $this->getOneDayPostExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOneDayPostExpiryInfo()
    {
        $yesterday = new Carbon('-2 days');
        $today = new Carbon('today');
        $sub = Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', '1')
                ->whereBetween('update_ends_at', [$yesterday, $today]);

        return $sub;
    }
}
