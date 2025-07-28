<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\Bussiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BussinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bussinesses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Bussiness::create([
            'id' => 1,
            'name' => 'Accounting',
            'short' => 'accounting',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 2,
            'name' => 'Airlines/Aviation',
            'short' => 'airlinesaviation',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 3,
            'name' => 'Alternative Dispute Resolution',
            'short' => 'alternative_dispute_resolution',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 4,
            'name' => 'Alternative Medicine',
            'short' => 'alternative_medicine',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 5,
            'name' => 'Animation',
            'short' => 'animation',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 6,
            'name' => 'Apparel & Fashion',
            'short' => 'apparel_fashion',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 7,
            'name' => 'Architecture & Planning',
            'short' => 'architecture_planning',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 8,
            'name' => 'Arts & Crafts',
            'short' => 'arts_crafts',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 9,
            'name' => 'Automotive',
            'short' => 'automotive',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 10,
            'name' => 'Aviation & Aerospace',
            'short' => 'aviation_aerospace',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 11,
            'name' => 'Banking',
            'short' => 'banking',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 12,
            'name' => 'Biotechnology',
            'short' => 'biotechnology',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 13,
            'name' => 'Broadcast Media',
            'short' => 'broadcast_media',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 14,
            'name' => 'Building Materials',
            'short' => 'building_materials',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 15,
            'name' => 'Business Supplies & Equipment',
            'short' => 'business_supplies_equipment',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 16,
            'name' => 'Capital Markets',
            'short' => 'capital_markets',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 17,
            'name' => 'Chemicals',
            'short' => 'chemicals',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 18,
            'name' => 'Civic & Social Organization',
            'short' => 'civic_social_organization',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 19,
            'name' => 'Civil Engineering',
            'short' => 'civil_engineering',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 20,
            'name' => 'Commercial Real Estate',
            'short' => 'commercial_real_estate',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 21,
            'name' => 'Computer & Network Security',
            'short' => 'computer_network_security',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 22,
            'name' => 'Computer Games',
            'short' => 'computer_games',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 23,
            'name' => 'Computer Hardware',
            'short' => 'computer_hardware',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 24,
            'name' => 'Computer Networking',
            'short' => 'computer_networking',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 25,
            'name' => 'Computer Software',
            'short' => 'computer_software',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 26,
            'name' => 'Construction',
            'short' => 'construction',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 27,
            'name' => 'Consumer Electronics',
            'short' => 'consumer_electronics',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 28,
            'name' => 'Consumer Goods',
            'short' => 'consumer_goods',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 29,
            'name' => 'Consumer Services',
            'short' => 'consumer_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 30,
            'name' => 'Cosmetics',
            'short' => 'cosmetics',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 31,
            'name' => 'Dairy',
            'short' => 'dairy',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 32,
            'name' => 'Defense & Space',
            'short' => 'defense_space',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 33,
            'name' => 'Design',
            'short' => 'design',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 34,
            'name' => 'Education Management',
            'short' => 'education_management',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 35,
            'name' => 'E-learning',
            'short' => 'e_learning',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 36,
            'name' => 'Electrical & Electronic Manufacturing',
            'short' => 'electrical_electronic_manufacturing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 37,
            'name' => 'Entertainment',
            'short' => 'entertainment',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 38,
            'name' => 'Environmental Services',
            'short' => 'environmental_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 39,
            'name' => 'Events Services',
            'short' => 'events_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 40,
            'name' => 'Executive Office',
            'short' => 'executive_office',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 41,
            'name' => 'Facilities Services',
            'short' => 'facilities_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 42,
            'name' => 'Farming',
            'short' => 'farming',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 43,
            'name' => 'Financial Services',
            'short' => 'financial_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 44,
            'name' => 'Fine Art',
            'short' => 'fine_art',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 45,
            'name' => 'Fishery',
            'short' => 'fishery',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 46,
            'name' => 'Food & Beverages',
            'short' => 'food_beverages',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 47,
            'name' => 'Food Production',
            'short' => 'food_production',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 48,
            'name' => 'Fundraising',
            'short' => 'fundraising',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 49,
            'name' => 'Furniture',
            'short' => 'furniture',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 50,
            'name' => 'Gambling & Casinos',
            'short' => 'gambling_casinos',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 51,
            'name' => 'Glass, Ceramics & Concrete',
            'short' => 'glass_ceramics_concrete',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 52,
            'name' => 'Government Administration',
            'short' => 'government_administration',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 53,
            'name' => 'Government Relations',
            'short' => 'government_relations',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 54,
            'name' => 'Graphic Design',
            'short' => 'graphic_design',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 55,
            'name' => 'Health, Wellness & Fitness',
            'short' => 'health_wellness_fitness',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 56,
            'name' => 'Higher Education',
            'short' => 'higher_education',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 57,
            'name' => 'Hospital & Health Care',
            'short' => 'hospital_health_care',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 58,
            'name' => 'Hospitality',
            'short' => 'hospitality',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 59,
            'name' => 'Human Resources',
            'short' => 'human_resources',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 60,
            'name' => 'Import & Export',
            'short' => 'import_export',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 61,
            'name' => 'Individual & Family Services',
            'short' => 'individual_family_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 62,
            'name' => 'Industrial Automation',
            'short' => 'industrial_automation',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 63,
            'name' => 'Information Services',
            'short' => 'information_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 64,
            'name' => 'Information Technology & Services',
            'short' => 'information_technology_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 65,
            'name' => 'Insurance',
            'short' => 'insurance',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 66,
            'name' => 'International Affairs',
            'short' => 'international_affairs',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 67,
            'name' => 'nternational Trade & Development',
            'short' => 'nternational_trade_development',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 68,
            'name' => 'Internet',
            'short' => 'internet',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 69,
            'name' => 'Investment Banking/Venture',
            'short' => 'investment_bankingventure',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 70,
            'name' => 'Investment Management',
            'short' => 'investment_management',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 71,
            'name' => 'Judiciary',
            'short' => 'judiciary',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 72,
            'name' => 'Law Enforcement',
            'short' => 'law_enforcement',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 73,
            'name' => 'Law Practice',
            'short' => 'law_practice',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 74,
            'name' => 'Legal Services',
            'short' => 'legal_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 75,
            'name' => 'Legislative Office',
            'short' => 'legislative_office',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 76,
            'name' => 'Leisure & Travel',
            'short' => 'leisure_travel',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 77,
            'name' => 'Libraries',
            'short' => 'libraries',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 78,
            'name' => 'Logistics & Supply Chain',
            'short' => 'logistics_supply_chain',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 79,
            'name' => 'Luxury Goods & Jewelry',
            'short' => 'luxury_goods_jewelry',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 80,
            'name' => 'Machinery',
            'short' => 'machinery',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 81,
            'name' => 'Management Consulting',
            'short' => 'management_consulting',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 82,
            'name' => 'Maritime',
            'short' => 'maritime',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 83,
            'name' => 'Marketing & Advertising',
            'short' => 'marketing_advertising',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 84,
            'name' => 'Market Research',
            'short' => 'market_research',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 85,
            'name' => 'Mechanical or Industrial Engineering',
            'short' => 'mechanical_or_industrial_engineering',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 86,
            'name' => 'Media Production',
            'short' => 'media_production',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 87,
            'name' => 'Medical Device',
            'short' => 'medical_device',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 88,
            'name' => 'Medical Practice',
            'short' => 'medical_practice',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 89,
            'name' => 'Mental Health Care',
            'short' => 'mental_health_care',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 90,
            'name' => 'Military',
            'short' => 'military',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 91,
            'name' => 'Mining & Metals',
            'short' => 'mining_metals',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 92,
            'name' => 'Motion Pictures & Film',
            'short' => 'motion_pictures_film',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 93,
            'name' => 'Museums & Institutions',
            'short' => 'museums_institutions',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 94,
            'name' => 'Music',
            'short' => 'music',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 95,
            'name' => 'Nanotechnology',
            'short' => 'nanotechnology',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 96,
            'name' => 'Newspapers',
            'short' => 'newspapers',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 97,
            'name' => 'Nonprofit Organization Management',
            'short' => 'nonprofit_organization_management',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 98,
            'name' => 'Oil & Energy',
            'short' => 'oil_energy',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 99,
            'name' => 'Online Publishing',
            'short' => 'online_publishing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 100,
            'name' => 'Outsourcing/Offshoring',
            'short' => 'outsourcingoffshoring',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 101,
            'name' => 'Package/Freight Delivery',
            'short' => 'packagefreight_delivery',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 102,
            'name' => 'Packaging & Containers',
            'short' => 'packaging_containers',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 103,
            'name' => 'Paper & Forest Products',
            'short' => 'paper_forest_products',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 104,
            'name' => 'Performing Arts',
            'short' => 'performing_arts',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 105,
            'name' => 'Pharmaceuticals',
            'short' => 'pharmaceuticals',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 106,
            'name' => 'Philanthropy',
            'short' => 'philanthropy',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 107,
            'name' => 'Photography',
            'short' => 'photography',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 108,
            'name' => 'Plastics',
            'short' => 'plastics',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 109,
            'name' => 'Political Organization',
            'short' => 'political_organization',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 110,
            'name' => 'Primary/Secondary',
            'short' => 'primarysecondary',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 111,
            'name' => 'Printing',
            'short' => 'printing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 112,
            'name' => 'Professional Training',
            'short' => 'professional_training',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 113,
            'name' => 'Program Development',
            'short' => 'program_development',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 114,
            'name' => 'Public Policy',
            'short' => 'public_policy',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 115,
            'name' => 'Public Relations',
            'short' => 'public_relations',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 116,
            'name' => 'Public Safety',
            'short' => 'public_safety',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 117,
            'name' => 'Publishing',
            'short' => 'publishing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 118,
            'name' => 'Railroad Manufacture',
            'short' => 'railroad_manufacture',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 119,
            'name' => 'Ranching',
            'short' => 'ranching',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 120,
            'name' => 'Real Estate',
            'short' => 'real_estate',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 121,
            'name' => 'Recreational Facilities & Services',
            'short' => 'recreational_facilities_services',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 122,
            'name' => 'Religious Institutions',
            'short' => 'religious_institutions',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 123,
            'name' => 'Renewables & Environment',
            'short' => 'renewables_environment',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 124,
            'name' => 'Research',
            'short' => 'research',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 125,
            'name' => 'Restaurants',
            'short' => 'restaurants',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 126,
            'name' => 'Retail',
            'short' => 'retail',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 127,
            'name' => 'Security & Investigations',
            'short' => 'security_investigations',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 128,
            'name' => 'Semiconductors',
            'short' => 'semiconductors',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 129,
            'name' => 'Shipbuilding',
            'short' => 'shipbuilding',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 130,
            'name' => 'Sporting Goods',
            'short' => 'sporting_goods',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 131,
            'name' => 'Sports',
            'short' => 'sports',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 132,
            'name' => 'Staffing & Recruiting',
            'short' => 'staffing_recruiting',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 133,
            'name' => 'Supermarkets',
            'short' => 'supermarkets',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 134,
            'name' => 'Telecommunications',
            'short' => 'telecommunications',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 135,
            'name' => 'Textiles',
            'short' => 'textiles',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 136,
            'name' => 'Tobacco',
            'short' => 'tobacco',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 137,
            'name' => 'Translation & Localization',
            'short' => 'translation_localization',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 138,
            'name' => 'Transportation/Trucking/Railroad',
            'short' => 'transportationtruckingrailroad',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 139,
            'name' => 'Utilities',
            'short' => 'utilities',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 140,
            'name' => 'Venture Capital',
            'short' => 'venture_capital',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 141,
            'name' => 'Veterinary',
            'short' => 'veterinary',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 142,
            'name' => 'Warehousing',
            'short' => 'warehousing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 143,
            'name' => 'Wholesale',
            'short' => 'wholesale',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 144,
            'name' => 'Wine & Spirits',
            'short' => 'wine_spirits',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 145,
            'name' => 'Wireless',
            'short' => 'wireless',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 146,
            'name' => 'Writing & Editing',
            'short' => 'writing_editing',
            'created_at' => '2017-01-17 12:53:58',
            'updated_at' => '2017-01-17 12:53:58',
        ]);

        Bussiness::create([
            'id' => 147,
            'name' => 'Others',
            'short' => 'other',
            'created_at' => null,
            'updated_at' => null,
        ]);
    }
}
