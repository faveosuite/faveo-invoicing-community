<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSubdivision extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('states_subdivisions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        State::create([
            'state_subdivision_id' => 12254,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Zabol [Zabul]',
            'state_subdivision_alternate_names' => 'Zabol, Zabul, Zābol',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-ZAB',
        ]);

        State::create([
            'state_subdivision_id' => 12255,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Oruzgan [Uruzgan]',
            'state_subdivision_alternate_names' => 'Oruzgan, Oruzghan, Orūzghān, Uruzgan, Uruzghan, Urūzghān',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-ORU',
        ]);

        State::create([
            'state_subdivision_id' => 12256,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Kondoz [Kunduz]',
            'state_subdivision_alternate_names' => 'Kondoz, Kondūz, Kūnduz, Qondūz',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KDZ',
        ]);

        State::create([
            'state_subdivision_id' => 12257,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Parwan',
            'state_subdivision_alternate_names' => 'Parvan, Parvān, Parwan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-PAR',
        ]);

        State::create([
            'state_subdivision_id' => 12258,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Sar-e Pol',
            'state_subdivision_alternate_names' => 'Sar-e Pol, Sar-i Pol, Sari Pol',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-SAR',
        ]);

        State::create([
            'state_subdivision_id' => 12259,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Paktika',
            'state_subdivision_alternate_names' => 'Paktika',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-PKA',
        ]);

        State::create([
            'state_subdivision_id' => 12260,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Lowgar',
            'state_subdivision_alternate_names' => 'Lawgar, Logar, Loghar, Lowgar, Lowghar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-LOW',
        ]);

        State::create([
            'state_subdivision_id' => 12261,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Kapisa',
            'state_subdivision_alternate_names' => 'Kapesa, Kapisa, Kapissa',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KAP',
        ]);

        State::create([
            'state_subdivision_id' => 12262,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Herat',
            'state_subdivision_alternate_names' => 'Herat',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-HER',
        ]);

        State::create([
            'state_subdivision_id' => 12263,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Kandahar',
            'state_subdivision_alternate_names' => 'Kandahar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KAN',
        ]);

        State::create([
            'state_subdivision_id' => 12264,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Samangan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-SAM',
        ]);

        State::create([
            'state_subdivision_id' => 12265,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Takhar',
            'state_subdivision_alternate_names' => 'Tahar, Takhar, Takhār',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-TAK',
        ]);

        State::create([
            'state_subdivision_id' => 12266,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Wardak [Wardag]',
            'state_subdivision_alternate_names' => 'Vardak, Wardagh, Wardak',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-WAR',
        ]);

        State::create([
            'state_subdivision_id' => 12267,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Badakhshan',
            'state_subdivision_alternate_names' => 'Badaẖšan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-BDS',
        ]);

        State::create([
            'state_subdivision_id' => 12268,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Baghlan',
            'state_subdivision_alternate_names' => 'Baghlan, Baghlān, Baglan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-BGL',
        ]);

        State::create([
            'state_subdivision_id' => 12269,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Bamian',
            'state_subdivision_alternate_names' => 'Bamian, Bamiyan, Bāmīān',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-BAM',
        ]);

        State::create([
            'state_subdivision_id' => 12270,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Faryab',
            'state_subdivision_alternate_names' => 'Fariab',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-FYB',
        ]);

        State::create([
            'state_subdivision_id' => 12271,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Ghazni',
            'state_subdivision_alternate_names' => 'Gazni, Ghazni',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-GHA',
        ]);

        State::create([
            'state_subdivision_id' => 12272,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Badghis',
            'state_subdivision_alternate_names' => 'Badghis, Badgis, Bādghīs',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-BDG',
        ]);

        State::create([
            'state_subdivision_id' => 12273,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Balkh',
            'state_subdivision_alternate_names' => 'Balkh',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-BAL',
        ]);

        State::create([
            'state_subdivision_id' => 12274,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Farah',
            'state_subdivision_alternate_names' => 'Fahrah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-FRA',
        ]);

        State::create([
            'state_subdivision_id' => 12275,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Ghowr',
            'state_subdivision_alternate_names' => 'Ghawr, Ghor, Ghowr, Gōr',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-GHO',
        ]);

        State::create([
            'state_subdivision_id' => 12276,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Khowst',
            'state_subdivision_alternate_names' => 'H̱ōst, Khawst, Khost, Matun, Matūn, H̱awst',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KHO',
        ]);

        State::create([
            'state_subdivision_id' => 12277,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Helmand',
            'state_subdivision_alternate_names' => 'Helmand, Hilmend',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-HEL',
        ]);

        State::create([
            'state_subdivision_id' => 12278,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Kabul [Kabol]',
            'state_subdivision_alternate_names' => 'Kabol, Kābol, Kābul, Kabul',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KAB',
        ]);

        State::create([
            'state_subdivision_id' => 12279,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Laghman',
            'state_subdivision_alternate_names' => 'Laghman, Laghmān, Lagman',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-LAG',
        ]);

        State::create([
            'state_subdivision_id' => 12280,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Nangrahar [Nangarhar]',
            'state_subdivision_alternate_names' => 'Nangarhar, Ningarhar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-NAN',
        ]);

        State::create([
            'state_subdivision_id' => 12281,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Nurestan',
            'state_subdivision_alternate_names' => 'Nooristan, Nouristan, Nurestan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-NUR',
        ]);

        State::create([
            'state_subdivision_id' => 12282,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Paktia',
            'state_subdivision_alternate_names' => 'Paktia, Paktiya, Paktīā',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-PIA',
        ]);

        State::create([
            'state_subdivision_id' => 12283,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Jowzjan',
            'state_subdivision_alternate_names' => 'Jawzjan, Jowzjan, Jowzjān, Jozjan, Juzjan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-JOW',
        ]);

        State::create([
            'state_subdivision_id' => 12284,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Konar [Kunar]',
            'state_subdivision_alternate_names' => 'Konar, Konarhā',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-KNR',
        ]);

        State::create([
            'state_subdivision_id' => 12285,
            'country_code_char2' => 'AF',
            'country_code_char3' => 'AFG',
            'state_subdivision_name' => 'Nimruz',
            'state_subdivision_alternate_names' => 'Chakhānsur, Neemroze, Nimroz, Nimroze',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AF-NIM',
        ]);

        State::create([
            'state_subdivision_id' => 12286,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Tiranë',
            'state_subdivision_alternate_names' => 'Tiranë, Tirana, Tirana',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-TR',
        ]);

        State::create([
            'state_subdivision_id' => 12287,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Shkodër',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-SH',
        ]);

        State::create([
            'state_subdivision_id' => 12288,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Pogradec',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-PG',
        ]);

        State::create([
            'state_subdivision_id' => 12289,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Mirditë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-MR',
        ]);

        State::create([
            'state_subdivision_id' => 12290,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Kolonjë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-ER',
        ]);

        State::create([
            'state_subdivision_id' => 12291,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Mat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-MT',
        ]);

        State::create([
            'state_subdivision_id' => 12292,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Peqin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-PQ',
        ]);

        State::create([
            'state_subdivision_id' => 12293,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Përmet',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-PR',
        ]);

        State::create([
            'state_subdivision_id' => 12294,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Pukë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-PU',
        ]);

        State::create([
            'state_subdivision_id' => 12295,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Sarandë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-SR',
        ]);

        State::create([
            'state_subdivision_id' => 12296,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Skrapar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-SK',
        ]);

        State::create([
            'state_subdivision_id' => 12297,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Tepelenë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-TE',
        ]);

        State::create([
            'state_subdivision_id' => 12298,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Tropojë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-TP',
        ]);

        State::create([
            'state_subdivision_id' => 12299,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Vlorë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-VL',
        ]);

        State::create([
            'state_subdivision_id' => 12300,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Has',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-HA',
        ]);

        State::create([
            'state_subdivision_id' => 12301,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Kavajë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KA',
        ]);

        State::create([
            'state_subdivision_id' => 12302,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Korçë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KO',
        ]);

        State::create([
            'state_subdivision_id' => 12303,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Krujë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KR',
        ]);

        State::create([
            'state_subdivision_id' => 12304,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Kukës',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KU',
        ]);

        State::create([
            'state_subdivision_id' => 12305,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Kurbin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KB',
        ]);

        State::create([
            'state_subdivision_id' => 12306,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Librazhd',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-LB',
        ]);

        State::create([
            'state_subdivision_id' => 12307,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Lushnjë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-LU',
        ]);

        State::create([
            'state_subdivision_id' => 12308,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Malësi e Madhe',
            'state_subdivision_alternate_names' => 'Malesia e Madhe',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-MM',
        ]);

        State::create([
            'state_subdivision_id' => 12309,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Bulqizë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-BU',
        ]);

        State::create([
            'state_subdivision_id' => 12310,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Delvinë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-DL',
        ]);

        State::create([
            'state_subdivision_id' => 12311,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Dibër',
            'state_subdivision_alternate_names' => 'Dibër',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-DI',
        ]);

        State::create([
            'state_subdivision_id' => 12312,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Durrës',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-DR',
        ]);

        State::create([
            'state_subdivision_id' => 12313,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Fier',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-FR',
        ]);

        State::create([
            'state_subdivision_id' => 12314,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Gjirokastër',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-GJ',
        ]);

        State::create([
            'state_subdivision_id' => 12315,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Elbasan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-EL',
        ]);

        State::create([
            'state_subdivision_id' => 12316,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Berat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-BR',
        ]);

        State::create([
            'state_subdivision_id' => 12317,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Devoll',
            'state_subdivision_alternate_names' => 'Devoli',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-DV',
        ]);

        State::create([
            'state_subdivision_id' => 12318,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Gramsh',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-GR',
        ]);

        State::create([
            'state_subdivision_id' => 12319,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Kuçovë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-KC',
        ]);

        State::create([
            'state_subdivision_id' => 12320,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Lezhë',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-LE',
        ]);

        State::create([
            'state_subdivision_id' => 12321,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Mallakastër',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AL-MK',
        ]);

        State::create([
            'state_subdivision_id' => 12322,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Ouargla',
            'state_subdivision_alternate_names' => 'Wargla, Ouargla',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-30',
        ]);

        State::create([
            'state_subdivision_id' => 12323,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'El Oued',
            'state_subdivision_alternate_names' => 'El Ouâdi, El Wad, El Oued',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-39',
        ]);

        State::create([
            'state_subdivision_id' => 12324,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tizi Ouzou',
            'state_subdivision_alternate_names' => 'Tizi-Ouzou',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-15',
        ]);

        State::create([
            'state_subdivision_id' => 12325,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tissemsilt',
            'state_subdivision_alternate_names' => 'Tissemselt, Tissemsilt',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-38',
        ]);

        State::create([
            'state_subdivision_id' => 12326,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Sidi Bel Abbès',
            'state_subdivision_alternate_names' => 'Sidi bel Abbès',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-22',
        ]);

        State::create([
            'state_subdivision_id' => 12327,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Saïda',
            'state_subdivision_alternate_names' => 'Saida, Saïda',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-20',
        ]);

        State::create([
            'state_subdivision_id' => 12328,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Naama',
            'state_subdivision_alternate_names' => 'Naama',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-45',
        ]);

        State::create([
            'state_subdivision_id' => 12329,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Mila',
            'state_subdivision_alternate_names' => 'Mila',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-43',
        ]);

        State::create([
            'state_subdivision_id' => 12330,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Jijel',
            'state_subdivision_alternate_names' => 'Djidjel, Djidjelli, Jijel, Djidjeli',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-18',
        ]);

        State::create([
            'state_subdivision_id' => 12332,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Annaba',
            'state_subdivision_alternate_names' => 'Bona, Bône, Annaba',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-23',
        ]);

        State::create([
            'state_subdivision_id' => 12333,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Aïn Témouchent',
            'state_subdivision_alternate_names' => 'Aïn Temouchent',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-46',
        ]);

        State::create([
            'state_subdivision_id' => 12334,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Batna',
            'state_subdivision_alternate_names' => 'Batna',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-05',
        ]);

        State::create([
            'state_subdivision_id' => 12335,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Béjaïa',
            'state_subdivision_alternate_names' => 'Bejaïa, Béjaïa, Bougie',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-06',
        ]);

        State::create([
            'state_subdivision_id' => 12336,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Blida',
            'state_subdivision_alternate_names' => 'El Boulaida, Blida',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-09',
        ]);

        State::create([
            'state_subdivision_id' => 12337,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Boumerdès',
            'state_subdivision_alternate_names' => 'Boumerdès',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-35',
        ]);

        State::create([
            'state_subdivision_id' => 12338,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Relizane',
            'state_subdivision_alternate_names' => 'Ghilizane, Ighil Izane, Rélizane, Relizane',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-48',
        ]);

        State::create([
            'state_subdivision_id' => 12339,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Khenchela',
            'state_subdivision_alternate_names' => 'Khenchla, Khenchela',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-40',
        ]);

        State::create([
            'state_subdivision_id' => 12340,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Illizi',
            'state_subdivision_alternate_names' => 'Illizi',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-33',
        ]);

        State::create([
            'state_subdivision_id' => 12341,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Bouira',
            'state_subdivision_alternate_names' => 'Bouira',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-10',
        ]);

        State::create([
            'state_subdivision_id' => 12342,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Bordj Bou Arréridj',
            'state_subdivision_alternate_names' => 'Bordj Bou Arreridj',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-34',
        ]);

        State::create([
            'state_subdivision_id' => 12343,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Ghardaïa',
            'state_subdivision_alternate_names' => 'Ghardaïa',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-47',
        ]);

        State::create([
            'state_subdivision_id' => 12344,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Alger',
            'state_subdivision_alternate_names' => 'Al-Jazair, Al-Jazaʿir, El Djazair, al-Jazāʿir, Algier, Alger',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-16',
        ]);

        State::create([
            'state_subdivision_id' => 12345,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Djelfa',
            'state_subdivision_alternate_names' => 'El Djelfa, Djelfa',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-17',
        ]);

        State::create([
            'state_subdivision_id' => 12346,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Médéa',
            'state_subdivision_alternate_names' => 'Lemdiyya, al-Midyah, Médéa',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-26',
        ]);

        State::create([
            'state_subdivision_id' => 12347,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Mostaganem',
            'state_subdivision_alternate_names' => 'Mestghanem, Mustaghanam, Mustaghanim, Mustaġānam, Mostaganem',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-27',
        ]);

        State::create([
            'state_subdivision_id' => 12348,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Constantine',
            'state_subdivision_alternate_names' => 'Ksontina, Qacentina, Qoussantina, Qusanţīnah, Constantine',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-25',
        ]);

        State::create([
            'state_subdivision_id' => 12349,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Skikda',
            'state_subdivision_alternate_names' => 'Skikda',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-21',
        ]);

        State::create([
            'state_subdivision_id' => 12350,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Laghouat',
            'state_subdivision_alternate_names' => 'Laghouat',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-03',
        ]);

        State::create([
            'state_subdivision_id' => 12351,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Aïn Defla',
            'state_subdivision_alternate_names' => 'Aïn Eddefla, Aïn Defla',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-44',
        ]);

        State::create([
            'state_subdivision_id' => 12352,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Béchar',
            'state_subdivision_alternate_names' => 'Béchar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-08',
        ]);

        State::create([
            'state_subdivision_id' => 12353,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'El Bayadh',
            'state_subdivision_alternate_names' => 'El Bayadh',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-32',
        ]);

        State::create([
            'state_subdivision_id' => 12354,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Biskra',
            'state_subdivision_alternate_names' => 'Beskra, Biskara, Biskra',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-07',
        ]);

        State::create([
            'state_subdivision_id' => 12355,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Sétif',
            'state_subdivision_alternate_names' => 'Setif, Stif, Sétif',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-19',
        ]);

        State::create([
            'state_subdivision_id' => 12356,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Souk Ahras',
            'state_subdivision_alternate_names' => 'Souq Ahras, Souk Ahras',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-41',
        ]);

        State::create([
            'state_subdivision_id' => 12357,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'El Tarf',
            'state_subdivision_alternate_names' => 'El Taref, at-Tarf, El Tarf',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-36',
        ]);

        State::create([
            'state_subdivision_id' => 12358,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tébessa',
            'state_subdivision_alternate_names' => 'Tbessa, Tébessa',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-12',
        ]);

        State::create([
            'state_subdivision_id' => 12359,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tindouf',
            'state_subdivision_alternate_names' => 'Tindouf',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-37',
        ]);

        State::create([
            'state_subdivision_id' => 12360,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tiaret',
            'state_subdivision_alternate_names' => 'Tihert, Tiaret',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-14',
        ]);

        State::create([
            'state_subdivision_id' => 12361,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Oum el Bouaghi',
            'state_subdivision_alternate_names' => 'Canrobert, Oum el Bouaghi',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-04',
        ]);

        State::create([
            'state_subdivision_id' => 12362,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Oran',
            'state_subdivision_alternate_names' => 'Ouahran, Oran',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-31',
        ]);

        State::create([
            'state_subdivision_id' => 12363,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Adrar',
            'state_subdivision_alternate_names' => 'Adrar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-01',
        ]);

        State::create([
            'state_subdivision_id' => 12364,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Msila',
            'state_subdivision_alternate_names' => 'MʿSila, Msila',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-28',
        ]);

        State::create([
            'state_subdivision_id' => 12365,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Mascara',
            'state_subdivision_alternate_names' => 'Mouaskar, Mascara',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-29',
        ]);

        State::create([
            'state_subdivision_id' => 12366,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Guelma',
            'state_subdivision_alternate_names' => 'Guelma',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-24',
        ]);

        State::create([
            'state_subdivision_id' => 12367,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Chlef',
            'state_subdivision_alternate_names' => 'Al Asnam, Al Asnām, Chelef, Chelf, Chlef, Chlif, Ech Cheliff, El Asnam',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-02',
        ]);

        State::create([
            'state_subdivision_id' => 12368,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tamanghasset',
            'state_subdivision_alternate_names' => 'Fort-Laperrine, Tamanghist, Tamanrasset',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-11',
        ]);

        State::create([
            'state_subdivision_id' => 12369,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tipaza',
            'state_subdivision_alternate_names' => 'Tipaza',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-42',
        ]);

        State::create([
            'state_subdivision_id' => 12370,
            'country_code_char2' => 'DZ',
            'country_code_char3' => 'DZA',
            'state_subdivision_name' => 'Tlemcen',
            'state_subdivision_alternate_names' => 'Tilimsen, Tlemcen',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'DZ-13',
        ]);

        State::create([
            'state_subdivision_id' => 12378,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Sant Julià de Lòria',
            'state_subdivision_alternate_names' => 'Saint Julia de Loria',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-06',
        ]);

        State::create([
            'state_subdivision_id' => 12379,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Ordino',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-05',
        ]);

        State::create([
            'state_subdivision_id' => 12380,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Escaldes-Engordany',
            'state_subdivision_alternate_names' => 'Les Escaldes',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-08',
        ]);

        State::create([
            'state_subdivision_id' => 12381,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Canillo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-02',
        ]);

        State::create([
            'state_subdivision_id' => 12382,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Andorra la Vella',
            'state_subdivision_alternate_names' => 'Andorra la Vieja, Andorre-la-Vieille',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-07',
        ]);

        State::create([
            'state_subdivision_id' => 12383,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'Encamp',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-03',
        ]);

        State::create([
            'state_subdivision_id' => 12384,
            'country_code_char2' => 'AD',
            'country_code_char3' => 'AND',
            'state_subdivision_name' => 'La Massana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AD-04',
        ]);

        State::create([
            'state_subdivision_id' => 12385,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Zaire',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-ZAI',
        ]);

        State::create([
            'state_subdivision_id' => 12386,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Lunda Sul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-LSU',
        ]);

        State::create([
            'state_subdivision_id' => 12387,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Luanda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-LUA',
        ]);

        State::create([
            'state_subdivision_id' => 12388,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Cuanza Norte',
            'state_subdivision_alternate_names' => 'Cuanza-Norte',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-CNO',
        ]);

        State::create([
            'state_subdivision_id' => 12389,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Cunene',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-CNN',
        ]);

        State::create([
            'state_subdivision_id' => 12390,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Bengo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-BGO',
        ]);

        State::create([
            'state_subdivision_id' => 12391,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Benguela',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-BGU',
        ]);

        State::create([
            'state_subdivision_id' => 12392,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Cabinda',
            'state_subdivision_alternate_names' => 'Kabinda',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-CAB',
        ]);

        State::create([
            'state_subdivision_id' => 12393,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Huambo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-HUA',
        ]);

        State::create([
            'state_subdivision_id' => 12394,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Cuando-Cubango',
            'state_subdivision_alternate_names' => 'Cuando-Cubango',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-CCU',
        ]);

        State::create([
            'state_subdivision_id' => 12395,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Bié',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-BIE',
        ]);

        State::create([
            'state_subdivision_id' => 12396,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Huíla',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-HUI',
        ]);

        State::create([
            'state_subdivision_id' => 12397,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Cuanza Sul',
            'state_subdivision_alternate_names' => 'Cuanza-Sul',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-CUS',
        ]);

        State::create([
            'state_subdivision_id' => 12398,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Lunda Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-LNO',
        ]);

        State::create([
            'state_subdivision_id' => 12399,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Malange',
            'state_subdivision_alternate_names' => 'Malange',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-MAL',
        ]);

        State::create([
            'state_subdivision_id' => 12400,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Uíge',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-UIG',
        ]);

        State::create([
            'state_subdivision_id' => 12401,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Moxico',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-MOX',
        ]);

        State::create([
            'state_subdivision_id' => 12402,
            'country_code_char2' => 'AO',
            'country_code_char3' => 'AGO',
            'state_subdivision_name' => 'Namibe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AO-NAM',
        ]);

        State::create([
            'state_subdivision_id' => 12425,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Redonda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Dependency',
            'state_subdivision_code' => 'AG-11',
        ]);

        State::create([
            'state_subdivision_id' => 12426,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Barbuda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Dependency',
            'state_subdivision_code' => 'AG-10',
        ]);

        State::create([
            'state_subdivision_id' => 12427,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint George',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-03',
        ]);

        State::create([
            'state_subdivision_id' => 12428,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint Mary',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-05',
        ]);

        State::create([
            'state_subdivision_id' => 12429,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint Paul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-06',
        ]);

        State::create([
            'state_subdivision_id' => 12430,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint Philip',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-08',
        ]);

        State::create([
            'state_subdivision_id' => 12431,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint Johns',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-04',
        ]);

        State::create([
            'state_subdivision_id' => 12432,
            'country_code_char2' => 'AG',
            'country_code_char3' => 'ATG',
            'state_subdivision_name' => 'Saint Peter',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'AG-07',
        ]);

        State::create([
            'state_subdivision_id' => 12433,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Santiago del Estero',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-G',
        ]);

        State::create([
            'state_subdivision_id' => 12434,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Santa Cruz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-Z',
        ]);

        State::create([
            'state_subdivision_id' => 12435,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Salta',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-A',
        ]);

        State::create([
            'state_subdivision_id' => 12436,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'La Rioja',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-F',
        ]);

        State::create([
            'state_subdivision_id' => 12437,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Ciudad Autónoma de Buenos Aires',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'federal district',
            'state_subdivision_code' => 'AR-C',
        ]);

        State::create([
            'state_subdivision_id' => 12438,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Catamarca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-K',
        ]);

        State::create([
            'state_subdivision_id' => 12439,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Buenos Aires',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-B',
        ]);

        State::create([
            'state_subdivision_id' => 12440,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Chaco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-H',
        ]);

        State::create([
            'state_subdivision_id' => 12441,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Córdoba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-X',
        ]);

        State::create([
            'state_subdivision_id' => 12442,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Corrientes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-W',
        ]);

        State::create([
            'state_subdivision_id' => 12443,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Entre Ríos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-E',
        ]);

        State::create([
            'state_subdivision_id' => 12444,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Jujuy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-Y',
        ]);

        State::create([
            'state_subdivision_id' => 12445,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'La Pampa',
            'state_subdivision_alternate_names' => 'Pampa',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-L',
        ]);

        State::create([
            'state_subdivision_id' => 12446,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Mendoza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-M',
        ]);

        State::create([
            'state_subdivision_id' => 12447,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Neuquén',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-Q',
        ]);

        State::create([
            'state_subdivision_id' => 12448,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Río Negro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-R',
        ]);

        State::create([
            'state_subdivision_id' => 12449,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'San Juan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-J',
        ]);

        State::create([
            'state_subdivision_id' => 12450,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'San Luis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-D',
        ]);

        State::create([
            'state_subdivision_id' => 12451,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Santa Fe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-S',
        ]);

        State::create([
            'state_subdivision_id' => 12452,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Tierra del Fuego',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-V',
        ]);

        State::create([
            'state_subdivision_id' => 12453,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Tucumán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-T',
        ]);

        State::create([
            'state_subdivision_id' => 12454,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Chubut',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-U',
        ]);

        State::create([
            'state_subdivision_id' => 12455,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Formosa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-P',
        ]);

        State::create([
            'state_subdivision_id' => 12456,
            'country_code_char2' => 'AR',
            'country_code_char3' => 'ARG',
            'state_subdivision_name' => 'Misiones',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'AR-N',
        ]);

        State::create([
            'state_subdivision_id' => 12457,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Tavuš',
            'state_subdivision_alternate_names' => 'Tavoush',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-TV',
        ]);

        State::create([
            'state_subdivision_id' => 12458,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Vayoc Jor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-VD',
        ]);

        State::create([
            'state_subdivision_id' => 12459,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Širak',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-SH',
        ]);

        State::create([
            'state_subdivision_id' => 12460,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Gegark',
            'state_subdivision_alternate_names' => 'unik',
            'primary_level_name' => '',
            'state_subdivision_code' => 'Gegharkunick',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-GR',
        ]);

        State::create([
            'state_subdivision_id' => 12461,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Ararat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-AR',
        ]);

        State::create([
            'state_subdivision_id' => 12462,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Kotayk',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Kotaik',
            'state_subdivision_code' => 'AM-KT',
        ]);

        State::create([
            'state_subdivision_id' => 12463,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Syunik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-SU',
        ]);

        State::create([
            'state_subdivision_id' => 12464,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Erevan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AM-ER',
        ]);

        State::create([
            'state_subdivision_id' => 12465,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Lo?y',
            'state_subdivision_alternate_names' => 'Lorri',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-LO',
        ]);

        State::create([
            'state_subdivision_id' => 12466,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Aragac?otn',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-AG',
        ]);

        State::create([
            'state_subdivision_id' => 12467,
            'country_code_char2' => 'AM',
            'country_code_char3' => 'ARM',
            'state_subdivision_name' => 'Armavir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'AM-AV',
        ]);

        State::create([
            'state_subdivision_id' => 12470,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Western Australia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-WA',
        ]);

        State::create([
            'state_subdivision_id' => 12471,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Australian Capital Territory',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'territory',
            'state_subdivision_code' => 'AU-ACT',
        ]);

        State::create([
            'state_subdivision_id' => 12472,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'South Australia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-SA',
        ]);

        State::create([
            'state_subdivision_id' => 12473,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Victoria',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-VIC',
        ]);

        State::create([
            'state_subdivision_id' => 12474,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Tasmania',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-TAS',
        ]);

        State::create([
            'state_subdivision_id' => 12475,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Northern Territory',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'territory',
            'state_subdivision_code' => 'AU-NT',
        ]);

        State::create([
            'state_subdivision_id' => 12476,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'Queensland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-QLD',
        ]);

        State::create([
            'state_subdivision_id' => 12477,
            'country_code_char2' => 'AU',
            'country_code_char3' => 'AUS',
            'state_subdivision_name' => 'New South Wales',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'AU-NSW',
        ]);

        State::create([
            'state_subdivision_id' => 12478,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Wien',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-9',
        ]);

        State::create([
            'state_subdivision_id' => 12479,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Salzburg',
            'state_subdivision_alternate_names' => 'Salzbourg',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-5',
        ]);

        State::create([
            'state_subdivision_id' => 12480,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Burgenland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-1',
        ]);

        State::create([
            'state_subdivision_id' => 12481,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Kärnten',
            'state_subdivision_alternate_names' => 'Carinthia, Koroška',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-2',
        ]);

        State::create([
            'state_subdivision_id' => 12482,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Oberösterreich',
            'state_subdivision_alternate_names' => 'Upper Austria',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-4',
        ]);

        State::create([
            'state_subdivision_id' => 12483,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Steiermark',
            'state_subdivision_alternate_names' => 'Styria',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-6',
        ]);

        State::create([
            'state_subdivision_id' => 12484,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Vorarlberg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-8',
        ]);

        State::create([
            'state_subdivision_id' => 12485,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Niederösterreich',
            'state_subdivision_alternate_names' => 'Lower Austria',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-3',
        ]);

        State::create([
            'state_subdivision_id' => 12486,
            'country_code_char2' => 'AT',
            'country_code_char3' => 'AUT',
            'state_subdivision_name' => 'Tirol',
            'state_subdivision_alternate_names' => 'Tyrol',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'AT-7',
        ]);

        State::create([
            'state_subdivision_id' => 12487,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Zängilan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ZAN',
        ]);

        State::create([
            'state_subdivision_id' => 12488,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Yardimli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-YAR',
        ]);

        State::create([
            'state_subdivision_id' => 12489,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xocali',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-XCI',
        ]);

        State::create([
            'state_subdivision_id' => 12490,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xankändi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-XA',
        ]);

        State::create([
            'state_subdivision_id' => 12491,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Sumqayit',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-SM',
        ]);

        State::create([
            'state_subdivision_id' => 12492,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Samux',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SMX',
        ]);

        State::create([
            'state_subdivision_id' => 12493,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Agstafa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AGA',
        ]);

        State::create([
            'state_subdivision_id' => 12494,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Babäk',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-BAB',
        ]);

        State::create([
            'state_subdivision_id' => 12495,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Bärdä',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-BAR',
        ]);

        State::create([
            'state_subdivision_id' => 12496,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Cäbrayil',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-CAB',
        ]);

        State::create([
            'state_subdivision_id' => 12497,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Daskäsän',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-DAS',
        ]);

        State::create([
            'state_subdivision_id' => 12498,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Äli Bayramli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-AB',
        ]);

        State::create([
            'state_subdivision_id' => 12499,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Gäncä',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-GA',
        ]);

        State::create([
            'state_subdivision_id' => 12500,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Haciqabul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-HAC',
        ]);

        State::create([
            'state_subdivision_id' => 12501,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Ismayilli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ISM',
        ]);

        State::create([
            'state_subdivision_id' => 12502,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Sahbuz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAH',
        ]);

        State::create([
            'state_subdivision_id' => 12503,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Samaxi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SMI',
        ]);

        State::create([
            'state_subdivision_id' => 12504,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Sädäräk',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAD',
        ]);

        State::create([
            'state_subdivision_id' => 12505,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Säki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAK',
        ]);

        State::create([
            'state_subdivision_id' => 12506,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Särur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAR',
        ]);

        State::create([
            'state_subdivision_id' => 12507,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Siyäzän',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SIY',
        ]);

        State::create([
            'state_subdivision_id' => 12508,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Susa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SUS',
        ]);

        State::create([
            'state_subdivision_id' => 12509,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Tärtär',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-TAR',
        ]);

        State::create([
            'state_subdivision_id' => 12510,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Ucar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-UCA',
        ]);

        State::create([
            'state_subdivision_id' => 12512,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Goranboy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-GOR',
        ]);

        State::create([
            'state_subdivision_id' => 12513,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Göyçay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-GOY',
        ]);

        State::create([
            'state_subdivision_id' => 12514,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Imisli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-IMI',
        ]);

        State::create([
            'state_subdivision_id' => 12515,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Kälbäcär',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-KAL',
        ]);

        State::create([
            'state_subdivision_id' => 12516,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Kürdämir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-KUR',
        ]);

        State::create([
            'state_subdivision_id' => 12517,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Länkäran',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-LAN',
        ]);

        State::create([
            'state_subdivision_id' => 12518,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Lerik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-LER',
        ]);

        State::create([
            'state_subdivision_id' => 12519,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Mingäçevir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-MI',
        ]);

        State::create([
            'state_subdivision_id' => 12520,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Naftalan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-NA',
        ]);

        State::create([
            'state_subdivision_id' => 12521,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Abseron',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ABS',
        ]);

        State::create([
            'state_subdivision_id' => 12522,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Agcabädi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AGC',
        ]);

        State::create([
            'state_subdivision_id' => 12523,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Agdas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AGS',
        ]);

        State::create([
            'state_subdivision_id' => 12524,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Agsu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AGU',
        ]);

        State::create([
            'state_subdivision_id' => 12525,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Astara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AST',
        ]);

        State::create([
            'state_subdivision_id' => 12526,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Baki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-BA',
        ]);

        State::create([
            'state_subdivision_id' => 12527,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Balakän',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-BAL',
        ]);

        State::create([
            'state_subdivision_id' => 12528,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Beyläqan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-BEY',
        ]);

        State::create([
            'state_subdivision_id' => 12529,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Biläsuvar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-BIL',
        ]);

        State::create([
            'state_subdivision_id' => 12530,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Cälilabab',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-CAL',
        ]);

        State::create([
            'state_subdivision_id' => 12531,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Culfa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-CUL',
        ]);

        State::create([
            'state_subdivision_id' => 12532,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Däväçi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-DAV',
        ]);

        State::create([
            'state_subdivision_id' => 12533,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Füzuli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-FUZ',
        ]);

        State::create([
            'state_subdivision_id' => 12534,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Gädäbäy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-GAD',
        ]);

        State::create([
            'state_subdivision_id' => 12535,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Neftçala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-NEF',
        ]);

        State::create([
            'state_subdivision_id' => 12536,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Ordubad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ORD',
        ]);

        State::create([
            'state_subdivision_id' => 12537,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qax',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QAX',
        ]);

        State::create([
            'state_subdivision_id' => 12538,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qäbälä',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QAB',
        ]);

        State::create([
            'state_subdivision_id' => 12539,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qobustan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QOB',
        ]);

        State::create([
            'state_subdivision_id' => 12540,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qubadli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QBI',
        ]);

        State::create([
            'state_subdivision_id' => 12541,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qusar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QUS',
        ]);

        State::create([
            'state_subdivision_id' => 12542,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Sabirabad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAB',
        ]);

        State::create([
            'state_subdivision_id' => 12543,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xaçmaz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-XAC',
        ]);

        State::create([
            'state_subdivision_id' => 12544,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xanlar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-XAN',
        ]);

        State::create([
            'state_subdivision_id' => 12545,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xizi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-XIZ',
        ]);

        State::create([
            'state_subdivision_id' => 12546,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Xocavänd',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-XVD',
        ]);

        State::create([
            'state_subdivision_id' => 12547,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Yevlax',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-YEV',
        ]);

        State::create([
            'state_subdivision_id' => 12548,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Zaqatala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ZAQ',
        ]);

        State::create([
            'state_subdivision_id' => 12549,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Zärdab',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-ZAR',
        ]);

        State::create([
            'state_subdivision_id' => 12550,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Agdam',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-AGM',
        ]);

        State::create([
            'state_subdivision_id' => 12551,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Laçin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-LAC',
        ]);

        State::create([
            'state_subdivision_id' => 12552,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Masalli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-MAS',
        ]);

        State::create([
            'state_subdivision_id' => 12553,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Naxçivan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'AZ-NX',
        ]);

        State::create([
            'state_subdivision_id' => 12554,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Oguz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-OGU',
        ]);

        State::create([
            'state_subdivision_id' => 12555,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Qazax',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QAZ',
        ]);

        State::create([
            'state_subdivision_id' => 12556,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Quba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-QBA',
        ]);

        State::create([
            'state_subdivision_id' => 12557,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Saatli',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAT',
        ]);

        State::create([
            'state_subdivision_id' => 12558,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Salyan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SAL',
        ]);

        State::create([
            'state_subdivision_id' => 12559,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Sämkir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-SKR',
        ]);

        State::create([
            'state_subdivision_id' => 12560,
            'country_code_char2' => 'AZ',
            'country_code_char3' => 'AZE',
            'state_subdivision_name' => 'Tovuz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'AZ-TOV',
        ]);

        State::create([
            'state_subdivision_id' => 12564,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Mayaguana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-MG',
        ]);

        State::create([
            'state_subdivision_id' => 12565,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Inagua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-IN',
        ]);

        State::create([
            'state_subdivision_id' => 12566,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'City of Freeport',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'BS-FP',
        ]);

        State::create([
            'state_subdivision_id' => 12567,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Acklins  Islands',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-AK',
        ]);

        State::create([
            'state_subdivision_id' => 12568,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Ragged Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-RI',
        ]);

        State::create([
            'state_subdivision_id' => 12571,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Green Turtle Cay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-GT',
        ]);

        State::create([
            'state_subdivision_id' => 12572,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Grand Cay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-GC',
        ]);

        State::create([
            'state_subdivision_id' => 12574,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Bimini and Cat Cay',
            'state_subdivision_alternate_names' => 'Bimini Islands',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-BI',
        ]);

        State::create([
            'state_subdivision_id' => 12575,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Cat Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-CI',
        ]);

        State::create([
            'state_subdivision_id' => 12578,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Exuma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-EX',
        ]);

        State::create([
            'state_subdivision_id' => 12579,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Harbour Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-HI',
        ]);

        State::create([
            'state_subdivision_id' => 12580,
            'country_code_char2' => 'BS',
            'country_code_char3' => 'BHS',
            'state_subdivision_name' => 'Long Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BS-LI',
        ]);

        State::create([
            'state_subdivision_id' => 12581,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Al Wustá',
            'state_subdivision_alternate_names' => 'Central, al-Mintaqah al-Wusta',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'BH-16',
        ]);

        State::create([
            'state_subdivision_id' => 12582,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Ash Shamaliyah',
            'state_subdivision_alternate_names' => 'Northern, al-Mintaqa ash Shamaliyah, ash Shamaliyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'BH-17',
        ]);

        State::create([
            'state_subdivision_id' => 12583,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Al Muharraq',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'BH-15',
        ]);

        State::create([
            'state_subdivision_id' => 12589,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Al Manamah',
            'state_subdivision_alternate_names' => 'Manāmah, al-Manāmah, Manama, Manama, Manama',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'BH-13',
        ]);

        State::create([
            'state_subdivision_id' => 12592,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Al Janubiyah',
            'state_subdivision_alternate_names' => 'Eastern, Hawa, Juzur H̨awār, Southern, ash Sharqiyah, aš-Šarqīyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'BH-14',
        ]);

        State::create([
            'state_subdivision_id' => 12593,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Tangail zila',
            'state_subdivision_alternate_names' => 'Tangail, Tangayal',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-63',
        ]);

        State::create([
            'state_subdivision_id' => 12594,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Sirajganj zila',
            'state_subdivision_alternate_names' => 'Serajgonj, Sirajganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-59',
        ]);

        State::create([
            'state_subdivision_id' => 12595,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Sherpur zila',
            'state_subdivision_alternate_names' => 'Sherpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-57',
        ]);

        State::create([
            'state_subdivision_id' => 12596,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Rajshahi zila',
            'state_subdivision_alternate_names' => 'Rajshahi, Rampur Boalia',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-54',
        ]);

        State::create([
            'state_subdivision_id' => 12597,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Panchagarh zila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-52',
        ]);

        State::create([
            'state_subdivision_id' => 12598,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Nawabganj zila',
            'state_subdivision_alternate_names' => 'Nawabganj, Nawabgonj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-45',
        ]);

        State::create([
            'state_subdivision_id' => 12599,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Narayanganj zila',
            'state_subdivision_alternate_names' => 'Narayanganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-40',
        ]);

        State::create([
            'state_subdivision_id' => 12600,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Bagerhat zila',
            'state_subdivision_alternate_names' => 'Bagarhat, Bagerhat, Bagherhat, Basabari, Bāsābāri',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-05',
        ]);

        State::create([
            'state_subdivision_id' => 12601,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Bandarban zila',
            'state_subdivision_alternate_names' => 'Bandarban',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-01',
        ]);

        State::create([
            'state_subdivision_id' => 12602,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Barguna zila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-02',
        ]);

        State::create([
            'state_subdivision_id' => 12603,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Jessore zila',
            'state_subdivision_alternate_names' => 'Jessore, Jessur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-22',
        ]);

        State::create([
            'state_subdivision_id' => 12604,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Jhenaidah zila',
            'state_subdivision_alternate_names' => 'Jhanaydah, Jhanidah, Jhanīdāh, Jhenaida, Jhenida',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-23',
        ]);

        State::create([
            'state_subdivision_id' => 12605,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Kishoreganj zila',
            'state_subdivision_alternate_names' => 'Kishoreganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-26',
        ]);

        State::create([
            'state_subdivision_id' => 12606,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Comilla zila',
            'state_subdivision_alternate_names' => 'Comilla, Komilla',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-08',
        ]);

        State::create([
            'state_subdivision_id' => 12607,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Kushtia zila',
            'state_subdivision_alternate_names' => 'Kushtia, Kushtiya',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-30',
        ]);

        State::create([
            'state_subdivision_id' => 12608,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Lalmonirhat zila',
            'state_subdivision_alternate_names' => 'Lalmanir Hat, Lalmonirhat',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-32',
        ]);

        State::create([
            'state_subdivision_id' => 12609,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Magura zila',
            'state_subdivision_alternate_names' => 'Magura',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-37',
        ]);

        State::create([
            'state_subdivision_id' => 12610,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Manikganj zila',
            'state_subdivision_alternate_names' => 'Manikganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-33',
        ]);

        State::create([
            'state_subdivision_id' => 12611,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Meherpur zila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-39',
        ]);

        State::create([
            'state_subdivision_id' => 12612,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Barisal zila',
            'state_subdivision_alternate_names' => 'Barisal',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-06',
        ]);

        State::create([
            'state_subdivision_id' => 12613,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Bhola zila',
            'state_subdivision_alternate_names' => 'Bhola',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-07',
        ]);

        State::create([
            'state_subdivision_id' => 12614,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Bogra zila',
            'state_subdivision_alternate_names' => 'Bogora, Bogra, Borga Thana',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-03',
        ]);

        State::create([
            'state_subdivision_id' => 12615,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Chandpur zila',
            'state_subdivision_alternate_names' => 'Chandipur, Chandpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-09',
        ]);

        State::create([
            'state_subdivision_id' => 12616,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Dhaka zila',
            'state_subdivision_alternate_names' => 'Dacca, Dakka, Dhaka',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-13',
        ]);

        State::create([
            'state_subdivision_id' => 12617,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Faridpur zila',
            'state_subdivision_alternate_names' => 'Faridpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-15',
        ]);

        State::create([
            'state_subdivision_id' => 12618,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Feni zila',
            'state_subdivision_alternate_names' => 'Feni',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-16',
        ]);

        State::create([
            'state_subdivision_id' => 12619,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Gopalganj zila',
            'state_subdivision_alternate_names' => 'Gopalganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-17',
        ]);

        State::create([
            'state_subdivision_id' => 12620,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Jaipurhat zila',
            'state_subdivision_alternate_names' => 'Jaipur Hat, Jaipurhat, Joypurhat',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-24',
        ]);

        State::create([
            'state_subdivision_id' => 12621,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Mymensingh zila',
            'state_subdivision_alternate_names' => 'Mymensingh, Nasirabad, Nasirābād',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-34',
        ]);

        State::create([
            'state_subdivision_id' => 12622,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Moulvibazar zila',
            'state_subdivision_alternate_names' => 'Maulvi Bazar, Moulvi Bazar',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-38',
        ]);

        State::create([
            'state_subdivision_id' => 12623,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Munshiganj zila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-35',
        ]);

        State::create([
            'state_subdivision_id' => 12624,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Narail zila',
            'state_subdivision_alternate_names' => 'Narail, Naral',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-43',
        ]);

        State::create([
            'state_subdivision_id' => 12625,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Narsingdi zila',
            'state_subdivision_alternate_names' => 'Narsinghdi',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-42',
        ]);

        State::create([
            'state_subdivision_id' => 12626,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Naogaon zila',
            'state_subdivision_alternate_names' => 'Naogaon, Naugaon',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-48',
        ]);

        State::create([
            'state_subdivision_id' => 12627,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Netrakona zila',
            'state_subdivision_alternate_names' => 'Netrakona, Netrokana',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-41',
        ]);

        State::create([
            'state_subdivision_id' => 12628,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Noakhali zila',
            'state_subdivision_alternate_names' => 'Noakhali',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-47',
        ]);

        State::create([
            'state_subdivision_id' => 12629,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Pabna zila',
            'state_subdivision_alternate_names' => 'Pabna',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-49',
        ]);

        State::create([
            'state_subdivision_id' => 12630,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Brahmanbaria zila',
            'state_subdivision_alternate_names' => 'Brahman Bariya, Brahmanbaria',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-04',
        ]);

        State::create([
            'state_subdivision_id' => 12631,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Chittagong zila',
            'state_subdivision_alternate_names' => 'Chattagam, Chittagong',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-10',
        ]);

        State::create([
            'state_subdivision_id' => 12632,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Chuadanga zila',
            'state_subdivision_alternate_names' => 'Chuadanga',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-12',
        ]);

        State::create([
            'state_subdivision_id' => 12633,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Dinajpur zila',
            'state_subdivision_alternate_names' => 'Dinajpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-14',
        ]);

        State::create([
            'state_subdivision_id' => 12634,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Gaibandha zila',
            'state_subdivision_alternate_names' => 'Gaibanda, Gaibandah, Gaibandha, Gaybanda, Gaybandah',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-19',
        ]);

        State::create([
            'state_subdivision_id' => 12635,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Gazipur zila',
            'state_subdivision_alternate_names' => 'Gajipur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-18',
        ]);

        State::create([
            'state_subdivision_id' => 12636,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Habiganj zila',
            'state_subdivision_alternate_names' => 'Habiganj, Hobiganj, Hobigonj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-20',
        ]);

        State::create([
            'state_subdivision_id' => 12637,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Jamalpur zila',
            'state_subdivision_alternate_names' => 'Jamalpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-21',
        ]);

        State::create([
            'state_subdivision_id' => 12638,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Jhalakati zila',
            'state_subdivision_alternate_names' => 'Jhalakati, Jhalokati',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-25',
        ]);

        State::create([
            'state_subdivision_id' => 12639,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Khagrachari zila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-29',
        ]);

        State::create([
            'state_subdivision_id' => 12640,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Khulna zila',
            'state_subdivision_alternate_names' => 'Khulna',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-27',
        ]);

        State::create([
            'state_subdivision_id' => 12641,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Cox',
            'state_subdivision_alternate_names' => 's Bazar zila',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-11',
        ]);

        State::create([
            'state_subdivision_id' => 12642,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Kurigram zila',
            'state_subdivision_alternate_names' => 'Kurigram',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-28',
        ]);

        State::create([
            'state_subdivision_id' => 12643,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Lakshmipur zila',
            'state_subdivision_alternate_names' => 'Lakshmipur, Laksmipur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-31',
        ]);

        State::create([
            'state_subdivision_id' => 12644,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Madaripur zila',
            'state_subdivision_alternate_names' => 'Madaripur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-36',
        ]);

        State::create([
            'state_subdivision_id' => 12645,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Patuakhali zila',
            'state_subdivision_alternate_names' => 'Patukhali',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-51',
        ]);

        State::create([
            'state_subdivision_id' => 12646,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Rajbari zila',
            'state_subdivision_alternate_names' => 'Rajbari',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-53',
        ]);

        State::create([
            'state_subdivision_id' => 12647,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Rangamati zila',
            'state_subdivision_alternate_names' => 'Rangamati',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-56',
        ]);

        State::create([
            'state_subdivision_id' => 12648,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Rangpur zila',
            'state_subdivision_alternate_names' => 'Rangpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-55',
        ]);

        State::create([
            'state_subdivision_id' => 12649,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Shariatpur zila',
            'state_subdivision_alternate_names' => 'Shariatpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-62',
        ]);

        State::create([
            'state_subdivision_id' => 12650,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Sylhet zila',
            'state_subdivision_alternate_names' => 'Silhat, Sylhet',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-60',
        ]);

        State::create([
            'state_subdivision_id' => 12651,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Sunamganj zila',
            'state_subdivision_alternate_names' => 'Shunamganj, Sunamganj',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-61',
        ]);

        State::create([
            'state_subdivision_id' => 12652,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Thakurgaon zila',
            'state_subdivision_alternate_names' => 'Thakurgaon',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-64',
        ]);

        State::create([
            'state_subdivision_id' => 12653,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Natore zila',
            'state_subdivision_alternate_names' => 'Nator, Natore',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-44',
        ]);

        State::create([
            'state_subdivision_id' => 12654,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Nilphamari zila',
            'state_subdivision_alternate_names' => 'Nilphamari',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-46',
        ]);

        State::create([
            'state_subdivision_id' => 12655,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Pirojpur zila',
            'state_subdivision_alternate_names' => 'Perojpur, Pirojpur',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-50',
        ]);

        State::create([
            'state_subdivision_id' => 12656,
            'country_code_char2' => 'BD',
            'country_code_char3' => 'BGD',
            'state_subdivision_name' => 'Satkhira zila',
            'state_subdivision_alternate_names' => 'Satkhira',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BD-58',
        ]);

        State::create([
            'state_subdivision_id' => 12657,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Thomas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-11',
        ]);

        State::create([
            'state_subdivision_id' => 12658,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Michael',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-08',
        ]);

        State::create([
            'state_subdivision_id' => 12659,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Joseph',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-06',
        ]);

        State::create([
            'state_subdivision_id' => 12660,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint James',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-04',
        ]);

        State::create([
            'state_subdivision_id' => 12661,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Christ Church',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-01',
        ]);

        State::create([
            'state_subdivision_id' => 12662,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint George',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-03',
        ]);

        State::create([
            'state_subdivision_id' => 12663,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Andrew',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-02',
        ]);

        State::create([
            'state_subdivision_id' => 12664,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint John',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-05',
        ]);

        State::create([
            'state_subdivision_id' => 12665,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Lucy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-07',
        ]);

        State::create([
            'state_subdivision_id' => 12666,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Peter',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-09',
        ]);

        State::create([
            'state_subdivision_id' => 12667,
            'country_code_char2' => 'BB',
            'country_code_char3' => 'BRB',
            'state_subdivision_name' => 'Saint Philip',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'BB-10',
        ]);

        State::create([
            'state_subdivision_id' => 12668,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Vitsyebskaya voblasts',
            'state_subdivision_alternate_names' => 'Vicebskaja Voblastsʿ, Vicebskaya Voblastsʿ, Viciebsk, Vicjebsk, Vitebsk, Vitebskaja Oblastʿ, Vitebskaya Oblastʿ, Vitsyebsk',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-VI',
        ]);

        State::create([
            'state_subdivision_id' => 12669,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Brestskaya voblasts',
            'state_subdivision_alternate_names' => 'Bierascie, Brest-Litovsk, Brestskaja Oblastʿ, Brestskaja Voblastsʿ, Brestskaya Oblastʿ, Brestskaya Voblastsʿ, Brisk, Brześć nad Bugiem, Brześć-Litewski',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-BR',
        ]);

        State::create([
            'state_subdivision_id' => 12670,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Homyel',
            'state_subdivision_alternate_names' => 'skaya voblasts',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-HO',
        ]);

        State::create([
            'state_subdivision_id' => 12671,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Mahilyowskaya voblasts',
            'state_subdivision_alternate_names' => 'Mahiljov, Mahiljowskaja Voblastsʿ, Mahilyov, Mahilyowskaya Voblastsʿ, Mahilëv, Mahilëŭ, Mogilev, Mogiliov, Mogiljovskaja Oblastʿ, Mogilov, Mogilyovskaya Oblast, Mogilëv, Mogilʿov',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-MA',
        ]);

        State::create([
            'state_subdivision_id' => 12672,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Hrodzenskaya voblasts',
            'state_subdivision_alternate_names' => 'Gardinas, Grodnenskaja Oblastʿ, Grodnenskaya Oblastʿ, Grodno, Horadnia, Hrodno, Hrodzenskaja Voblastsʿ, Hrodzenskaya Voblastsʿ',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-HR',
        ]);

        State::create([
            'state_subdivision_id' => 12673,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Minskaya voblasts',
            'state_subdivision_alternate_names' => 'Minskaja Oblastʿ, Minskaya Oblastʿ, Minskaya Voblastsʿ',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BY-MI',
        ]);

        State::create([
            'state_subdivision_id' => 12674,
            'country_code_char2' => 'BY',
            'country_code_char3' => 'BLR',
            'state_subdivision_name' => 'Horad Minsk',
            'state_subdivision_alternate_names' => 'Gorod Minsk, Horad Minsk, Mensk',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'BY-HM',
        ]);

        State::create([
            'state_subdivision_id' => 12675,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Vlaams Brabant',
            'state_subdivision_alternate_names' => 'Brabant-Vlanderen, Vlaams-Brabant, Flämisch Brabant, Brabant-Flamand',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-VBR',
        ]);

        State::create([
            'state_subdivision_id' => 12676,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Luxembourg',
            'state_subdivision_alternate_names' => 'Luxembourg, Luxemburg',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-WLX',
        ]);

        State::create([
            'state_subdivision_id' => 12677,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Oost-Vlaanderen',
            'state_subdivision_alternate_names' => 'Oos-Vlanderen, Oost-Vlaanderen, Ost-Flandern, Flandre-Orientale',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-VOV',
        ]);

        State::create([
            'state_subdivision_id' => 12678,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Hainaut',
            'state_subdivision_alternate_names' => 'Henegouwen, Hennegau',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-WHT',
        ]);

        State::create([
            'state_subdivision_id' => 12679,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Brabant Wallon',
            'state_subdivision_alternate_names' => 'Waals-Brabant, Wallonisch Brabant, Walloon Brabant',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-WBR',
        ]);

        State::create([
            'state_subdivision_id' => 12680,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Limburg',
            'state_subdivision_alternate_names' => 'Limbourg',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-VLI',
        ]);

        State::create([
            'state_subdivision_id' => 12681,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'West-Vlaanderen',
            'state_subdivision_alternate_names' => 'Wes-Vlanderen, West-Vlaanderen, West-Flandern, Flandre-Occidentale',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-VWV',
        ]);

        State::create([
            'state_subdivision_id' => 12682,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Brussels',
            'state_subdivision_alternate_names' => 'Brussels Hoofdstedelijk Gewest, Région de Bruxelles-Capitale, Brussel, Brüssel, Bruxelles',
            'primary_level_name' => 'Capital Region',
            'state_subdivision_code' => 'BE-BRU',
        ]);

        State::create([
            'state_subdivision_id' => 12683,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Antwerpen',
            'state_subdivision_alternate_names' => 'Antwerpen, Anvers',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-VAN',
        ]);

        State::create([
            'state_subdivision_id' => 12684,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Liège',
            'state_subdivision_alternate_names' => 'Luik, Lüttich',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-WLG',
        ]);

        State::create([
            'state_subdivision_id' => 12685,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Namur',
            'state_subdivision_alternate_names' => 'Namen',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BE-WNA',
        ]);

        State::create([
            'state_subdivision_id' => 12686,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Stann Creek',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-SC',
        ]);

        State::create([
            'state_subdivision_id' => 12687,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Belize',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-BZ',
        ]);

        State::create([
            'state_subdivision_id' => 12688,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Corozal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-CZL',
        ]);

        State::create([
            'state_subdivision_id' => 12689,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Orange Walk',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-OW',
        ]);

        State::create([
            'state_subdivision_id' => 12690,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Toledo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-TOL',
        ]);

        State::create([
            'state_subdivision_id' => 12691,
            'country_code_char2' => 'BZ',
            'country_code_char3' => 'BLZ',
            'state_subdivision_name' => 'Cayo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BZ-CY',
        ]);

        State::create([
            'state_subdivision_id' => 12692,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Zou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-ZO',
        ]);

        State::create([
            'state_subdivision_id' => 12693,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Plateau',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-PL',
        ]);

        State::create([
            'state_subdivision_id' => 12694,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Littoral',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-LI',
        ]);

        State::create([
            'state_subdivision_id' => 12696,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Alibori',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-AL',
        ]);

        State::create([
            'state_subdivision_id' => 12697,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Atlantique',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-AQ',
        ]);

        State::create([
            'state_subdivision_id' => 12698,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Borgou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-BO',
        ]);

        State::create([
            'state_subdivision_id' => 12699,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Kouffo',
            'state_subdivision_alternate_names' => 'Kouffo',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-KO',
        ]);

        State::create([
            'state_subdivision_id' => 12700,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Mono',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-MO',
        ]);

        State::create([
            'state_subdivision_id' => 12701,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Ouémé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-OU',
        ]);

        State::create([
            'state_subdivision_id' => 12702,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Atakora',
            'state_subdivision_alternate_names' => 'Atakora',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-AK',
        ]);

        State::create([
            'state_subdivision_id' => 12704,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Collines',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-CO',
        ]);

        State::create([
            'state_subdivision_id' => 12705,
            'country_code_char2' => 'BJ',
            'country_code_char3' => 'BEN',
            'state_subdivision_name' => 'Donga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BJ-DO',
        ]);

        State::create([
            'state_subdivision_id' => 12717,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Trongsa',
            'state_subdivision_alternate_names' => 'Tongsa, Trongsa',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-32',
        ]);

        State::create([
            'state_subdivision_id' => 12718,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Samtse',
            'state_subdivision_alternate_names' => 'Samchi, Samtse',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-14',
        ]);

        State::create([
            'state_subdivision_id' => 12719,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Punakha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-23',
        ]);

        State::create([
            'state_subdivision_id' => 12720,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Zhemgang',
            'state_subdivision_alternate_names' => 'Shemgang, Zhemgang',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-34',
        ]);

        State::create([
            'state_subdivision_id' => 12721,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Trashigang',
            'state_subdivision_alternate_names' => 'Tashigang, Trashigang',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-41',
        ]);

        State::create([
            'state_subdivision_id' => 12722,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Monggar',
            'state_subdivision_alternate_names' => 'Monggar, Mongor',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-42',
        ]);

        State::create([
            'state_subdivision_id' => 12723,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Dagana',
            'state_subdivision_alternate_names' => 'Daga, Dagana',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-22',
        ]);

        State::create([
            'state_subdivision_id' => 12724,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Bumthang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-33',
        ]);

        State::create([
            'state_subdivision_id' => 12725,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Tsirang',
            'state_subdivision_alternate_names' => 'Chirang, Tsirang',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-21',
        ]);

        State::create([
            'state_subdivision_id' => 12726,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Sarpang',
            'state_subdivision_alternate_names' => 'Gaylegphug, Geylegphug, Sarbhang, Sarpang',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-31',
        ]);

        State::create([
            'state_subdivision_id' => 12727,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Lhuentse',
            'state_subdivision_alternate_names' => 'Lhuentse, Lhun Tshi, Lhuntshi, Lhuntsi',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-44',
        ]);

        State::create([
            'state_subdivision_id' => 12728,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Pemagatshel',
            'state_subdivision_alternate_names' => 'Pema Gatshel, Pemagatsel',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-43',
        ]);

        State::create([
            'state_subdivision_id' => 12729,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Paro',
            'state_subdivision_alternate_names' => 'Paro, Rinpung',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-11',
        ]);

        State::create([
            'state_subdivision_id' => 12730,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Samdrup Jongkha',
            'state_subdivision_alternate_names' => 'Samdruk Jongkhar, Samdrup, Samdrup Jongkha',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-45',
        ]);

        State::create([
            'state_subdivision_id' => 12731,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Thimphu',
            'state_subdivision_alternate_names' => 'Thimbu, Thimphu, Thimpu, Timbhu, Timbu, Timphu',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-15',
        ]);

        State::create([
            'state_subdivision_id' => 12732,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Wangdue Phodrang',
            'state_subdivision_alternate_names' => 'Wangdi Phodrang, Wangdiphodrang, Wangdue, Wangdue Phodrang',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-24',
        ]);

        State::create([
            'state_subdivision_id' => 12733,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Gasa',
            'state_subdivision_alternate_names' => 'Gaza',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-GA',
        ]);

        State::create([
            'state_subdivision_id' => 12734,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Trashi Yangtse',
            'state_subdivision_alternate_names' => 'Tashiyangtse',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-TY',
        ]);

        State::create([
            'state_subdivision_id' => 12735,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Chhukha',
            'state_subdivision_alternate_names' => 'Chhuka, Chuka, Chukha',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-12',
        ]);

        State::create([
            'state_subdivision_id' => 12736,
            'country_code_char2' => 'BT',
            'country_code_char3' => 'BTN',
            'state_subdivision_name' => 'Ha',
            'state_subdivision_alternate_names' => 'Ha, Haa',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BT-13',
        ]);

        State::create([
            'state_subdivision_id' => 12737,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Potosí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-P',
        ]);

        State::create([
            'state_subdivision_id' => 12738,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Oruro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-O',
        ]);

        State::create([
            'state_subdivision_id' => 12739,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Chuquisaca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-H',
        ]);

        State::create([
            'state_subdivision_id' => 12740,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'El Beni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-B',
        ]);

        State::create([
            'state_subdivision_id' => 12741,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Cochabamba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-C',
        ]);

        State::create([
            'state_subdivision_id' => 12742,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'La Paz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-L',
        ]);

        State::create([
            'state_subdivision_id' => 12743,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Pando',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-N',
        ]);

        State::create([
            'state_subdivision_id' => 12744,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Santa Cruz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-S',
        ]);

        State::create([
            'state_subdivision_id' => 12745,
            'country_code_char2' => 'BO',
            'country_code_char3' => 'BOL',
            'state_subdivision_name' => 'Tarija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'BO-T',
        ]);

        State::create([
            'state_subdivision_id' => 12746,
            'country_code_char2' => 'BA',
            'country_code_char3' => 'BIH',
            'state_subdivision_name' => 'Federacija Bosna i Hercegovina',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Entity',
            'state_subdivision_code' => 'BA-BIH',
        ]);

        State::create([
            'state_subdivision_id' => 12747,
            'country_code_char2' => 'BA',
            'country_code_char3' => 'BIH',
            'state_subdivision_name' => 'Republika Srpska',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Entity',
            'state_subdivision_code' => 'BA-SRP',
        ]);

        State::create([
            'state_subdivision_id' => 12749,
            'country_code_char2' => 'BW',
            'country_code_char3' => 'BWA',
            'state_subdivision_name' => 'North-East',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BW-NE',
        ]);

        State::create([
            'state_subdivision_id' => 12751,
            'country_code_char2' => 'BW',
            'country_code_char3' => 'BWA',
            'state_subdivision_name' => 'South-East',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BW-SE',
        ]);

        State::create([
            'state_subdivision_id' => 12759,
            'country_code_char2' => 'BW',
            'country_code_char3' => 'BWA',
            'state_subdivision_name' => 'Ghanzi',
            'state_subdivision_alternate_names' => 'Ghansi, Khanzi',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BW-GH',
        ]);

        State::create([
            'state_subdivision_id' => 12762,
            'country_code_char2' => 'BW',
            'country_code_char3' => 'BWA',
            'state_subdivision_name' => 'Kweneng',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BW-KW',
        ]);

        State::create([
            'state_subdivision_id' => 12770,
            'country_code_char2' => 'BW',
            'country_code_char3' => 'BWA',
            'state_subdivision_name' => 'Kgatleng',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BW-KL',
        ]);

        State::create([
            'state_subdivision_id' => 12771,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'São Paulo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-SP',
        ]);

        State::create([
            'state_subdivision_id' => 12772,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Rondônia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-RO',
        ]);

        State::create([
            'state_subdivision_id' => 12773,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Pernambuco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-PE',
        ]);

        State::create([
            'state_subdivision_id' => 12774,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Mato Grosso do Sul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-MS',
        ]);

        State::create([
            'state_subdivision_id' => 12775,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Amapá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-AP',
        ]);

        State::create([
            'state_subdivision_id' => 12776,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Bahia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-BA',
        ]);

        State::create([
            'state_subdivision_id' => 12777,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Distrito Federal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'federal district',
            'state_subdivision_code' => 'BR-DF',
        ]);

        State::create([
            'state_subdivision_id' => 12778,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Espírito Santo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-ES',
        ]);

        State::create([
            'state_subdivision_id' => 12779,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Maranhão',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-MA',
        ]);

        State::create([
            'state_subdivision_id' => 12780,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Mato Grosso',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-MT',
        ]);

        State::create([
            'state_subdivision_id' => 12781,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Minas Gerais',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-MG',
        ]);

        State::create([
            'state_subdivision_id' => 12782,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Paraíba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-PB',
        ]);

        State::create([
            'state_subdivision_id' => 12783,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Paraná',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-PR',
        ]);

        State::create([
            'state_subdivision_id' => 12784,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Alagoas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-AL',
        ]);

        State::create([
            'state_subdivision_id' => 12785,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Piauí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-PI',
        ]);

        State::create([
            'state_subdivision_id' => 12786,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Rio de Janeiro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-RJ',
        ]);

        State::create([
            'state_subdivision_id' => 12787,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Rio Grande do Sul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-RS',
        ]);

        State::create([
            'state_subdivision_id' => 12788,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Roraima',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-RR',
        ]);

        State::create([
            'state_subdivision_id' => 12789,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Santa Catarina',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-SC',
        ]);

        State::create([
            'state_subdivision_id' => 12790,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Sergipe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-SE',
        ]);

        State::create([
            'state_subdivision_id' => 12791,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Tocantins',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-TO',
        ]);

        State::create([
            'state_subdivision_id' => 12793,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Acre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-AC',
        ]);

        State::create([
            'state_subdivision_id' => 12794,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Amazonas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-AM',
        ]);

        State::create([
            'state_subdivision_id' => 12795,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Ceará',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-CE',
        ]);

        State::create([
            'state_subdivision_id' => 12796,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Goiás',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-GO',
        ]);

        State::create([
            'state_subdivision_id' => 12797,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Pará',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-PA',
        ]);

        State::create([
            'state_subdivision_id' => 12798,
            'country_code_char2' => 'BR',
            'country_code_char3' => 'BRA',
            'state_subdivision_name' => 'Rio Grande do Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BR-RN',
        ]);

        State::create([
            'state_subdivision_id' => 12804,
            'country_code_char2' => 'BN',
            'country_code_char3' => 'BRN',
            'state_subdivision_name' => 'Tutong',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BN-TU',
        ]);

        State::create([
            'state_subdivision_id' => 12805,
            'country_code_char2' => 'BN',
            'country_code_char3' => 'BRN',
            'state_subdivision_name' => 'Belait',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BN-BE',
        ]);

        State::create([
            'state_subdivision_id' => 12806,
            'country_code_char2' => 'BN',
            'country_code_char3' => 'BRN',
            'state_subdivision_name' => 'Temburong',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BN-TE',
        ]);

        State::create([
            'state_subdivision_id' => 12807,
            'country_code_char2' => 'BN',
            'country_code_char3' => 'BRN',
            'state_subdivision_name' => 'Brunei-Muara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'BN-BM',
        ]);

        State::create([
            'state_subdivision_id' => 12808,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Veliko Tarnovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-04',
        ]);

        State::create([
            'state_subdivision_id' => 12809,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Smolyan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-21',
        ]);

        State::create([
            'state_subdivision_id' => 12810,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Razgrad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-17',
        ]);

        State::create([
            'state_subdivision_id' => 12811,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Montana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-12',
        ]);

        State::create([
            'state_subdivision_id' => 12812,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Yambol',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-28',
        ]);

        State::create([
            'state_subdivision_id' => 12813,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Burgas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-02',
        ]);

        State::create([
            'state_subdivision_id' => 12814,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Plovdiv',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-16',
        ]);

        State::create([
            'state_subdivision_id' => 12815,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Ruse',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-18',
        ]);

        State::create([
            'state_subdivision_id' => 12816,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Sliven',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-20',
        ]);

        State::create([
            'state_subdivision_id' => 12817,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Sofia-Grad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-22',
        ]);

        State::create([
            'state_subdivision_id' => 12818,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Stara Zagora',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-24',
        ]);

        State::create([
            'state_subdivision_id' => 12819,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Šumen',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-27',
        ]);

        State::create([
            'state_subdivision_id' => 12820,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Varna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-03',
        ]);

        State::create([
            'state_subdivision_id' => 12821,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Vidin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-05',
        ]);

        State::create([
            'state_subdivision_id' => 12822,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Vratsa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-06',
        ]);

        State::create([
            'state_subdivision_id' => 12823,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Gabrovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-07',
        ]);

        State::create([
            'state_subdivision_id' => 12824,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Haskovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-26',
        ]);

        State::create([
            'state_subdivision_id' => 12825,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Kardzhali',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-09',
        ]);

        State::create([
            'state_subdivision_id' => 12826,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Lovech',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-11',
        ]);

        State::create([
            'state_subdivision_id' => 12827,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Pazardzhik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-13',
        ]);

        State::create([
            'state_subdivision_id' => 12828,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Pleven',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-15',
        ]);

        State::create([
            'state_subdivision_id' => 12829,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Blagoevgrad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-01',
        ]);

        State::create([
            'state_subdivision_id' => 12830,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Dobrich',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-08',
        ]);

        State::create([
            'state_subdivision_id' => 12831,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Kjustendil',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-10',
        ]);

        State::create([
            'state_subdivision_id' => 12832,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Pernik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-14',
        ]);

        State::create([
            'state_subdivision_id' => 12833,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Silistra',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-19',
        ]);

        State::create([
            'state_subdivision_id' => 12834,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Sofia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-23',
        ]);

        State::create([
            'state_subdivision_id' => 12835,
            'country_code_char2' => 'BG',
            'country_code_char3' => 'BGR',
            'state_subdivision_name' => 'Targovishte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'BG-25',
        ]);

        State::create([
            'state_subdivision_id' => 12837,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Loroum',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-LOR',
        ]);

        State::create([
            'state_subdivision_id' => 12838,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Nahouri',
            'state_subdivision_alternate_names' => 'Naouri',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-NAO',
        ]);

        State::create([
            'state_subdivision_id' => 12839,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Noumbiel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-NOU',
        ]);

        State::create([
            'state_subdivision_id' => 12840,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Oudalan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-OUD',
        ]);

        State::create([
            'state_subdivision_id' => 12841,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Sanguié',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SNG',
        ]);

        State::create([
            'state_subdivision_id' => 12842,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Séno',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SEN',
        ]);

        State::create([
            'state_subdivision_id' => 12843,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Sissili',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SIS',
        ]);

        State::create([
            'state_subdivision_id' => 12844,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Soum',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SOM',
        ]);

        State::create([
            'state_subdivision_id' => 12845,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Sourou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SOR',
        ]);

        State::create([
            'state_subdivision_id' => 12846,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Balé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BAL',
        ]);

        State::create([
            'state_subdivision_id' => 12847,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Banwa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BAN',
        ]);

        State::create([
            'state_subdivision_id' => 12848,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Boulgou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BLG',
        ]);

        State::create([
            'state_subdivision_id' => 12849,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Comoé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-COM',
        ]);

        State::create([
            'state_subdivision_id' => 12850,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Gourma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-GOU',
        ]);

        State::create([
            'state_subdivision_id' => 12851,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Ioba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-IOB',
        ]);

        State::create([
            'state_subdivision_id' => 12852,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kénédougou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KEN',
        ]);

        State::create([
            'state_subdivision_id' => 12853,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kossi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KOS',
        ]);

        State::create([
            'state_subdivision_id' => 12854,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kouritenga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KOT',
        ]);

        State::create([
            'state_subdivision_id' => 12855,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Bam',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BAM',
        ]);

        State::create([
            'state_subdivision_id' => 12856,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Bazèga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BAZ',
        ]);

        State::create([
            'state_subdivision_id' => 12857,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Bougouriba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BGR',
        ]);

        State::create([
            'state_subdivision_id' => 12858,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Boulkiemdé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-BLK',
        ]);

        State::create([
            'state_subdivision_id' => 12859,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Ganzourgou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-GAN',
        ]);

        State::create([
            'state_subdivision_id' => 12860,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Gnagna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-GNA',
        ]);

        State::create([
            'state_subdivision_id' => 12861,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Houet',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-HOU',
        ]);

        State::create([
            'state_subdivision_id' => 12862,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kadiogo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KAD',
        ]);

        State::create([
            'state_subdivision_id' => 12863,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Komondjari',
            'state_subdivision_alternate_names' => 'Komandjoari, Komondjari',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KMD',
        ]);

        State::create([
            'state_subdivision_id' => 12864,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kompienga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KMP',
        ]);

        State::create([
            'state_subdivision_id' => 12865,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Koulpélogo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KOP',
        ]);

        State::create([
            'state_subdivision_id' => 12866,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Kourwéogo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-KOW',
        ]);

        State::create([
            'state_subdivision_id' => 12867,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Léraba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-LER',
        ]);

        State::create([
            'state_subdivision_id' => 12868,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Mouhoun',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-MOU',
        ]);

        State::create([
            'state_subdivision_id' => 12869,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Namentenga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-NAM',
        ]);

        State::create([
            'state_subdivision_id' => 12870,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Nayala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-NAY',
        ]);

        State::create([
            'state_subdivision_id' => 12871,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Oubritenga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-OUB',
        ]);

        State::create([
            'state_subdivision_id' => 12872,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Passoré',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-PAS',
        ]);

        State::create([
            'state_subdivision_id' => 12873,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Poni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-PON',
        ]);

        State::create([
            'state_subdivision_id' => 12874,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Sanmatenga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-SMT',
        ]);

        State::create([
            'state_subdivision_id' => 12876,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Ziro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-ZIR',
        ]);

        State::create([
            'state_subdivision_id' => 12877,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Yatenga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-YAT',
        ]);

        State::create([
            'state_subdivision_id' => 12878,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Yagha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-YAG',
        ]);

        State::create([
            'state_subdivision_id' => 12879,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Tapoa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-TAP',
        ]);

        State::create([
            'state_subdivision_id' => 12880,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Tui',
            'state_subdivision_alternate_names' => 'Tui',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-TUI',
        ]);

        State::create([
            'state_subdivision_id' => 12881,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Zondoma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-ZON',
        ]);

        State::create([
            'state_subdivision_id' => 12882,
            'country_code_char2' => 'BF',
            'country_code_char3' => 'BFA',
            'state_subdivision_name' => 'Zoundwéogo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BF-ZOU',
        ]);

        State::create([
            'state_subdivision_id' => 12883,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Ruyigi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-RY',
        ]);

        State::create([
            'state_subdivision_id' => 12885,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Cankuzo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-CA',
        ]);

        State::create([
            'state_subdivision_id' => 12886,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Gitega',
            'state_subdivision_alternate_names' => 'Kitega',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-GI',
        ]);

        State::create([
            'state_subdivision_id' => 12887,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Karuzi',
            'state_subdivision_alternate_names' => 'Karusi',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-KR',
        ]);

        State::create([
            'state_subdivision_id' => 12888,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Kirundo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-KI',
        ]);

        State::create([
            'state_subdivision_id' => 12889,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Makamba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-MA',
        ]);

        State::create([
            'state_subdivision_id' => 12890,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Muyinga',
            'state_subdivision_alternate_names' => 'Muhinga',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-MY',
        ]);

        State::create([
            'state_subdivision_id' => 12891,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Ngozi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-NG',
        ]);

        State::create([
            'state_subdivision_id' => 12892,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Rutana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-RT',
        ]);

        State::create([
            'state_subdivision_id' => 12893,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Bubanza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-BB',
        ]);

        State::create([
            'state_subdivision_id' => 12894,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Kayanza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-KY',
        ]);

        State::create([
            'state_subdivision_id' => 12895,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Bururi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-BR',
        ]);

        State::create([
            'state_subdivision_id' => 12896,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Cibitoke',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-CI',
        ]);

        State::create([
            'state_subdivision_id' => 12897,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Muramvya',
            'state_subdivision_alternate_names' => 'Muramuya',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-MU',
        ]);

        State::create([
            'state_subdivision_id' => 12898,
            'country_code_char2' => 'BI',
            'country_code_char3' => 'BDI',
            'state_subdivision_name' => 'Mwaro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'BI-MW',
        ]);

        State::create([
            'state_subdivision_id' => 12900,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Pousaat [Pouthisat]',
            'state_subdivision_alternate_names' => 'Poŭthĭsăt, Pursat',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-15',
        ]);

        State::create([
            'state_subdivision_id' => 12901,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Otdar Mean Chey [Otdâr Méanchey] ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-22',
        ]);

        State::create([
            'state_subdivision_id' => 12902,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kampong Thum [Kâmpóng Thum]',
            'state_subdivision_alternate_names' => 'Kompong Thom, Kompong Thum',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-6',
        ]);

        State::create([
            'state_subdivision_id' => 12903,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kampot [Kâmpôt]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-7',
        ]);

        State::create([
            'state_subdivision_id' => 12904,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kandaal [Kândal]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-8',
        ]);

        State::create([
            'state_subdivision_id' => 12905,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kaoh Kong [Kaôh Kong]',
            'state_subdivision_alternate_names' => 'Koh Kong',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-9',
        ]);

        State::create([
            'state_subdivision_id' => 12906,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kracheh [Krâchéh]',
            'state_subdivision_alternate_names' => 'Kratié',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-10',
        ]);

        State::create([
            'state_subdivision_id' => 12907,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Krong Kaeb [Krong Kêb]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'autonomous municipality',
            'state_subdivision_code' => 'KH-23',
        ]);

        State::create([
            'state_subdivision_id' => 12908,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Krong Pailin [Krong Pailin]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'autonomous municipality',
            'state_subdivision_code' => 'KH-24',
        ]);

        State::create([
            'state_subdivision_id' => 12909,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Krong Preah Sihanouk [Krong Preah Sihanouk]',
            'state_subdivision_alternate_names' => 'Preah Seihânu, Sihanoukville',
            'primary_level_name' => 'autonomous municipality',
            'state_subdivision_code' => 'KH-18',
        ]);

        State::create([
            'state_subdivision_id' => 12910,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Mondol Kiri [Môndól Kiri]',
            'state_subdivision_alternate_names' => 'Mondolkiri',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-11',
        ]);

        State::create([
            'state_subdivision_id' => 12911,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Banteay Mean Chey [Bântéay Méanchey]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-1',
        ]);

        State::create([
            'state_subdivision_id' => 12912,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kampong Chaam [Kâmpóng Cham]',
            'state_subdivision_alternate_names' => 'Kompong Cham',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-3',
        ]);

        State::create([
            'state_subdivision_id' => 12913,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Preah Vihear [Preah Vihéar]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-13',
        ]);

        State::create([
            'state_subdivision_id' => 12914,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Prey Veaeng [Prey Vêng]',
            'state_subdivision_alternate_names' => 'Prey Vêng',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-14',
        ]);

        State::create([
            'state_subdivision_id' => 12915,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Siem Reab [Siemréab]',
            'state_subdivision_alternate_names' => 'Siem Reap, Siemréab',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-17',
        ]);

        State::create([
            'state_subdivision_id' => 12916,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Svaay Rieng [Svay Rieng]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-20',
        ]);

        State::create([
            'state_subdivision_id' => 12918,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Baat Dambang [Batdâmbâng]',
            'state_subdivision_alternate_names' => 'Batdâmbâng, Battambang',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-2',
        ]);

        State::create([
            'state_subdivision_id' => 12919,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kampong Chhnang [Kâmpóng Chhnang]',
            'state_subdivision_alternate_names' => 'Kompong Chhnang',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-4',
        ]);

        State::create([
            'state_subdivision_id' => 12920,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Kampong Spueu [Kâmpóng Spœ]',
            'state_subdivision_alternate_names' => 'Kompong Speu, Kompong Spoe',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-5',
        ]);

        State::create([
            'state_subdivision_id' => 12921,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Phnom Penh [Phnum Pénh]',
            'state_subdivision_alternate_names' => 'Phnom Penh',
            'primary_level_name' => 'autonomous municipality',
            'state_subdivision_code' => 'KH-12',
        ]);

        State::create([
            'state_subdivision_id' => 12922,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Rotanak Kiri [Rôtânôkiri]',
            'state_subdivision_alternate_names' => 'Ratanakiri, Rotanokiri, Rôtanah Kiri',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-16',
        ]);

        State::create([
            'state_subdivision_id' => 12924,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Taakaev [Takêv]',
            'state_subdivision_alternate_names' => 'Takeo, Takêv',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-21',
        ]);

        State::create([
            'state_subdivision_id' => 12925,
            'country_code_char2' => 'KH',
            'country_code_char3' => 'KHM',
            'state_subdivision_name' => 'Stueng Traeng [Stœ?ng Trêng]',
            'state_subdivision_alternate_names' => 'Stoeng Trêng, Stung Treng',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KH-19',
        ]);

        State::create([
            'state_subdivision_id' => 12926,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'South-West',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-SW',
        ]);

        State::create([
            'state_subdivision_id' => 12927,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'North-West',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-NW',
        ]);

        State::create([
            'state_subdivision_id' => 12928,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'Centre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-CE',
        ]);

        State::create([
            'state_subdivision_id' => 12929,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'Littoral',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-LT',
        ]);

        State::create([
            'state_subdivision_id' => 12930,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'West',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-OU',
        ]);

        State::create([
            'state_subdivision_id' => 12931,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'North',
            'state_subdivision_alternate_names' => 'Bénoué',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-NO',
        ]);

        State::create([
            'state_subdivision_id' => 12932,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'Adamaoua',
            'state_subdivision_alternate_names' => 'Adamawa',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-AD',
        ]);

        State::create([
            'state_subdivision_id' => 12933,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'East',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-ES',
        ]);

        State::create([
            'state_subdivision_id' => 12934,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'Far North',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-EN',
        ]);

        State::create([
            'state_subdivision_id' => 12935,
            'country_code_char2' => 'CM',
            'country_code_char3' => 'CMR',
            'state_subdivision_name' => 'South',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CM-SU',
        ]);

        State::create([
            'state_subdivision_id' => 12936,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Saskatchewan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-SK',
        ]);

        State::create([
            'state_subdivision_id' => 12937,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'New Brunswick',
            'state_subdivision_alternate_names' => 'Nouveau-Brunswick',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-NB',
        ]);

        State::create([
            'state_subdivision_id' => 12938,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'British Columbia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-BC',
        ]);

        State::create([
            'state_subdivision_id' => 12939,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Northwest Territories',
            'state_subdivision_alternate_names' => 'Territoires du Nord-Ouest',
            'primary_level_name' => 'territory',
            'state_subdivision_code' => 'CA-NT',
        ]);

        State::create([
            'state_subdivision_id' => 12940,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Alberta',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-AB',
        ]);

        State::create([
            'state_subdivision_id' => 12941,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Manitoba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-MB',
        ]);

        State::create([
            'state_subdivision_id' => 12942,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Newfoundland and Labrador',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-NL',
        ]);

        State::create([
            'state_subdivision_id' => 12943,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Nova Scotia',
            'state_subdivision_alternate_names' => 'Nouvelle-Écosse',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-NS',
        ]);

        State::create([
            'state_subdivision_id' => 12944,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Nunavut',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'territory',
            'state_subdivision_code' => 'CA-NU',
        ]);

        State::create([
            'state_subdivision_id' => 12945,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Prince Edward Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-PE',
        ]);

        State::create([
            'state_subdivision_id' => 12946,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Quebec',
            'state_subdivision_alternate_names' => 'Québec',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-QC',
        ]);

        State::create([
            'state_subdivision_id' => 12947,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Yukon Territory',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'territory',
            'state_subdivision_code' => 'CA-YT',
        ]);

        State::create([
            'state_subdivision_id' => 12948,
            'country_code_char2' => 'CA',
            'country_code_char3' => 'CAN',
            'state_subdivision_name' => 'Ontario',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CA-ON',
        ]);

        State::create([
            'state_subdivision_id' => 12950,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'São Vicente',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-SV',
        ]);

        State::create([
            'state_subdivision_id' => 12953,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'Boa Vista',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-BV',
        ]);

        State::create([
            'state_subdivision_id' => 12954,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'Maio',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-MA',
        ]);

        State::create([
            'state_subdivision_id' => 12955,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'Ribeira Grande',
            'state_subdivision_alternate_names' => 'Santiago',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-RG',
        ]);

        State::create([
            'state_subdivision_id' => 12956,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'São Lourenço dos Órgãos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-SL',
        ]);

        State::create([
            'state_subdivision_id' => 12957,
            'country_code_char2' => 'CV',
            'country_code_char3' => 'CPV',
            'state_subdivision_name' => 'Brava',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CV-BR',
        ]);

        State::create([
            'state_subdivision_id' => 12958,
            'country_code_char2' => 'KY',
            'country_code_char3' => 'CYM',
            'state_subdivision_name' => 'Little Cayman',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'Ky-05~',
        ]);

        State::create([
            'state_subdivision_id' => 12962,
            'country_code_char2' => 'KY',
            'country_code_char3' => 'CYM',
            'state_subdivision_name' => 'Cayman Brac',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'KY-02~',
        ]);

        State::create([
            'state_subdivision_id' => 12963,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Sangha',
            'state_subdivision_alternate_names' => 'Mbaeré, Sangha',
            'primary_level_name' => 'economic prefecture',
            'state_subdivision_code' => 'CF-SE',
        ]);

        State::create([
            'state_subdivision_id' => 12964,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Ombella-Mpoko',
            'state_subdivision_alternate_names' => 'Ombella-Mʿpoko, Ombelle Mpoko',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-MP',
        ]);

        State::create([
            'state_subdivision_id' => 12965,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Gribingui',
            'state_subdivision_alternate_names' => 'Gribingui, Nana-Grébisi',
            'primary_level_name' => 'economic prefecture',
            'state_subdivision_code' => 'CF-KB',
        ]);

        State::create([
            'state_subdivision_id' => 12966,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Haute-Sangha / Mambéré-Kadéï',
            'state_subdivision_alternate_names' => 'Haut-Sangha',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-HS',
        ]);

        State::create([
            'state_subdivision_id' => 12967,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Kémo-Gribingui',
            'state_subdivision_alternate_names' => 'Kémo Gribingui',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-KG',
        ]);

        State::create([
            'state_subdivision_id' => 12968,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Haute-Kotto',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-HK',
        ]);

        State::create([
            'state_subdivision_id' => 12969,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Bangui',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'commune',
            'state_subdivision_code' => 'CF-BGF',
        ]);

        State::create([
            'state_subdivision_id' => 12970,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Ouham-Pendé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-OP',
        ]);

        State::create([
            'state_subdivision_id' => 12971,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Vakaga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-VK',
        ]);

        State::create([
            'state_subdivision_id' => 12972,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Bamingui-Bangoran',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-BB',
        ]);

        State::create([
            'state_subdivision_id' => 12973,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Basse-Kotto',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-BK',
        ]);

        State::create([
            'state_subdivision_id' => 12974,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Haut-Mbomou',
            'state_subdivision_alternate_names' => 'Haut-Mʿbomou',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-HM',
        ]);

        State::create([
            'state_subdivision_id' => 12975,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Lobaye',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-LB',
        ]);

        State::create([
            'state_subdivision_id' => 12976,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Mbomou',
            'state_subdivision_alternate_names' => 'Mʿbomou',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-MB',
        ]);

        State::create([
            'state_subdivision_id' => 12977,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Nana-Mambéré',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-NM',
        ]);

        State::create([
            'state_subdivision_id' => 12978,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Ouham',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-AC',
        ]);

        State::create([
            'state_subdivision_id' => 12979,
            'country_code_char2' => 'CF',
            'country_code_char3' => 'CAF',
            'state_subdivision_name' => 'Ouaka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'prefecture',
            'state_subdivision_code' => 'CF-UK',
        ]);

        State::create([
            'state_subdivision_id' => 12980,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Tānjilī',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-TA',
        ]);

        State::create([
            'state_subdivision_id' => 12981,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Al Baṭḩah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-BA',
        ]);

        State::create([
            'state_subdivision_id' => 12983,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Qīrā',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-GR',
        ]);

        State::create([
            'state_subdivision_id' => 12984,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Kānim',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-KA',
        ]);

        State::create([
            'state_subdivision_id' => 12985,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Al Buḩayrah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-LC',
        ]);

        State::create([
            'state_subdivision_id' => 12986,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Lūqūn al Gharbī',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-LO',
        ]);

        State::create([
            'state_subdivision_id' => 12987,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Lūqūn ash Sharqī',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-LR',
        ]);

        State::create([
            'state_subdivision_id' => 12988,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Waddāy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-OD',
        ]);

        State::create([
            'state_subdivision_id' => 12990,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Shārī Bāqirmī',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-CB',
        ]);

        State::create([
            'state_subdivision_id' => 12991,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Shārī al Awsaṭ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-MC',
        ]);

        State::create([
            'state_subdivision_id' => 12992,
            'country_code_char2' => 'TD',
            'country_code_char3' => 'TCD',
            'state_subdivision_name' => 'Salāmāt',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'TD-SA',
        ]);

        State::create([
            'state_subdivision_id' => 12994,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Tarapacá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-TA',
        ]);

        State::create([
            'state_subdivision_id' => 12995,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Maule',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-ML',
        ]);

        State::create([
            'state_subdivision_id' => 12996,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Coquimbo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-CO',
        ]);

        State::create([
            'state_subdivision_id' => 12997,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Araucanía',
            'state_subdivision_alternate_names' => 'La Araucanía',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-AR',
        ]);

        State::create([
            'state_subdivision_id' => 12998,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Antofagasta',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-AN',
        ]);

        State::create([
            'state_subdivision_id' => 12999,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Atacama',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-AT',
        ]);

        State::create([
            'state_subdivision_id' => 13000,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Libertador General Bernardo Higgins',
            'state_subdivision_alternate_names' => 'Higgins',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-LI',
        ]);

        State::create([
            'state_subdivision_id' => 13001,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Magallanes',
            'state_subdivision_alternate_names' => 'Magellantes y la Antártica Chilena',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-MA',
        ]);

        State::create([
            'state_subdivision_id' => 13002,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Región Metropolitana de Santiago',
            'state_subdivision_alternate_names' => 'Metropolitana de Santiago',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-RM',
        ]);

        State::create([
            'state_subdivision_id' => 13003,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Valparaíso',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-VS',
        ]);

        State::create([
            'state_subdivision_id' => 13004,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Aisén del General Carlos Ibáñez del Campo',
            'state_subdivision_alternate_names' => 'Aisén del General Carlos Ibáñez del Campo, Aysén',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-AI',
        ]);

        State::create([
            'state_subdivision_id' => 13005,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Bío-Bío',
            'state_subdivision_alternate_names' => 'Bíobío',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-BI',
        ]);

        State::create([
            'state_subdivision_id' => 13006,
            'country_code_char2' => 'CL',
            'country_code_char3' => 'CHL',
            'state_subdivision_name' => 'Los Lagos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CL-LL',
        ]);

        State::create([
            'state_subdivision_id' => 13007,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Zhejiang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-33',
        ]);

        State::create([
            'state_subdivision_id' => 13008,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Xinjiang',
            'state_subdivision_alternate_names' => 'Uighur, Uygur',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'CN-65',
        ]);

        State::create([
            'state_subdivision_id' => 13009,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Tianjin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CN-12',
        ]);

        State::create([
            'state_subdivision_id' => 13010,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Shanxi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-14',
        ]);

        State::create([
            'state_subdivision_id' => 13011,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Shandong',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-37',
        ]);

        State::create([
            'state_subdivision_id' => 13012,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Anhui',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-34',
        ]);

        State::create([
            'state_subdivision_id' => 13013,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Beijing',
            'state_subdivision_alternate_names' => 'Beijing, Pekín',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CN-11',
        ]);

        State::create([
            'state_subdivision_id' => 13014,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Chongqing',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CN-50',
        ]);

        State::create([
            'state_subdivision_id' => 13015,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Gansu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-62',
        ]);

        State::create([
            'state_subdivision_id' => 13016,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Guangdong',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-44',
        ]);

        State::create([
            'state_subdivision_id' => 13017,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Guizhou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-52',
        ]);

        State::create([
            'state_subdivision_id' => 13018,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Hainan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-46',
        ]);

        State::create([
            'state_subdivision_id' => 13019,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Hebei',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-13',
        ]);

        State::create([
            'state_subdivision_id' => 13020,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Hubei',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-42',
        ]);

        State::create([
            'state_subdivision_id' => 13021,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Jiangxi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-36',
        ]);

        State::create([
            'state_subdivision_id' => 13022,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Jilin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-22',
        ]);

        State::create([
            'state_subdivision_id' => 13023,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Liaoning',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-21',
        ]);

        State::create([
            'state_subdivision_id' => 13024,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Nei Mongol (mn)',
            'state_subdivision_alternate_names' => 'Inner Mongolia, Nei Monggol',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'CN-15',
        ]);

        State::create([
            'state_subdivision_id' => 13025,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Ningxia',
            'state_subdivision_alternate_names' => 'Ningxia Hui',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'CN-64',
        ]);

        State::create([
            'state_subdivision_id' => 13026,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Qinghai',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-63',
        ]);

        State::create([
            'state_subdivision_id' => 13027,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Shaanxi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-61',
        ]);

        State::create([
            'state_subdivision_id' => 13028,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Heilongjiang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-23',
        ]);

        State::create([
            'state_subdivision_id' => 13029,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Henan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-41',
        ]);

        State::create([
            'state_subdivision_id' => 13030,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Hunan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-43',
        ]);

        State::create([
            'state_subdivision_id' => 13031,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Jiangsu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-32',
        ]);

        State::create([
            'state_subdivision_id' => 13032,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Xizang',
            'state_subdivision_alternate_names' => 'Tibet',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'CN-54',
        ]);

        State::create([
            'state_subdivision_id' => 13033,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Yunnan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-53',
        ]);

        State::create([
            'state_subdivision_id' => 13034,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Aomen (zh) ***',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'special administrative region',
            'state_subdivision_code' => 'CN-92',
        ]);

        State::create([
            'state_subdivision_id' => 13035,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Fujian',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-35',
        ]);

        State::create([
            'state_subdivision_id' => 13036,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Guangxi',
            'state_subdivision_alternate_names' => 'Guangxi Zhuang',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'CN-45',
        ]);

        State::create([
            'state_subdivision_id' => 13037,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Shanghai',
            'state_subdivision_alternate_names' => 'Schanghai',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'CN-31',
        ]);

        State::create([
            'state_subdivision_id' => 13038,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Sichuan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CN-51',
        ]);

        State::create([
            'state_subdivision_id' => 13039,
            'country_code_char2' => 'CN',
            'country_code_char3' => 'CHN',
            'state_subdivision_name' => 'Xianggang (zh) **',
            'state_subdivision_alternate_names' => 'Xianggang, Hongkong',
            'primary_level_name' => 'special administrative region',
            'state_subdivision_code' => 'CN-91',
        ]);

        State::create([
            'state_subdivision_id' => 13040,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Vaupés',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-VAU',
        ]);

        State::create([
            'state_subdivision_id' => 13041,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Sucre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-SUC',
        ]);

        State::create([
            'state_subdivision_id' => 13042,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Quindío',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-QUI',
        ]);

        State::create([
            'state_subdivision_id' => 13043,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'San Andrés, Providencia y Santa Catalina',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-SAP',
        ]);

        State::create([
            'state_subdivision_id' => 13044,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Nariño',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-NAR',
        ]);

        State::create([
            'state_subdivision_id' => 13045,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Amazonas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-AMA',
        ]);

        State::create([
            'state_subdivision_id' => 13046,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Arauca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-ARA',
        ]);

        State::create([
            'state_subdivision_id' => 13047,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Atlántico',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-ATL',
        ]);

        State::create([
            'state_subdivision_id' => 13048,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Bolívar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-BOL',
        ]);

        State::create([
            'state_subdivision_id' => 13049,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Caldas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CAL',
        ]);

        State::create([
            'state_subdivision_id' => 13050,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Caquetá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CAQ',
        ]);

        State::create([
            'state_subdivision_id' => 13051,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Cauca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CAU',
        ]);

        State::create([
            'state_subdivision_id' => 13052,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Chocó',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CHO',
        ]);

        State::create([
            'state_subdivision_id' => 13053,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Córdoba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-COR',
        ]);

        State::create([
            'state_subdivision_id' => 13054,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Guainía',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-GUA',
        ]);

        State::create([
            'state_subdivision_id' => 13055,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Antioquia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-ANT',
        ]);

        State::create([
            'state_subdivision_id' => 13056,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Distrito Capital de Bogotá',
            'state_subdivision_alternate_names' => 'Santafé de Bogotá Distrito Capital',
            'primary_level_name' => 'capital district',
            'state_subdivision_code' => 'CO-DC',
        ]);

        State::create([
            'state_subdivision_id' => 13057,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Boyacá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-BOY',
        ]);

        State::create([
            'state_subdivision_id' => 13058,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Casanare',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CAS',
        ]);

        State::create([
            'state_subdivision_id' => 13059,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Cesar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CES',
        ]);

        State::create([
            'state_subdivision_id' => 13060,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Cundinamarca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-CUN',
        ]);

        State::create([
            'state_subdivision_id' => 13061,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Guaviare',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-GUV',
        ]);

        State::create([
            'state_subdivision_id' => 13062,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Magdalena',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-MAG',
        ]);

        State::create([
            'state_subdivision_id' => 13063,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Huila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-HUI',
        ]);

        State::create([
            'state_subdivision_id' => 13064,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'La Guajira',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-LAG',
        ]);

        State::create([
            'state_subdivision_id' => 13065,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Meta',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-MET',
        ]);

        State::create([
            'state_subdivision_id' => 13066,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Norte de Santander',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-NSA',
        ]);

        State::create([
            'state_subdivision_id' => 13067,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Putumayo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-PUT',
        ]);

        State::create([
            'state_subdivision_id' => 13068,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Risaralda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-RIS',
        ]);

        State::create([
            'state_subdivision_id' => 13069,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Santander',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-SAN',
        ]);

        State::create([
            'state_subdivision_id' => 13070,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Tolima',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-TOL',
        ]);

        State::create([
            'state_subdivision_id' => 13071,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Valle del Cauca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-VAC',
        ]);

        State::create([
            'state_subdivision_id' => 13072,
            'country_code_char2' => 'CO',
            'country_code_char3' => 'COL',
            'state_subdivision_name' => 'Vichada',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'CO-VID',
        ]);

        State::create([
            'state_subdivision_id' => 13073,
            'country_code_char2' => 'KM',
            'country_code_char3' => 'COM',
            'state_subdivision_name' => 'Andjouân (Anjwān)',
            'state_subdivision_alternate_names' => 'Anjouan, Ndzuwani, Nzwani',
            'primary_level_name' => 'island',
            'state_subdivision_code' => 'KM-A',
        ]);

        State::create([
            'state_subdivision_id' => 13074,
            'country_code_char2' => 'KM',
            'country_code_char3' => 'COM',
            'state_subdivision_name' => 'Andjazîdja',
            'state_subdivision_alternate_names' => 'Grande Comore, Njazídja',
            'primary_level_name' => 'island',
            'state_subdivision_code' => 'KM-G',
        ]);

        State::create([
            'state_subdivision_id' => 13075,
            'country_code_char2' => 'KM',
            'country_code_char3' => 'COM',
            'state_subdivision_name' => 'Moûhîlî (Mūhīlī)',
            'state_subdivision_alternate_names' => 'Mohilla, Mohéli, Moili',
            'primary_level_name' => 'island',
            'state_subdivision_code' => 'KM-M',
        ]);

        State::create([
            'state_subdivision_id' => 13076,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Cuvette-Ouest',
            'state_subdivision_alternate_names' => 'Cuvette Ouest',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-15',
        ]);

        State::create([
            'state_subdivision_id' => 13077,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Cuvette',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-8',
        ]);

        State::create([
            'state_subdivision_id' => 13078,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Bouenza',
            'state_subdivision_alternate_names' => 'Bouénza',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-11',
        ]);

        State::create([
            'state_subdivision_id' => 13079,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Likouala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-7',
        ]);

        State::create([
            'state_subdivision_id' => 13080,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Brazzaville',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'CG-BZV',
        ]);

        State::create([
            'state_subdivision_id' => 13081,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Kouilou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-5',
        ]);

        State::create([
            'state_subdivision_id' => 13082,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Lékoumou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-2',
        ]);

        State::create([
            'state_subdivision_id' => 13083,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Niari',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-9',
        ]);

        State::create([
            'state_subdivision_id' => 13084,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Pool',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-12',
        ]);

        State::create([
            'state_subdivision_id' => 13085,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Sangha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-13',
        ]);

        State::create([
            'state_subdivision_id' => 13086,
            'country_code_char2' => 'CG',
            'country_code_char3' => 'COG',
            'state_subdivision_name' => 'Plateaux',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'CG-14',
        ]);

        State::create([
            'state_subdivision_id' => 13087,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Nord-Kivu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-NK',
        ]);

        State::create([
            'state_subdivision_id' => 13088,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Bas-Congo',
            'state_subdivision_alternate_names' => 'Bas-Zaire',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-BC',
        ]);

        State::create([
            'state_subdivision_id' => 13089,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Orientale',
            'state_subdivision_alternate_names' => 'Haut-Zaire, Orientale',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-OR',
        ]);

        State::create([
            'state_subdivision_id' => 13090,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Kasai-Oriental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-KE',
        ]);

        State::create([
            'state_subdivision_id' => 13091,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Kinshasa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'CD-KN',
        ]);

        State::create([
            'state_subdivision_id' => 13092,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Kasai-Occidental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-KW',
        ]);

        State::create([
            'state_subdivision_id' => 13093,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Équateur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-EQ',
        ]);

        State::create([
            'state_subdivision_id' => 13094,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Bandundu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-BN',
        ]);

        State::create([
            'state_subdivision_id' => 13095,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Maniema',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-MA',
        ]);

        State::create([
            'state_subdivision_id' => 13096,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Sud-Kivu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-SK',
        ]);

        State::create([
            'state_subdivision_id' => 13097,
            'country_code_char2' => 'CD',
            'country_code_char3' => 'COD',
            'state_subdivision_name' => 'Katanga',
            'state_subdivision_alternate_names' => 'Shaba',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CD-KA',
        ]);

        State::create([
            'state_subdivision_id' => 13113,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Puntarenas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-P',
        ]);

        State::create([
            'state_subdivision_id' => 13114,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Limón',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-L',
        ]);

        State::create([
            'state_subdivision_id' => 13115,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Cartago',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-C',
        ]);

        State::create([
            'state_subdivision_id' => 13116,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Guanacaste',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-G',
        ]);

        State::create([
            'state_subdivision_id' => 13117,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'San José',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-SJ',
        ]);

        State::create([
            'state_subdivision_id' => 13118,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Heredia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-H',
        ]);

        State::create([
            'state_subdivision_id' => 13119,
            'country_code_char2' => 'CR',
            'country_code_char3' => 'CRI',
            'state_subdivision_name' => 'Alajuela',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'CR-A',
        ]);

        State::create([
            'state_subdivision_id' => 13120,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Vukovarsko-srijemska županija',
            'state_subdivision_alternate_names' => 'Vukovar-Sirmium',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-16',
        ]);

        State::create([
            'state_subdivision_id' => 13121,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Istarska županija',
            'state_subdivision_alternate_names' => 'Istria',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-18',
        ]);

        State::create([
            'state_subdivision_id' => 13122,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Dubrovacko-neretvanska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-19',
        ]);

        State::create([
            'state_subdivision_id' => 13123,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Grad Zagreb',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'HR-21',
        ]);

        State::create([
            'state_subdivision_id' => 13124,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Karlovacka županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-04',
        ]);

        State::create([
            'state_subdivision_id' => 13125,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Krapinsko-zagorska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-02',
        ]);

        State::create([
            'state_subdivision_id' => 13126,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Licko-senjska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-09',
        ]);

        State::create([
            'state_subdivision_id' => 13127,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Osjecko-baranjska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-14',
        ]);

        State::create([
            'state_subdivision_id' => 13128,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Primorsko-goranska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-08',
        ]);

        State::create([
            'state_subdivision_id' => 13129,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Šibensko-kninska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-15',
        ]);

        State::create([
            'state_subdivision_id' => 13130,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Brodsko-posavska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-12',
        ]);

        State::create([
            'state_subdivision_id' => 13131,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Varaždinska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-05',
        ]);

        State::create([
            'state_subdivision_id' => 13132,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Viroviticko-podravska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-10',
        ]);

        State::create([
            'state_subdivision_id' => 13133,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Zadarska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-13',
        ]);

        State::create([
            'state_subdivision_id' => 13134,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Zagrebacka županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-01',
        ]);

        State::create([
            'state_subdivision_id' => 13135,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Bjelovarsko-bilogorska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-07',
        ]);

        State::create([
            'state_subdivision_id' => 13136,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Koprivnicko-križevacka županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-06',
        ]);

        State::create([
            'state_subdivision_id' => 13137,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Splitsko-dalmatinska županija',
            'state_subdivision_alternate_names' => 'Split-Dalmatia',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-17',
        ]);

        State::create([
            'state_subdivision_id' => 13138,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Sisacko-moslavacka županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-03',
        ]);

        State::create([
            'state_subdivision_id' => 13139,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Požeško-slavonska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-11',
        ]);

        State::create([
            'state_subdivision_id' => 13140,
            'country_code_char2' => 'HR',
            'country_code_char3' => 'HRV',
            'state_subdivision_name' => 'Medimurska županija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'HR-20',
        ]);

        State::create([
            'state_subdivision_id' => 13142,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Ciudad de La Habana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-03',
        ]);

        State::create([
            'state_subdivision_id' => 13143,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Isla de la Juventud',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'special municipality',
            'state_subdivision_code' => 'CU-99',
        ]);

        State::create([
            'state_subdivision_id' => 13144,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Las Tunas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-10',
        ]);

        State::create([
            'state_subdivision_id' => 13145,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Matanzas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-04',
        ]);

        State::create([
            'state_subdivision_id' => 13146,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Sancti Spíritus',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-07',
        ]);

        State::create([
            'state_subdivision_id' => 13147,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Villa Clara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-05',
        ]);

        State::create([
            'state_subdivision_id' => 13152,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Camagüey',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-09',
        ]);

        State::create([
            'state_subdivision_id' => 13153,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Ciego de Ávila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-08',
        ]);

        State::create([
            'state_subdivision_id' => 13154,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Cienfuegos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-06',
        ]);

        State::create([
            'state_subdivision_id' => 13155,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Granma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-12',
        ]);

        State::create([
            'state_subdivision_id' => 13156,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Holguín',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-11',
        ]);

        State::create([
            'state_subdivision_id' => 13157,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Guantánamo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-14',
        ]);

        State::create([
            'state_subdivision_id' => 13158,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Santiago de Cuba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-13',
        ]);

        State::create([
            'state_subdivision_id' => 13159,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'La Habana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-02',
        ]);

        State::create([
            'state_subdivision_id' => 13160,
            'country_code_char2' => 'CU',
            'country_code_char3' => 'CUB',
            'state_subdivision_name' => 'Pinar del Río',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'CU-01',
        ]);

        State::create([
            'state_subdivision_id' => 13183,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Karlovarský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-KA',
        ]);

        State::create([
            'state_subdivision_id' => 13184,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Liberecký kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-LI',
        ]);

        State::create([
            'state_subdivision_id' => 13185,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Olomoucký kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-OL',
        ]);

        State::create([
            'state_subdivision_id' => 13186,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Plzenský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-PL',
        ]);

        State::create([
            'state_subdivision_id' => 13187,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Stredoceský kraj',
            'state_subdivision_alternate_names' => 'Central Bohemia, Mittelböhmen, Stredocesky',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-ST',
        ]);

        State::create([
            'state_subdivision_id' => 13188,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Vysocina',
            'state_subdivision_alternate_names' => 'Jihlavský',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-VY',
        ]);

        State::create([
            'state_subdivision_id' => 13219,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Jihoceský kraj',
            'state_subdivision_alternate_names' => 'Budějovický, Českobudějovický',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-JC',
        ]);

        State::create([
            'state_subdivision_id' => 13220,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Jihomoravský kraj ',
            'state_subdivision_alternate_names' => 'Brnenský',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-JM',
        ]);

        State::create([
            'state_subdivision_id' => 13221,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Královéhradecký kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-KR',
        ]);

        State::create([
            'state_subdivision_id' => 13222,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Moravskoslezský kraj',
            'state_subdivision_alternate_names' => 'Ostravský',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-MO',
        ]);

        State::create([
            'state_subdivision_id' => 13223,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Pardubický kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-PA',
        ]);

        State::create([
            'state_subdivision_id' => 13224,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Praha, hlavní mesto',
            'state_subdivision_alternate_names' => 'Hlavi město Praha, Praha, Prag, Prague',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-PR',
        ]);

        State::create([
            'state_subdivision_id' => 13225,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Ústecký kraj ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-US',
        ]);

        State::create([
            'state_subdivision_id' => 13226,
            'country_code_char2' => 'CZ',
            'country_code_char3' => 'CZE',
            'state_subdivision_name' => 'Zlínský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'CZ-ZL',
        ]);

        State::create([
            'state_subdivision_id' => 13256,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Bornholm',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-040',
        ]);

        State::create([
            'state_subdivision_id' => 13257,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Frederiksborg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-020',
        ]);

        State::create([
            'state_subdivision_id' => 13258,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'København City',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'DK-101',
        ]);

        State::create([
            'state_subdivision_id' => 13259,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Ringkøbing',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-065',
        ]);

        State::create([
            'state_subdivision_id' => 13260,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Sønderjylland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-050',
        ]);

        State::create([
            'state_subdivision_id' => 13261,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Storstrøm',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-035',
        ]);

        State::create([
            'state_subdivision_id' => 13262,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Vejle',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-060',
        ]);

        State::create([
            'state_subdivision_id' => 13263,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Vestsjælland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-030',
        ]);

        State::create([
            'state_subdivision_id' => 13264,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Viborg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-076',
        ]);

        State::create([
            'state_subdivision_id' => 13265,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Fyn',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-042',
        ]);

        State::create([
            'state_subdivision_id' => 13266,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'København',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-015',
        ]);

        State::create([
            'state_subdivision_id' => 13267,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Nordjylland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-080',
        ]);

        State::create([
            'state_subdivision_id' => 13268,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Ribe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-055',
        ]);

        State::create([
            'state_subdivision_id' => 13269,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Roskilde',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-025',
        ]);

        State::create([
            'state_subdivision_id' => 13274,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Århus',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'county',
            'state_subdivision_code' => 'DK-070',
        ]);

        State::create([
            'state_subdivision_id' => 13275,
            'country_code_char2' => 'DK',
            'country_code_char3' => 'DNK',
            'state_subdivision_name' => 'Frederiksberg City',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'DK-147',
        ]);

        State::create([
            'state_subdivision_id' => 13280,
            'country_code_char2' => 'DJ',
            'country_code_char3' => 'DJI',
            'state_subdivision_name' => 'Djibouti',
            'state_subdivision_alternate_names' => 'Djibouti',
            'primary_level_name' => 'City',
            'state_subdivision_code' => 'DJ-DJ',
        ]);

        State::create([
            'state_subdivision_id' => 13281,
            'country_code_char2' => 'DJ',
            'country_code_char3' => 'DJI',
            'state_subdivision_name' => 'Obock',
            'state_subdivision_alternate_names' => 'Obock, Obok',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'DJ-OB',
        ]);

        State::create([
            'state_subdivision_id' => 13282,
            'country_code_char2' => 'DJ',
            'country_code_char3' => 'DJI',
            'state_subdivision_name' => 'Dikhil',
            'state_subdivision_alternate_names' => 'Dikhil, Dikkil',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'DJ-DI',
        ]);

        State::create([
            'state_subdivision_id' => 13283,
            'country_code_char2' => 'DJ',
            'country_code_char3' => 'DJI',
            'state_subdivision_name' => 'Ali Sabieh',
            'state_subdivision_alternate_names' => 'Ali Sabieh, Ali Sabih',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'DJ-AS',
        ]);

        State::create([
            'state_subdivision_id' => 13284,
            'country_code_char2' => 'DJ',
            'country_code_char3' => 'DJI',
            'state_subdivision_name' => 'Tadjourah',
            'state_subdivision_alternate_names' => 'Tadjoura, Tadjourah, Tajura',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'DJ-TA',
        ]);

        State::create([
            'state_subdivision_id' => 13285,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Patrick',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-09',
        ]);

        State::create([
            'state_subdivision_id' => 13286,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Andrew',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-02',
        ]);

        State::create([
            'state_subdivision_id' => 13287,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Paul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-10',
        ]);

        State::create([
            'state_subdivision_id' => 13288,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Peter',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-11',
        ]);

        State::create([
            'state_subdivision_id' => 13289,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint David',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-03',
        ]);

        State::create([
            'state_subdivision_id' => 13290,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Mark',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-08',
        ]);

        State::create([
            'state_subdivision_id' => 13291,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Joseph',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-06',
        ]);

        State::create([
            'state_subdivision_id' => 13292,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint John',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-05',
        ]);

        State::create([
            'state_subdivision_id' => 13293,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint George',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-04',
        ]);

        State::create([
            'state_subdivision_id' => 13294,
            'country_code_char2' => 'DM',
            'country_code_char3' => 'DMA',
            'state_subdivision_name' => 'Saint Luke',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'DM-07',
        ]);

        State::create([
            'state_subdivision_id' => 13477,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Barahona',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-04',
        ]);

        State::create([
            'state_subdivision_id' => 13487,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'La Romana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-12',
        ]);

        State::create([
            'state_subdivision_id' => 13489,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Bahoruco',
            'state_subdivision_alternate_names' => 'Bahoruco, Baoruco',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-03',
        ]);

        State::create([
            'state_subdivision_id' => 13490,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Dajabón',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-05',
        ]);

        State::create([
            'state_subdivision_id' => 13491,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Duarte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-06',
        ]);

        State::create([
            'state_subdivision_id' => 13492,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'El Seybo [El Seibo]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-08',
        ]);

        State::create([
            'state_subdivision_id' => 13493,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Espaillat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-09',
        ]);

        State::create([
            'state_subdivision_id' => 13494,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Independencia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-10',
        ]);

        State::create([
            'state_subdivision_id' => 13496,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'María Trinidad Sánchez',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-14',
        ]);

        State::create([
            'state_subdivision_id' => 13497,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Monte Cristi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-15',
        ]);

        State::create([
            'state_subdivision_id' => 13498,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Pedernales',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-16',
        ]);

        State::create([
            'state_subdivision_id' => 13499,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Peravia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-17',
        ]);

        State::create([
            'state_subdivision_id' => 13500,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Salcedo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-19',
        ]);

        State::create([
            'state_subdivision_id' => 13501,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Sánchez Ramírez',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-24',
        ]);

        State::create([
            'state_subdivision_id' => 13502,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'San Jose de Ocoa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-31',
        ]);

        State::create([
            'state_subdivision_id' => 13503,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'San Pedro de Macorís',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-23',
        ]);

        State::create([
            'state_subdivision_id' => 13504,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Santiago Rodríguez',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-26',
        ]);

        State::create([
            'state_subdivision_id' => 13505,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Valverde',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-27',
        ]);

        State::create([
            'state_subdivision_id' => 13506,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Azua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-02',
        ]);

        State::create([
            'state_subdivision_id' => 13509,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'La Estrelleta [Elías Piña]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-07',
        ]);

        State::create([
            'state_subdivision_id' => 13510,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Hato Mayor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-30',
        ]);

        State::create([
            'state_subdivision_id' => 13511,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'La Altagracia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-11',
        ]);

        State::create([
            'state_subdivision_id' => 13512,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'La Vega',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-13',
        ]);

        State::create([
            'state_subdivision_id' => 13513,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Monseñor Nouel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-28',
        ]);

        State::create([
            'state_subdivision_id' => 13514,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Monte Plata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-29',
        ]);

        State::create([
            'state_subdivision_id' => 13515,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Puerto Plata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-18',
        ]);

        State::create([
            'state_subdivision_id' => 13516,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Samaná',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-20',
        ]);

        State::create([
            'state_subdivision_id' => 13517,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'San Cristóbal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-21',
        ]);

        State::create([
            'state_subdivision_id' => 13518,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'San Juan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-22',
        ]);

        State::create([
            'state_subdivision_id' => 13519,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Santiago',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-25',
        ]);

        State::create([
            'state_subdivision_id' => 13520,
            'country_code_char2' => 'DO',
            'country_code_char3' => 'DOM',
            'state_subdivision_name' => 'Distrito Nacional (Santo Domingo)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'DO-01',
        ]);

        State::create([
            'state_subdivision_id' => 13552,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Manufahi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-MF',
        ]);

        State::create([
            'state_subdivision_id' => 13553,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Ainaro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-AN',
        ]);

        State::create([
            'state_subdivision_id' => 13554,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Aileu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-AL',
        ]);

        State::create([
            'state_subdivision_id' => 13555,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Oecussi',
            'state_subdivision_alternate_names' => 'Ambeno, Ambino, Oecusse',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-OE',
        ]);

        State::create([
            'state_subdivision_id' => 13556,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Bobonaro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-BO',
        ]);

        State::create([
            'state_subdivision_id' => 13557,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Cova Lima',
            'state_subdivision_alternate_names' => 'Kova-Lima',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-CO',
        ]);

        State::create([
            'state_subdivision_id' => 13558,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Ermera',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-ER',
        ]);

        State::create([
            'state_subdivision_id' => 13559,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Liquiça',
            'state_subdivision_alternate_names' => 'Likisia',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-LI',
        ]);

        State::create([
            'state_subdivision_id' => 13560,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Manatuto',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-MT',
        ]);

        State::create([
            'state_subdivision_id' => 13561,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Viqueque',
            'state_subdivision_alternate_names' => 'Vikeke',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-VI',
        ]);

        State::create([
            'state_subdivision_id' => 13562,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Baucau',
            'state_subdivision_alternate_names' => 'Baukau',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-BA',
        ]);

        State::create([
            'state_subdivision_id' => 13563,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Lautem',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-LA',
        ]);

        State::create([
            'state_subdivision_id' => 13564,
            'country_code_char2' => 'TL',
            'country_code_char3' => 'TLS',
            'state_subdivision_name' => 'Dili',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'TL-DI',
        ]);

        State::create([
            'state_subdivision_id' => 13566,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Zamora-Chinchipe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-Z',
        ]);

        State::create([
            'state_subdivision_id' => 13567,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Sucumbíos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-U',
        ]);

        State::create([
            'state_subdivision_id' => 13568,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Pastaza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-Y',
        ]);

        State::create([
            'state_subdivision_id' => 13569,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Napo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-N',
        ]);

        State::create([
            'state_subdivision_id' => 13570,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Azuay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-A',
        ]);

        State::create([
            'state_subdivision_id' => 13571,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Bolívar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-B',
        ]);

        State::create([
            'state_subdivision_id' => 13572,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Carchi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-C',
        ]);

        State::create([
            'state_subdivision_id' => 13573,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Cotopaxi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-X',
        ]);

        State::create([
            'state_subdivision_id' => 13574,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'El Oro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-O',
        ]);

        State::create([
            'state_subdivision_id' => 13575,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Galápagos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-W',
        ]);

        State::create([
            'state_subdivision_id' => 13576,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Guayas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-G',
        ]);

        State::create([
            'state_subdivision_id' => 13577,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Loja',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-L',
        ]);

        State::create([
            'state_subdivision_id' => 13578,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Cañar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-F',
        ]);

        State::create([
            'state_subdivision_id' => 13579,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Chimborazo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-H',
        ]);

        State::create([
            'state_subdivision_id' => 13580,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Esmeraldas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-E',
        ]);

        State::create([
            'state_subdivision_id' => 13581,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Imbabura',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-I',
        ]);

        State::create([
            'state_subdivision_id' => 13582,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Los Ríos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-R',
        ]);

        State::create([
            'state_subdivision_id' => 13583,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Manabí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-M',
        ]);

        State::create([
            'state_subdivision_id' => 13584,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Morona-Santiago',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-S',
        ]);

        State::create([
            'state_subdivision_id' => 13585,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Orellana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-D',
        ]);

        State::create([
            'state_subdivision_id' => 13586,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Pichincha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-P',
        ]);

        State::create([
            'state_subdivision_id' => 13587,
            'country_code_char2' => 'EC',
            'country_code_char3' => 'ECU',
            'state_subdivision_name' => 'Tungurahua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'EC-T',
        ]);

        State::create([
            'state_subdivision_id' => 13588,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Wadi al Jadid',
            'state_subdivision_alternate_names' => 'El Wadi el Jadid, El Wadi el Jedid',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-WAD',
        ]);

        State::create([
            'state_subdivision_id' => 13589,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'al-Uqsur',
            'state_subdivision_alternate_names' => 'al-Uqsur, al-Uqşur, Luxor, Louxor',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-LX',
        ]);

        State::create([
            'state_subdivision_id' => 13590,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Shamal Sina',
            'state_subdivision_alternate_names' => 'Shamal Sina, Sina aš-Šamālīyah, Sinai ash-Shamaliyah, Šamāl Sīna',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-SIN',
        ]);

        State::create([
            'state_subdivision_id' => 13591,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Minya',
            'state_subdivision_alternate_names' => 'El Minya, Minia, al-Minya, al-Minyā',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-MN',
        ]);

        State::create([
            'state_subdivision_id' => 13592,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Kafr ash Shaykh',
            'state_subdivision_alternate_names' => 'Kafr-ash-Shaykh, Kafr-aš-Šayẖ',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-KFS',
        ]);

        State::create([
            'state_subdivision_id' => 13593,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Būr Sa`īd',
            'state_subdivision_alternate_names' => 'Bur Said, Būr Saʿīd',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-PTS',
        ]);

        State::create([
            'state_subdivision_id' => 13594,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Dumyat',
            'state_subdivision_alternate_names' => 'Damiat, Dumyat, Dumyāţ, Damiette',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-DT',
        ]);

        State::create([
            'state_subdivision_id' => 13595,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Iskandariyah',
            'state_subdivision_alternate_names' => 'El Iskandariya, al-Iskandariyah, al-Iskandarīyah, Alexandria, Alexandrie, Alexandria',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-ALX',
        ]);

        State::create([
            'state_subdivision_id' => 13596,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Jizah',
            'state_subdivision_alternate_names' => 'El Giza, Gise, Giza, Gizah, Jiza, Jizah, al-Jīzah, Giseh, Gîza',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-GZ',
        ]);

        State::create([
            'state_subdivision_id' => 13597,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Matrūh',
            'state_subdivision_alternate_names' => 'Matrah, Matrūh',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-MT',
        ]);

        State::create([
            'state_subdivision_id' => 13598,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Qahirah',
            'state_subdivision_alternate_names' => 'El Qahira, Le Caire-sur-Mer, al-Qāhirah, Kairo, Caire, Cairo',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-C',
        ]);

        State::create([
            'state_subdivision_id' => 13599,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Qina',
            'state_subdivision_alternate_names' => 'Qina, Qinā',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-KN',
        ]);

        State::create([
            'state_subdivision_id' => 13600,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Ash Sharqiyah',
            'state_subdivision_alternate_names' => 'ash-Sharqiyah, aš-Šarqīyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-SHR',
        ]);

        State::create([
            'state_subdivision_id' => 13601,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'As Suways',
            'state_subdivision_alternate_names' => 'El Suweiz, as-Suways',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-SUZ',
        ]);

        State::create([
            'state_subdivision_id' => 13602,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Aswan',
            'state_subdivision_alternate_names' => 'Aswān, Assuan, Assouan',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-ASN',
        ]);

        State::create([
            'state_subdivision_id' => 13603,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Bani Suwayf',
            'state_subdivision_alternate_names' => 'Bani Suwayf, Banī Suwayf',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-BNS',
        ]);

        State::create([
            'state_subdivision_id' => 13604,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Asyut',
            'state_subdivision_alternate_names' => 'Asiut, Assyût, Siut, Asyūţ, Assiut, Assiout',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-AST',
        ]);

        State::create([
            'state_subdivision_id' => 13605,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Bahr al Ahmar',
            'state_subdivision_alternate_names' => 'El Bahr el Ahmar',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-BA',
        ]);

        State::create([
            'state_subdivision_id' => 13606,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Buhayrah',
            'state_subdivision_alternate_names' => 'El Buhayra, al-Buh̨ayrah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-BH',
        ]);

        State::create([
            'state_subdivision_id' => 13607,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Ad Daqahliyah',
            'state_subdivision_alternate_names' => 'Dakahlia, El Daqahliya, ad-Daqahliyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-DK',
        ]);

        State::create([
            'state_subdivision_id' => 13608,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Fayyum',
            'state_subdivision_alternate_names' => 'El Faiyūm, al-Fayyum, al-Fayyūm',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-FYM',
        ]);

        State::create([
            'state_subdivision_id' => 13609,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Gharbiyah',
            'state_subdivision_alternate_names' => 'El Gharbiya, Gharbiya, al-Garbiyah, al-Ġarbīyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-GH',
        ]);

        State::create([
            'state_subdivision_id' => 13610,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Ismā`īlīyah',
            'state_subdivision_alternate_names' => 'El Ismailia, Ismaʿiliya, al-Ismailiyah, al-Ismāīlīyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-IS',
        ]);

        State::create([
            'state_subdivision_id' => 13611,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Janub Sina',
            'state_subdivision_alternate_names' => 'Sina al-Janūbīyah, Sinai al-Janubiyah, South Sinai',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-JS',
        ]);

        State::create([
            'state_subdivision_id' => 13612,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Minufiyah',
            'state_subdivision_alternate_names' => 'El Minufiya, Menufiya, al-Minufiyah, al-Minūfīyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-MNF',
        ]);

        State::create([
            'state_subdivision_id' => 13613,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Al Qalyubiyah',
            'state_subdivision_alternate_names' => 'El Qalubiya, al-Qalyubiyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-KB',
        ]);

        State::create([
            'state_subdivision_id' => 13614,
            'country_code_char2' => 'EG',
            'country_code_char3' => 'EGY',
            'state_subdivision_name' => 'Suhaj',
            'state_subdivision_alternate_names' => 'Sawhaj, Suhag, Suhaj, Sūhaj, Sawhāj',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'EG-SHG',
        ]);

        State::create([
            'state_subdivision_id' => 13615,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Usulután',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-US',
        ]);

        State::create([
            'state_subdivision_id' => 13616,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'San Vicente',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-SV',
        ]);

        State::create([
            'state_subdivision_id' => 13617,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Morazán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-MO',
        ]);

        State::create([
            'state_subdivision_id' => 13618,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Chalatenango',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-CH',
        ]);

        State::create([
            'state_subdivision_id' => 13619,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Ahuachapán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-AH',
        ]);

        State::create([
            'state_subdivision_id' => 13620,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Cabañas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-CA',
        ]);

        State::create([
            'state_subdivision_id' => 13621,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Cuscatlán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-CU',
        ]);

        State::create([
            'state_subdivision_id' => 13622,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'La Paz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-PA',
        ]);

        State::create([
            'state_subdivision_id' => 13623,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'La Unión',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-UN',
        ]);

        State::create([
            'state_subdivision_id' => 13624,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'San Miguel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-SM',
        ]);

        State::create([
            'state_subdivision_id' => 13625,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Santa Ana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-SA',
        ]);

        State::create([
            'state_subdivision_id' => 13626,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'Sonsonate',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-SO',
        ]);

        State::create([
            'state_subdivision_id' => 13627,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'La Libertad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-LI',
        ]);

        State::create([
            'state_subdivision_id' => 13628,
            'country_code_char2' => 'SV',
            'country_code_char3' => 'SLV',
            'state_subdivision_name' => 'San Salvador',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Department',
            'state_subdivision_code' => 'SV-SS',
        ]);

        State::create([
            'state_subdivision_id' => 13629,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Wele-Nzás',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-WN',
        ]);

        State::create([
            'state_subdivision_id' => 13630,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Kie-Ntem',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-KN',
        ]);

        State::create([
            'state_subdivision_id' => 13631,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Litoral',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-LI',
        ]);

        State::create([
            'state_subdivision_id' => 13632,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Bioko Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-BN',
        ]);

        State::create([
            'state_subdivision_id' => 13633,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Centro Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-CS',
        ]);

        State::create([
            'state_subdivision_id' => 13634,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Bioko Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-BS',
        ]);

        State::create([
            'state_subdivision_id' => 13635,
            'country_code_char2' => 'GQ',
            'country_code_char3' => 'GNQ',
            'state_subdivision_name' => 'Annobón',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'GQ-AN',
        ]);

        State::create([
            'state_subdivision_id' => 13636,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Semenawi Keyih Bahri [Semien-Keih-Bahri]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-SK',
        ]);

        State::create([
            'state_subdivision_id' => 13637,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Gash-Barka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-GB',
        ]);

        State::create([
            'state_subdivision_id' => 13638,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Anseba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-AN',
        ]);

        State::create([
            'state_subdivision_id' => 13639,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Debubawi Keyih Bahri [Debub-Keih-Bahri]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-DK',
        ]);

        State::create([
            'state_subdivision_id' => 13640,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Maakel [Maekel]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-MA',
        ]);

        State::create([
            'state_subdivision_id' => 13641,
            'country_code_char2' => 'ER',
            'country_code_char3' => 'ERI',
            'state_subdivision_name' => 'Debub',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'ER-DU',
        ]);

        State::create([
            'state_subdivision_id' => 13642,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Võrumaa',
            'state_subdivision_alternate_names' => 'Vorumaa',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-86',
        ]);

        State::create([
            'state_subdivision_id' => 13643,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Raplamaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-70',
        ]);

        State::create([
            'state_subdivision_id' => 13644,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Viljandimaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-84',
        ]);

        State::create([
            'state_subdivision_id' => 13645,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Saaremaa',
            'state_subdivision_alternate_names' => 'Saare, Ösel',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-74',
        ]);

        State::create([
            'state_subdivision_id' => 13646,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Ida-Virumaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-44',
        ]);

        State::create([
            'state_subdivision_id' => 13647,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Jõgevamaa',
            'state_subdivision_alternate_names' => 'Jogevamaa',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-49',
        ]);

        State::create([
            'state_subdivision_id' => 13648,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Läänemaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-57',
        ]);

        State::create([
            'state_subdivision_id' => 13649,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Pärnumaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-67',
        ]);

        State::create([
            'state_subdivision_id' => 13650,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Põlvamaa',
            'state_subdivision_alternate_names' => 'Polvamaa',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-65',
        ]);

        State::create([
            'state_subdivision_id' => 13651,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Valgamaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-82',
        ]);

        State::create([
            'state_subdivision_id' => 13652,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Järvamaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-51',
        ]);

        State::create([
            'state_subdivision_id' => 13653,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Harjumaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-37',
        ]);

        State::create([
            'state_subdivision_id' => 13654,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Hiiumaa',
            'state_subdivision_alternate_names' => 'Dagden, Dagö',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-39',
        ]);

        State::create([
            'state_subdivision_id' => 13655,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Lääne-Virumaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-59',
        ]);

        State::create([
            'state_subdivision_id' => 13656,
            'country_code_char2' => 'EE',
            'country_code_char3' => 'EST',
            'state_subdivision_name' => 'Tartumaa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'EE-78',
        ]);

        State::create([
            'state_subdivision_id' => 13657,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'YeDebub Biheroch Bihereseboch na Hizboch',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-SN',
        ]);

        State::create([
            'state_subdivision_id' => 13658,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Amara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-AM',
        ]);

        State::create([
            'state_subdivision_id' => 13659,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Tigray',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-TI',
        ]);

        State::create([
            'state_subdivision_id' => 13661,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Adis Abeba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'administration',
            'state_subdivision_code' => 'ET-AA',
        ]);

        State::create([
            'state_subdivision_id' => 13662,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Afar',
            'state_subdivision_alternate_names' => 'Affar',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-AF',
        ]);

        State::create([
            'state_subdivision_id' => 13663,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Binshangul Gumuz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-BE',
        ]);

        State::create([
            'state_subdivision_id' => 13664,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Sumale',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-SO',
        ]);

        State::create([
            'state_subdivision_id' => 13665,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Hareri Hizb',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-HA',
        ]);

        State::create([
            'state_subdivision_id' => 13666,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Gambela Hizboch',
            'state_subdivision_alternate_names' => 'Gambela',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-GA',
        ]);

        State::create([
            'state_subdivision_id' => 13667,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Dire Dawa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'administration',
            'state_subdivision_code' => 'ET-DD',
        ]);

        State::create([
            'state_subdivision_id' => 13668,
            'country_code_char2' => 'ET',
            'country_code_char3' => 'ETH',
            'state_subdivision_name' => 'Oromiya',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'ET-OR',
        ]);

        State::create([
            'state_subdivision_id' => 13681,
            'country_code_char2' => 'FJ',
            'country_code_char3' => 'FJI',
            'state_subdivision_name' => 'Western',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'FJ-W',
        ]);

        State::create([
            'state_subdivision_id' => 13682,
            'country_code_char2' => 'FJ',
            'country_code_char3' => 'FJI',
            'state_subdivision_name' => 'Eastern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'FJ-E',
        ]);

        State::create([
            'state_subdivision_id' => 13683,
            'country_code_char2' => 'FJ',
            'country_code_char3' => 'FJI',
            'state_subdivision_name' => 'Central',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'FJ-C',
        ]);

        State::create([
            'state_subdivision_id' => 13684,
            'country_code_char2' => 'FJ',
            'country_code_char3' => 'FJI',
            'state_subdivision_name' => 'Northern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'FJ-N',
        ]);

        State::create([
            'state_subdivision_id' => 13690,
            'country_code_char2' => 'FI',
            'country_code_char3' => 'FIN',
            'state_subdivision_name' => 'Lapin lääni',
            'state_subdivision_alternate_names' => 'Lappland, Lappi, Lappland, Laponie',
            'primary_level_name' => '',
            'state_subdivision_code' => 'FI-LL',
        ]);

        State::create([
            'state_subdivision_id' => 13703,
            'country_code_char2' => 'FI',
            'country_code_char3' => 'FIN',
            'state_subdivision_name' => 'Ahvenanmaan lääni',
            'state_subdivision_alternate_names' => 'Åland',
            'primary_level_name' => '',
            'state_subdivision_code' => 'FI-AL',
        ]);

        State::create([
            'state_subdivision_id' => 13706,
            'country_code_char2' => 'FI',
            'country_code_char3' => 'FIN',
            'state_subdivision_name' => 'Itä-Suomen lääni',
            'state_subdivision_alternate_names' => 'Östra Nyland, Itä-Uusimaa, Uusima de lʿEst',
            'primary_level_name' => '',
            'state_subdivision_code' => 'FI-IS',
        ]);

        State::create([
            'state_subdivision_id' => 13707,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Yvelines',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-78',
        ]);

        State::create([
            'state_subdivision_id' => 13708,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Vosges',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-88',
        ]);

        State::create([
            'state_subdivision_id' => 13709,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Val-d Oise',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-95',
        ]);

        State::create([
            'state_subdivision_id' => 13710,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Seine-Maritime',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-76',
        ]);

        State::create([
            'state_subdivision_id' => 13711,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Somme',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-80',
        ]);

        State::create([
            'state_subdivision_id' => 13712,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Tarn-et-Garonne',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-82',
        ]);

        State::create([
            'state_subdivision_id' => 13713,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Vaucluse',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-84',
        ]);

        State::create([
            'state_subdivision_id' => 13714,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Savoie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-73',
        ]);

        State::create([
            'state_subdivision_id' => 13717,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Allier',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-03',
        ]);

        State::create([
            'state_subdivision_id' => 13718,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Alpes-Maritimes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-06',
        ]);

        State::create([
            'state_subdivision_id' => 13719,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Ardennes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-08',
        ]);

        State::create([
            'state_subdivision_id' => 13720,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Aude',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-11',
        ]);

        State::create([
            'state_subdivision_id' => 13721,
            'country_code_char2' => 'FR',
            'country_code_char3' => 'FRA',
            'state_subdivision_name' => 'Bas-Rhin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'metropolitan department',
            'state_subdivision_code' => 'FR-67',
        ]);
    }
}
