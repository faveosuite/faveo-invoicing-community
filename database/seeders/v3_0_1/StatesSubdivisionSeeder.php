<?php

namespace Database\Seeders\v3_0_1;


use App\Model\Common\State;
use Illuminate\Database\Seeder;

class StatesSubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        State::create([
            'state_subdivision_id' => 48251,
            'country_code_char2' => 'AL',
            'country_code_char3' => 'ALB',
            'state_subdivision_name' => 'Dibres',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'AL-011',
        ]);
         State::create([
            'state_subdivision_id' => 48252,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'sitrah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'island',
            'state_subdivision_code' => 'BH-14',
        ]);
          State::create([
            'state_subdivision_id' => 48253,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Juzur Hawar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'island',
            'state_subdivision_code' => 'BH-14',
        ]);
          State::create([
            'state_subdivision_id' => 48254,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Madinat Isa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'BH-00',
        ]);
         State::create([
            'state_subdivision_id' => 48255,
            'country_code_char2' => 'BH',
            'country_code_char3' => 'BHR',
            'state_subdivision_name' => 'Jidhafs',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'BH-03',
        ]);
        State::create([
            'state_subdivision_id' => 48256,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Wallonia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'BE-WAL',
        ]);
        State::create([
            'state_subdivision_id' => 48257,
            'country_code_char2' => 'BE',
            'country_code_char3' => 'BEL',
            'state_subdivision_name' => 'Flanders',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'BE-VLG',
        ]);
        State::create([
            'state_subdivision_id' => 48258,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Hamilton',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-HA',
        ]);
        State::create([
            'state_subdivision_id' => 48259,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Devonshire ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-DS',
        ]);
        State::create([
            'state_subdivision_id' => 48260,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Saint George',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-GC',
        ]);
        State::create([
            'state_subdivision_id' => 48261,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Hamilton City ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',   
            'state_subdivision_code' => 'BM-HC',
        ]);
        State::create([
            'state_subdivision_id' => 48262,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Pembroke',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-PB',
        ]);
        State::create([
            'state_subdivision_id' => 48263,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Paget',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-PG',
        ]);
        State::create([
            'state_subdivision_id' => 48264,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Sandys',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-SA',
        ]);
        State::create([
            'state_subdivision_id' => 48265,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Saint Georges',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-SG',
        ]);
        State::create([
            'state_subdivision_id' => 48266,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Southampton',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-SH',
        ]);
        State::create([
            'state_subdivision_id' => 48267,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Smiths',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-SM',
        ]);
        State::create([
            'state_subdivision_id' => 48268,
            'country_code_char2' => 'BM',
            'country_code_char3' => 'BMU',
            'state_subdivision_name' => 'Warwick ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',   
            'state_subdivision_code' => 'BM-WA',
        ]);
          State::create([
            'state_subdivision_id' => 48269,
            'country_code_char2' => 'GM',
            'country_code_char3' => 'GMB',
            'state_subdivision_name' => 'Central River',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'GM-M',
        ]);
           State::create([
            'state_subdivision_id' => 48270,
            'country_code_char2' => 'MC',
            'country_code_char3' => 'MCO',
            'state_subdivision_name' => 'Malbousquet',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'quarter',
            'state_subdivision_code' => 'MC-MA',
        ]);
            State::create([
            'state_subdivision_id' => 48271,
            'country_code_char2' => 'MC',
            'country_code_char3' => 'MCO',
            'state_subdivision_name' => 'Monaco-Ville',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'quarter',
            'state_subdivision_code' => 'MC-MO',
        ]);
             State::create([
            'state_subdivision_id' => 48272,
            'country_code_char2' => 'MC',
            'country_code_char3' => 'MCO',
            'state_subdivision_name' => 'Moneghetti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'quarter',
            'state_subdivision_code' => 'MC-MG',
        ]);
              State::create([
            'state_subdivision_id' => 48273,
            'country_code_char2' => 'MC',
            'country_code_char3' => 'MCO',
            'state_subdivision_name' => 'Monte-Carlo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'quarter',
            'state_subdivision_code' => 'MC-MC',
        ]);
        State::create([
            'state_subdivision_id' => 48274,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Água Grande',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-01',
        ]);
        State::create([
            'state_subdivision_id' => 48275,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Cantagalo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-02',
        ]);
        State::create([
            'state_subdivision_id' => 48276,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Caué',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-03',
        ]);
        State::create([
            'state_subdivision_id' => 48277,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Lembá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-04',
        ]);
        State::create([
            'state_subdivision_id' => 48278,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Lobata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-05',
        ]);
        State::create([
            'state_subdivision_id' => 48279,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Mé-Zóchi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-06',
        ]);
        State::create([
            'state_subdivision_id' => 48280,
            'country_code_char2' => 'ST',
            'country_code_char3' => 'STP',
            'state_subdivision_name' => 'Príncipe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'ST-P',
        ]);
        State::create([
            'state_subdivision_id' => 48281,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Mariehamn',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-MH',
        ]);
        State::create([
            'state_subdivision_id' => 48282,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Jomala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-JM',
        ]);
        State::create([
            'state_subdivision_id' => 48283,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Finström',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-FN',
        ]);
         State::create([
            'state_subdivision_id' => 48284,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Lemland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-LE',
        ]);
        State::create([
            'state_subdivision_id' => 48285,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Saltvik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-SV',
        ]);
        State::create([
            'state_subdivision_id' => 48286,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Hammarland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-HM',
        ]);
        State::create([
            'state_subdivision_id' => 48287,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Sund',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-SD',
        ]);
        State::create([
            'state_subdivision_id' => 48288,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Eckerö',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-EC',
        ]);
        State::create([
            'state_subdivision_id' => 48289,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Föglö',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-FG',
        ]);
        State::create([
            'state_subdivision_id' => 48290,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Brändö',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-BR',
        ]);
        State::create([
            'state_subdivision_id' => 48291,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Geta',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-GT',
        ]);
         State::create([
            'state_subdivision_id' => 48292,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Vårdö',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-VR',
        ]);
        State::create([
            'state_subdivision_id' => 48293,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Lumparland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-LU',
        ]);
        State::create([
            'state_subdivision_id' => 48294,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Kumlinge',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-KM',
        ]);
        State::create([
            'state_subdivision_id' => 48295,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Kökar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-KK',
        ]);
        State::create([
            'state_subdivision_id' => 48296,
            'country_code_char2' => 'AX',
            'country_code_char3' => 'ALA',
            'state_subdivision_name' => 'Sottunga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'AX-ST',
        ]);
        State::create([
            'state_subdivision_id' => 48297,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Central Equatoria',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-EC',
        ]);

          State::create([
            'state_subdivision_id' => 48298,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Eastern Equatoria',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-EE',
        ]);
           State::create([
            'state_subdivision_id' => 48299,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Jonglei',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-JG',
        ]);
            State::create([
            'state_subdivision_id' => 48300,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Lakes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-LK',
        ]);
             State::create([
            'state_subdivision_id' => 48301,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Northern Bahr el Ghazal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-BN',
        ]);
            State::create([
            'state_subdivision_id' => 48302,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Unity',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-UY',
        ]);
            State::create([
            'state_subdivision_id' => 48303,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Upper Nile',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-NU',
        ]);
            State::create([
            'state_subdivision_id' => 48304,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Warrap',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-WR',
        ]);
             State::create([
            'state_subdivision_id' => 48305,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Western Bahr el Ghazal ',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-BW',
        ]);
            State::create([
            'state_subdivision_id' => 48306,
            'country_code_char2' => 'SS',
            'country_code_char3' => 'SSD',
            'state_subdivision_name' => 'Western Equatoria',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'states',
            'state_subdivision_code' => 'SS-EW',
        ]);

            State::where('state_subdivision_id','18329')->update(['state_subdivision_name' => 'Dushambe']);
            State::where('state_subdivision_id','18567')->update(['state_subdivision_name' => 'Ashgabat']);



      }
}