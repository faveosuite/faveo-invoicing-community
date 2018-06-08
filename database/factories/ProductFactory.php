<?php

use App\Model\Product\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name'             => 'Helpdesk Advance',
        'description'      => 'Few Lines Of Description',
        'type'             => '2',
        'category'         => 'helpdesk',
        'group'            => 1,
        'require_domain'   => 1,
        'github_owner'     => 'ladybirdweb',
        'github_repository'=> 'faveo-helpdesk-advance',
    ];
});
