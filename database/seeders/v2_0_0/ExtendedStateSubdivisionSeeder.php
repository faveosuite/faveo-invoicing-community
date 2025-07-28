<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\State;
use Illuminate\Database\Seeder;

class ExtendedStateSubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        State::create([
            'state_subdivision_id' => 18247,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Skåne län [SE-12]',
            'state_subdivision_alternate_names' => 'Scania',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-M',
        ]);

        State::create([
            'state_subdivision_id' => 18248,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Dalarnas län [SE-20]',
            'state_subdivision_alternate_names' => 'Dalarnas, Dalecarlia, Kopparberg',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-W',
        ]);

        State::create([
            'state_subdivision_id' => 18249,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Gotlands län [SE-09]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-I',
        ]);

        State::create([
            'state_subdivision_id' => 18250,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Jönköpings län [SE-06]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-F',
        ]);

        State::create([
            'state_subdivision_id' => 18251,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Norrbottens län [SE-25]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-BD',
        ]);

        State::create([
            'state_subdivision_id' => 18252,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Stockholms län [SE-01]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-AB',
        ]);

        State::create([
            'state_subdivision_id' => 18253,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Västmanlands län [SE-19]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-U',
        ]);

        State::create([
            'state_subdivision_id' => 18254,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Västerbottens län [SE-24]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-AC',
        ]);

        State::create([
            'state_subdivision_id' => 18255,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Vaud (fr)',
            'state_subdivision_alternate_names' => 'Waadt',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-VD',
        ]);

        State::create([
            'state_subdivision_id' => 18256,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Zürich (de)',
            'state_subdivision_alternate_names' => 'Zurigo, Zürich, Zurich',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-ZH',
        ]);

        State::create([
            'state_subdivision_id' => 18257,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Aargau (de)',
            'state_subdivision_alternate_names' => 'Argovie',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-AG',
        ]);

        State::create([
            'state_subdivision_id' => 18258,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Appenzell Innerrhoden (de)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-AI',
        ]);

        State::create([
            'state_subdivision_id' => 18259,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Basel-Stadt (de)',
            'state_subdivision_alternate_names' => 'Basel, Basilea, Basle, Basel-Stadt, Bâle-Ville',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-BS',
        ]);

        State::create([
            'state_subdivision_id' => 18260,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Fribourg (fr)',
            'state_subdivision_alternate_names' => 'Freiburg',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-FR',
        ]);

        State::create([
            'state_subdivision_id' => 18261,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Glarus (de)',
            'state_subdivision_alternate_names' => 'Glaris',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-GL',
        ]);

        State::create([
            'state_subdivision_id' => 18262,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Jura (fr)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-JU',
        ]);

        State::create([
            'state_subdivision_id' => 18263,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Neuchâtel (fr)',
            'state_subdivision_alternate_names' => 'Neuenburg',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-NE',
        ]);

        State::create([
            'state_subdivision_id' => 18264,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Obwalden (de)',
            'state_subdivision_alternate_names' => 'Obwald',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-OW',
        ]);

        State::create([
            'state_subdivision_id' => 18265,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Graubünden (de)',
            'state_subdivision_alternate_names' => 'Grigioni, Grisons',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-GR',
        ]);

        State::create([
            'state_subdivision_id' => 18266,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Luzern (de)',
            'state_subdivision_alternate_names' => 'Lucerna, Lucerne',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-LU',
        ]);

        State::create([
            'state_subdivision_id' => 18267,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Nidwalden (de)',
            'state_subdivision_alternate_names' => 'Nidwald',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-NW',
        ]);

        State::create([
            'state_subdivision_id' => 18268,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Sankt Gallen (de)',
            'state_subdivision_alternate_names' => 'Saint Galle, Sankt Gallen, Saint-Gall',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-SG',
        ]);

        State::create([
            'state_subdivision_id' => 18269,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Schwyz (de)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-SZ',
        ]);

        State::create([
            'state_subdivision_id' => 18270,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Solothurn (de)',
            'state_subdivision_alternate_names' => 'Soleure',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-SO',
        ]);

        State::create([
            'state_subdivision_id' => 18271,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Ticino (it)',
            'state_subdivision_alternate_names' => 'Tessin, Tessin',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-TI',
        ]);

        State::create([
            'state_subdivision_id' => 18272,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Valais (fr)',
            'state_subdivision_alternate_names' => 'Vallese, Wallis',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-VS',
        ]);

        State::create([
            'state_subdivision_id' => 18273,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Zug (de)',
            'state_subdivision_alternate_names' => 'Zoug',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-ZG',
        ]);

        State::create([
            'state_subdivision_id' => 18274,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Appenzell Ausserrhoden (de)',
            'state_subdivision_alternate_names' => 'Appenzell-Ausser Rhoden',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-AR',
        ]);

        State::create([
            'state_subdivision_id' => 18275,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Basel-Landschaft (de)',
            'state_subdivision_alternate_names' => 'Bâle-Campagne',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-BL',
        ]);

        State::create([
            'state_subdivision_id' => 18276,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Bern (de)',
            'state_subdivision_alternate_names' => 'Berne',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-BE',
        ]);

        State::create([
            'state_subdivision_id' => 18277,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Genève (fr)',
            'state_subdivision_alternate_names' => 'Ginevra, Genève, Genf, Ginebra',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-GE',
        ]);

        State::create([
            'state_subdivision_id' => 18278,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Uri (de)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-UR',
        ]);

        State::create([
            'state_subdivision_id' => 18279,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Schaffhausen (de)',
            'state_subdivision_alternate_names' => 'Schaffhouse',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-SH',
        ]);

        State::create([
            'state_subdivision_id' => 18280,
            'country_code_char2' => 'CH',
            'country_code_char3' => 'CHE',
            'state_subdivision_name' => 'Thurgau (de)',
            'state_subdivision_alternate_names' => 'Thurgovie',
            'primary_level_name' => 'Canton',
            'state_subdivision_code' => 'CH-TG',
        ]);

        State::create([
            'state_subdivision_id' => 18281,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Tartus',
            'state_subdivision_alternate_names' => 'Tartoûs, Tartus',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-TA',
        ]);

        State::create([
            'state_subdivision_id' => 18282,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Ar Raqqah',
            'state_subdivision_alternate_names' => 'Raqqah, al-Rakka',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-RA',
        ]);

        State::create([
            'state_subdivision_id' => 18283,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Dimashq',
            'state_subdivision_alternate_names' => 'Dimashq, Madīnat Dimašq, Damaskus, Damas',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-DI',
        ]);

        State::create([
            'state_subdivision_id' => 18284,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => '?ims',
            'state_subdivision_alternate_names' => 'Hims, Homs',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-HI',
        ]);

        State::create([
            'state_subdivision_id' => 18285,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => '?alab',
            'state_subdivision_alternate_names' => 'Halab, Haleb, H̨alab, Alep',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-HL',
        ]);

        State::create([
            'state_subdivision_id' => 18286,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Dar?a',
            'state_subdivision_alternate_names' => 'Dara, Darā, Darʿa, Deraa, Dārayyā',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-DR',
        ]);

        State::create([
            'state_subdivision_id' => 18287,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Rif Dimashq',
            'state_subdivision_alternate_names' => 'Dimashq, Dimašq, Damaskus, Damas, Damascus',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-RD',
        ]);

        State::create([
            'state_subdivision_id' => 18288,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Al ?asakah',
            'state_subdivision_alternate_names' => 'El Haseke, Hassetche, H̨asakah, al-Hasakah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-HA',
        ]);

        State::create([
            'state_subdivision_id' => 18289,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Al Ladhiqiyah',
            'state_subdivision_alternate_names' => 'Latakia, Lattakia, Lattaquié, Lādhiqīyah, al-Ladhiqiyah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-LA',
        ]);

        State::create([
            'state_subdivision_id' => 18290,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Al Qunaytirah',
            'state_subdivision_alternate_names' => 'Quneitra, al-Qunaytirah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-QU',
        ]);

        State::create([
            'state_subdivision_id' => 18291,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'As Suwayda',
            'state_subdivision_alternate_names' => 'as-Suwayda',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-SU',

        ]);

        State::create([
            'state_subdivision_id' => 18292,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Dayr az Zawr',
            'state_subdivision_alternate_names' => 'Deir El-Zor, Deir-ez-Zor',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-DY',
        ]);

        State::create([
            'state_subdivision_id' => 18293,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => '?amah',
            'state_subdivision_alternate_names' => 'Hama, Hamah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-HM',
        ]);

        State::create([
            'state_subdivision_id' => 18294,
            'country_code_char2' => 'SY',
            'country_code_char3' => 'SYR',
            'state_subdivision_name' => 'Idlib',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'SY-ID',
        ]);

        State::create([
            'state_subdivision_id' => 15143,
            'country_code_char2' => 'LS',
            'country_code_char3' => 'LSO',
            'state_subdivision_name' => 'Quthing',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LS-G',
        ]);

        State::create([
            'state_subdivision_id' => 15144,
            'country_code_char2' => 'LS',
            'country_code_char3' => 'LSO',
            'state_subdivision_name' => 'Thaba-Tseka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LS-K',
        ]);

        State::create([
            'state_subdivision_id' => 15145,
            'country_code_char2' => 'LS',
            'country_code_char3' => 'LSO',
            'state_subdivision_name' => 'Leribe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LS-C',
        ]);

        State::create([
            'state_subdivision_id' => 15146,
            'country_code_char2' => 'LS',
            'country_code_char3' => 'LSO',
            'state_subdivision_name' => 'Maseru',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LS-A',
        ]);

        State::create([
            'state_subdivision_id' => 15147,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Sinoe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-SI',
        ]);

        State::create([
            'state_subdivision_id' => 15148,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Bong',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-BG',
        ]);

        State::create([
            'state_subdivision_id' => 15149,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Rivercess',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-RI',
        ]);

        State::create([
            'state_subdivision_id' => 15150,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Maryland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-MY',
        ]);

        State::create([
            'state_subdivision_id' => 15151,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Grand Gedeh',
            'state_subdivision_alternate_names' => 'Grand Gedah',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-GG',
        ]);

        State::create([
            'state_subdivision_id' => 15152,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Bomi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-BM',
        ]);

        State::create([
            'state_subdivision_id' => 15153,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Grand Bassa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-GB',
        ]);

        State::create([
            'state_subdivision_id' => 15154,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Lofa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-LO',
        ]);

        State::create([
            'state_subdivision_id' => 15155,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Nimba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-NI',
        ]);

        State::create([
            'state_subdivision_id' => 15156,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Grand Cape Mount',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-CM',
        ]);

        State::create([
            'state_subdivision_id' => 15157,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Montserrado',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-MO',
        ]);

        State::create([
            'state_subdivision_id' => 15158,
            'country_code_char2' => 'LR',
            'country_code_char3' => 'LBR',
            'state_subdivision_name' => 'Margibi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LR-MG',
        ]);

        State::create([
            'state_subdivision_id' => 15160,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Marqab',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-MB',
        ]);

        State::create([
            'state_subdivision_id' => 15161,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Misratah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-MI',
        ]);

        State::create([
            'state_subdivision_id' => 15162,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Nalut',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-NL',
        ]);

        State::create([
            'state_subdivision_id' => 15165,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Tarabulus',
            'state_subdivision_alternate_names' => 'Ţarābulus, Tripoli, Tripoli',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-TB',
        ]);

        State::create([
            'state_subdivision_id' => 15166,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Wadi al ?ayat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-WD',
        ]);

        State::create([
            'state_subdivision_id' => 15167,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Wāḩāt',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-WA',
        ]);

        State::create([
            'state_subdivision_id' => 15168,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Az Zawiyah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-ZA',
        ]);

        State::create([
            'state_subdivision_id' => 15171,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Butnan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-BU',
        ]);

        State::create([
            'state_subdivision_id' => 15173,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Ghat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-GT',
        ]);

        State::create([
            'state_subdivision_id' => 15174,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Jabal al Akh?ar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-JA',
        ]);

        State::create([
            'state_subdivision_id' => 15175,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Jufrah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-JU',
        ]);

        State::create([
            'state_subdivision_id' => 15176,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Kufrah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-KF',
        ]);

        State::create([
            'state_subdivision_id' => 15177,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Banghazi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-BA',
        ]);

        State::create([
            'state_subdivision_id' => 15178,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Darnah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-DR',
        ]);

        State::create([
            'state_subdivision_id' => 15181,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Jifarah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-JI',
        ]);

        State::create([
            'state_subdivision_id' => 15182,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Al Marj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-MJ',
        ]);

        State::create([
            'state_subdivision_id' => 15183,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Surt',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-SR',
        ]);

        State::create([
            'state_subdivision_id' => 15184,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Sabha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-SB',
        ]);

        State::create([
            'state_subdivision_id' => 15185,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'An Nuqat al Khams',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-NQ',
        ]);

        State::create([
            'state_subdivision_id' => 15186,
            'country_code_char2' => 'LY',
            'country_code_char3' => 'LBY',
            'state_subdivision_name' => 'Murzuq',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'popularate',
            'state_subdivision_code' => 'LY-MQ',
        ]);

        State::create([
            'state_subdivision_id' => 15190,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Vaduz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-11',
        ]);

        State::create([
            'state_subdivision_id' => 15191,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Ruggell',
            'state_subdivision_alternate_names' => 'Ruggel',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-06',
        ]);

        State::create([
            'state_subdivision_id' => 15192,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Gamprin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-03',
        ]);

        State::create([
            'state_subdivision_id' => 15193,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Balzers',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-01',
        ]);

        State::create([
            'state_subdivision_id' => 15194,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Eschen',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-02',
        ]);

        State::create([
            'state_subdivision_id' => 15195,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Mauren',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-04',
        ]);

        State::create([
            'state_subdivision_id' => 15196,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Planken',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-05',
        ]);

        State::create([
            'state_subdivision_id' => 15197,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Schaan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-07',
        ]);

        State::create([
            'state_subdivision_id' => 15198,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Triesen',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-09',
        ]);

        State::create([
            'state_subdivision_id' => 15199,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Triesenberg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-10',
        ]);

        State::create([
            'state_subdivision_id' => 15200,
            'country_code_char2' => 'LI',
            'country_code_char3' => 'LIE',
            'state_subdivision_name' => 'Schellenberg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Commune',
            'state_subdivision_code' => 'LI-08',
        ]);

        State::create([
            'state_subdivision_id' => 15201,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Vilniaus Apskritis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-VL',
        ]);

        State::create([
            'state_subdivision_id' => 15202,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Panevežio Apskritis',
            'state_subdivision_alternate_names' => 'Panevezhio',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-PN',
        ]);

        State::create([
            'state_subdivision_id' => 15203,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Utenos Apskritis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-UT',
        ]);

        State::create([
            'state_subdivision_id' => 15204,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Šiauliu Apskritis',
            'state_subdivision_alternate_names' => 'Shiauliu',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-SA',
        ]);

        State::create([
            'state_subdivision_id' => 15205,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Kauno Apskritis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-KU',
        ]);

        State::create([
            'state_subdivision_id' => 15206,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Marijampoles Apskritis',
            'state_subdivision_alternate_names' => 'Mariampoles',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-MR',
        ]);

        State::create([
            'state_subdivision_id' => 15207,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Telšiu Apskritis',
            'state_subdivision_alternate_names' => 'Telshiu',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-TE',
        ]);

        State::create([
            'state_subdivision_id' => 15208,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Klaipedos Apskritis',
            'state_subdivision_alternate_names' => 'Klaipedos',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-KL',
        ]);

        State::create([
            'state_subdivision_id' => 15209,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Alytaus Apskritis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-AL',
        ]);

        State::create([
            'state_subdivision_id' => 15210,
            'country_code_char2' => 'LT',
            'country_code_char3' => 'LTU',
            'state_subdivision_name' => 'Taurages Apskritis',
            'state_subdivision_alternate_names' => 'Taurages',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'LT-TA',
        ]);

        State::create([
            'state_subdivision_id' => 15215,
            'country_code_char2' => 'LU',
            'country_code_char3' => 'LUX',
            'state_subdivision_name' => 'Grevenmacher',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LU-G',
        ]);

        State::create([
            'state_subdivision_id' => 15216,
            'country_code_char2' => 'LU',
            'country_code_char3' => 'LUX',
            'state_subdivision_name' => 'Diekirch',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LU-D',
        ]);

        State::create([
            'state_subdivision_id' => 15220,
            'country_code_char2' => 'LU',
            'country_code_char3' => 'LUX',
            'state_subdivision_name' => 'Luxembourg (fr)',
            'state_subdivision_alternate_names' => 'Luxemburg',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'LU-L',
        ]);

        State::create([
            'state_subdivision_id' => 15223,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Veles',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-VE',
        ]);

        State::create([
            'state_subdivision_id' => 15224,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Berovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-BR',
        ]);

        State::create([
            'state_subdivision_id' => 15225,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Bitola',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-TL',
        ]);

        State::create([
            'state_subdivision_id' => 15227,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Debar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-DB',
        ]);

        State::create([
            'state_subdivision_id' => 15228,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Delcevo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-DL',
        ]);

        State::create([
            'state_subdivision_id' => 15229,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Demir Hisar',
            'state_subdivision_alternate_names' => 'Murgaševo',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-DM',
        ]);

        State::create([
            'state_subdivision_id' => 15230,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Gevgelija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-GV',
        ]);

        State::create([
            'state_subdivision_id' => 15231,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kavadarci',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-AV',
        ]);

        State::create([
            'state_subdivision_id' => 15232,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kocani',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-OC',
        ]);

        State::create([
            'state_subdivision_id' => 15233,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Gostivar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-GT',
        ]);

        State::create([
            'state_subdivision_id' => 15234,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kicevo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-KH',
        ]);

        State::create([
            'state_subdivision_id' => 15235,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kratovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-KY',
        ]);

        State::create([
            'state_subdivision_id' => 15236,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kruševo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-KS',
        ]);

        State::create([
            'state_subdivision_id' => 15237,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Negotino',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-NG',
        ]);

        State::create([
            'state_subdivision_id' => 15238,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Ohrid',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-OD',
        ]);

        State::create([
            'state_subdivision_id' => 15239,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Probištip',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-PT',
        ]);

        State::create([
            'state_subdivision_id' => 15240,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Resen',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-RE',
        ]);

        State::create([
            'state_subdivision_id' => 15241,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Štip',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-ST',
        ]);

        State::create([
            'state_subdivision_id' => 15242,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Struga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-UG',
        ]);

        State::create([
            'state_subdivision_id' => 15243,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Sveti Nikole',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-SL',
        ]);

        State::create([
            'state_subdivision_id' => 15244,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Valandovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-VA',
        ]);

        State::create([
            'state_subdivision_id' => 15245,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Vinica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-NI',
        ]);

        State::create([
            'state_subdivision_id' => 15246,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Skopje',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => '',
            'state_subdivision_code' => 'MK-X1~',
        ]);

        State::create([
            'state_subdivision_id' => 15247,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Radoviš',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-RV',
        ]);

        State::create([
            'state_subdivision_id' => 15248,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Prilep',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-PP',
        ]);

        State::create([
            'state_subdivision_id' => 15249,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kriva Palanka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-KZ',
        ]);

        State::create([
            'state_subdivision_id' => 15250,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Kumanovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-UM',
        ]);

        State::create([
            'state_subdivision_id' => 15251,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Strumica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-RU',
        ]);

        State::create([
            'state_subdivision_id' => 15252,
            'country_code_char2' => 'MK',
            'country_code_char3' => 'MKD',
            'state_subdivision_name' => 'Tetovo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MK-ET',
        ]);

        State::create([
            'state_subdivision_id' => 15253,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Toamasina',
            'state_subdivision_alternate_names' => 'Tamatave',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-A',
        ]);

        State::create([
            'state_subdivision_id' => 15254,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Antsiranana',
            'state_subdivision_alternate_names' => 'Antseranana, Diégo Suarez',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-D',
        ]);

        State::create([
            'state_subdivision_id' => 15255,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Antananarivo',
            'state_subdivision_alternate_names' => 'Tananarive',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-T',
        ]);

        State::create([
            'state_subdivision_id' => 15256,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Toliara',
            'state_subdivision_alternate_names' => 'Toliara, Tuléar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-U',
        ]);

        State::create([
            'state_subdivision_id' => 15257,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Mahajanga',
            'state_subdivision_alternate_names' => 'Majunga',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-M',
        ]);

        State::create([
            'state_subdivision_id' => 15258,
            'country_code_char2' => 'MG',
            'country_code_char3' => 'MDG',
            'state_subdivision_name' => 'Fianarantsoa',
            'state_subdivision_alternate_names' => 'Fianar',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'MG-F',
        ]);

        State::create([
            'state_subdivision_id' => 15261,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Blantyre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-BL',
        ]);

        State::create([
            'state_subdivision_id' => 15262,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Chiradzulu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-CR',
        ]);

        State::create([
            'state_subdivision_id' => 15263,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Dedza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-DE',
        ]);

        State::create([
            'state_subdivision_id' => 15264,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Dowa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-DO',
        ]);

        State::create([
            'state_subdivision_id' => 15265,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Kasungu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-KS',
        ]);

        State::create([
            'state_subdivision_id' => 15267,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Machinga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MH',
        ]);

        State::create([
            'state_subdivision_id' => 15268,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Mangochi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MG',
        ]);

        State::create([
            'state_subdivision_id' => 15269,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Mulanje',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MU',
        ]);

        State::create([
            'state_subdivision_id' => 15270,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Mwanza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MW',
        ]);

        State::create([
            'state_subdivision_id' => 15272,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Nkhotakota',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-NK',
        ]);

        State::create([
            'state_subdivision_id' => 15273,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Nsanje',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-NS',
        ]);

        State::create([
            'state_subdivision_id' => 15274,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Ntchisi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-NI',
        ]);

        State::create([
            'state_subdivision_id' => 15275,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Phalombe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-PH',
        ]);

        State::create([
            'state_subdivision_id' => 15276,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Salima',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-SA',
        ]);

        State::create([
            'state_subdivision_id' => 15277,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Thyolo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-TH',
        ]);

        State::create([
            'state_subdivision_id' => 15278,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Zomba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-ZO',
        ]);

        State::create([
            'state_subdivision_id' => 15279,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Balaka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-BA',
        ]);

        State::create([
            'state_subdivision_id' => 15280,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Chikwawa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-CK',
        ]);

        State::create([
            'state_subdivision_id' => 15281,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Chitipa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-CT',
        ]);

        State::create([
            'state_subdivision_id' => 15282,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Nkhata Bay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-NB',
        ]);

        State::create([
            'state_subdivision_id' => 15283,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Mzimba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MZ',
        ]);

        State::create([
            'state_subdivision_id' => 15284,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Mchinji',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-MC',
        ]);

        State::create([
            'state_subdivision_id' => 15285,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Karonga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-KR',
        ]);

        State::create([
            'state_subdivision_id' => 15286,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Lilongwe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-LI',
        ]);

        State::create([
            'state_subdivision_id' => 15287,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Ntcheu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-NU',
        ]);

        State::create([
            'state_subdivision_id' => 15288,
            'country_code_char2' => 'MW',
            'country_code_char3' => 'MWI',
            'state_subdivision_name' => 'Rumphi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'MW-RU',
        ]);

        State::create([
            'state_subdivision_id' => 15289,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Terengganu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-11',
        ]);

        State::create([
            'state_subdivision_id' => 15290,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Johor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-01',
        ]);

        State::create([
            'state_subdivision_id' => 15291,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Kelantan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-03',
        ]);

        State::create([
            'state_subdivision_id' => 15292,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Wilayah Persekutuan Labuan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'federal territory',
            'state_subdivision_code' => 'MY-15',
        ]);

        State::create([
            'state_subdivision_id' => 15293,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Melaka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-04',
        ]);

        State::create([
            'state_subdivision_id' => 15294,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Pahang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-06',
        ]);

        State::create([
            'state_subdivision_id' => 15295,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Perak',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-08',
        ]);

        State::create([
            'state_subdivision_id' => 15296,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Pulau Pinang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-07',
        ]);

        State::create([
            'state_subdivision_id' => 15297,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Sabah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-12',
        ]);

        State::create([
            'state_subdivision_id' => 15298,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Selangor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-10',
        ]);

        State::create([
            'state_subdivision_id' => 15299,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Kedah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-02',
        ]);

        State::create([
            'state_subdivision_id' => 15300,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Wilayah Persekutuan Kuala Lumpur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'federal territory',
            'state_subdivision_code' => 'MY-14',
        ]);

        State::create([
            'state_subdivision_id' => 15301,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Negeri Sembilan',
            'state_subdivision_alternate_names' => 'Negri Sembilan',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-05',
        ]);

        State::create([
            'state_subdivision_id' => 15302,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Perlis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-09',
        ]);

        State::create([
            'state_subdivision_id' => 15303,
            'country_code_char2' => 'MY',
            'country_code_char3' => 'MYS',
            'state_subdivision_name' => 'Sarawak',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MY-13',
        ]);

        State::create([
            'state_subdivision_id' => 15304,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Thaa',
            'state_subdivision_alternate_names' => 'Kolhumadhulu, Kolhumadulu, Kolumadulu, Thâ',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-08',
        ]);

        State::create([
            'state_subdivision_id' => 15305,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Kaafu',
            'state_subdivision_alternate_names' => 'Kaafu, Kâf, Maleʿ Atholhu, Malé Atoll',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-26',
        ]);

        State::create([
            'state_subdivision_id' => 15306,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Gnaviyani',
            'state_subdivision_alternate_names' => 'Foah Mulah, Foahmulah, Foammulah, Foamulah, Fua Mulak, Fuamulaku, Fuvahmulah, Gnyaviani',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-29',
        ]);

        State::create([
            'state_subdivision_id' => 15307,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Alif',
            'state_subdivision_alternate_names' => 'Alif Atoll Dhekunu, Ari Atholhu Uthuruburi, North Ari Atoll',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-02',
        ]);

        State::create([
            'state_subdivision_id' => 15308,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Alif Dhaal',
            'state_subdivision_alternate_names' => 'Alif Atoll Uthuru, Alif Dhâl, Ari Atholhu Dhekunuburi, South Ari Atoll',
            'primary_level_name' => '',
            'state_subdivision_code' => 'MV-X1~',
        ]);

        State::create([
            'state_subdivision_id' => 15309,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Baa',
            'state_subdivision_alternate_names' => 'Bâ, Maalhosmadhulu Dhekunuburi, Malosmadulu-South, South Maalhosmadhulu',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-20',
        ]);

        State::create([
            'state_subdivision_id' => 15310,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Dhaalu',
            'state_subdivision_alternate_names' => 'Dhaalu, Dhâl, Nilandhe Atholhu Dhekunuburi, Nilandu-South, South Nilandhe Atoll',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-17',
        ]);

        State::create([
            'state_subdivision_id' => 15311,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Faafu',
            'state_subdivision_alternate_names' => 'Faafu, Faafu Atoll, Fâf, Nilandhe Atholhu Uthuruburi, Nilandu-North, North Nilandhe, North Nilandhe Atoll',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-14',
        ]);

        State::create([
            'state_subdivision_id' => 15312,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Gaaf Alif',
            'state_subdivision_alternate_names' => 'Gaafu Alifu, Gâf Alif, Huvadhu Atholhu Uthuruburi, North Huvadhu Atoll, Suvadiva-Huvadu-North',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-27',
        ]);

        State::create([
            'state_subdivision_id' => 15313,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Gaafu Dhaalu',
            'state_subdivision_alternate_names' => 'Gaafu Dhaalu, Gâf Dhâl, Huvadhu Atholhu Dhekunuburi, South Huvadhu, Suvadiva-Huvadu-South',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-28',
        ]);

        State::create([
            'state_subdivision_id' => 15314,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Haa Alif',
            'state_subdivision_alternate_names' => 'Haa Alifu, Hâ Alif, North Thiladhunmathi, Thiladhunmathi Uthuruburi, Tiladummati-North',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-07',
        ]);

        State::create([
            'state_subdivision_id' => 15315,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Haa Dhaalu',
            'state_subdivision_alternate_names' => 'Haa Dhaalu, Hâ Dhâl, South Thiladhunmathi, Thiladhunmathi Dhekunuburi, Tiladummati-South',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-23',
        ]);

        State::create([
            'state_subdivision_id' => 15316,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Laamu',
            'state_subdivision_alternate_names' => 'Haddumati, Hadhdhunmathi, Hadhunmathi, Laamu, Lâm',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-05',
        ]);

        State::create([
            'state_subdivision_id' => 15317,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Male',
            'state_subdivision_alternate_names' => 'Maale, Mâle',
            'primary_level_name' => 'capital',
            'state_subdivision_code' => 'MV-MLE',
        ]);

        State::create([
            'state_subdivision_id' => 15318,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Raa',
            'state_subdivision_alternate_names' => 'Maalhosmadhulu Uthuruburi, Malosmadulu-North, North Maalhosmadhulu, Râ',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-13',
        ]);

        State::create([
            'state_subdivision_id' => 15319,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Seenu',
            'state_subdivision_alternate_names' => 'Addu, Addu Atholhu, Addu Atoll, Seenu, Sîn',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-01',
        ]);

        State::create([
            'state_subdivision_id' => 15320,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Vaavu',
            'state_subdivision_alternate_names' => 'Felidhe Atholhu, Felidhu Atoll, Felidu, Vaafu, Vaavu, Vâv',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-04',
        ]);

        State::create([
            'state_subdivision_id' => 15321,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Lhaviyani',
            'state_subdivision_alternate_names' => 'Faadhippolhu, Fadiffolu, Laviyani',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-03',
        ]);

        State::create([
            'state_subdivision_id' => 15322,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Shaviyani',
            'state_subdivision_alternate_names' => 'Miladhunmadhulu Uthuruburi, Miladummadulu-North, North Miladhunmadhulu',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-24',
        ]);

        State::create([
            'state_subdivision_id' => 15323,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Noonu',
            'state_subdivision_alternate_names' => 'Miladhunmadhulu Dhekunuburi, Miladummadulu-South, Mulakatholhu, Noonu, Nûn, South Miladhunmadhulu',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-25',
        ]);

        State::create([
            'state_subdivision_id' => 15324,
            'country_code_char2' => 'MV',
            'country_code_char3' => 'MDV',
            'state_subdivision_name' => 'Meemu',
            'state_subdivision_alternate_names' => 'Meemu, Mimu, Mulakatholhu, Mulaku, Mulaku Atholhu, Mîm',
            'primary_level_name' => 'administrative atoll',
            'state_subdivision_code' => 'MV-12',
        ]);

        State::create([
            'state_subdivision_id' => 15325,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Tombouctou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-6',
        ]);

        State::create([
            'state_subdivision_id' => 15326,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Mopti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-5',
        ]);

        State::create([
            'state_subdivision_id' => 15327,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Bamako',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'ML-BKO',
        ]);

        State::create([
            'state_subdivision_id' => 15328,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Kidal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-8',
        ]);

        State::create([
            'state_subdivision_id' => 15329,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Gao',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-7',
        ]);

        State::create([
            'state_subdivision_id' => 15330,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Kayes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-1',
        ]);

        State::create([
            'state_subdivision_id' => 15331,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Koulikoro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-2',
        ]);

        State::create([
            'state_subdivision_id' => 15332,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Ségou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-4',
        ]);

        State::create([
            'state_subdivision_id' => 15333,
            'country_code_char2' => 'ML',
            'country_code_char3' => 'MLI',
            'state_subdivision_name' => 'Sikasso',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'ML-3',
        ]);

        State::create([
            'state_subdivision_id' => 15340,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Wotje',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-WTJ',
        ]);

        State::create([
            'state_subdivision_id' => 15341,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Wotho',
            'state_subdivision_alternate_names' => 'Wōtto',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-WTH',
        ]);

        State::create([
            'state_subdivision_id' => 15344,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Namu',
            'state_subdivision_alternate_names' => 'Namo',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-NMU',
        ]);

        State::create([
            'state_subdivision_id' => 15345,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Majuro',
            'state_subdivision_alternate_names' => 'Mājro',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-MAJ',
        ]);

        State::create([
            'state_subdivision_id' => 15347,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Ailuk',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-ALK',
        ]);

        State::create([
            'state_subdivision_id' => 15348,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Arno',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-ARN',
        ]);

        State::create([
            'state_subdivision_id' => 15351,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Ebon',
            'state_subdivision_alternate_names' => 'Epoon',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-EBO',
        ]);

        State::create([
            'state_subdivision_id' => 15353,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Jaluit',
            'state_subdivision_alternate_names' => 'Jālwōj',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-JAL',
        ]);

        State::create([
            'state_subdivision_id' => 15355,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Kwajalein',
            'state_subdivision_alternate_names' => 'Kuwajleen',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-KWA',
        ]);

        State::create([
            'state_subdivision_id' => 15356,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Lib',
            'state_subdivision_alternate_names' => 'Ellep',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-LIB',
        ]);

        State::create([
            'state_subdivision_id' => 15357,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Likiep',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-LIK',
        ]);

        State::create([
            'state_subdivision_id' => 15358,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Maloelap',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-MAL',
        ]);

        State::create([
            'state_subdivision_id' => 15359,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Mili',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-MIL',
        ]);

        State::create([
            'state_subdivision_id' => 15360,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Namdrik',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-NMK',
        ]);

        State::create([
            'state_subdivision_id' => 15361,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Rongelap',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-RON',
        ]);

        State::create([
            'state_subdivision_id' => 15362,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Ailinglaplap',
            'state_subdivision_alternate_names' => 'Ailinglaplap',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-ALL',
        ]);

        State::create([
            'state_subdivision_id' => 15363,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Aur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-AUR',
        ]);

        State::create([
            'state_subdivision_id' => 15365,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Enewetak',
            'state_subdivision_alternate_names' => 'Eniwetok',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-ENI',
        ]);

        State::create([
            'state_subdivision_id' => 15367,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Kili',
            'state_subdivision_alternate_names' => 'Kōle',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-KIL',
        ]);

        State::create([
            'state_subdivision_id' => 15368,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Lae',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-LAE',
        ]);

        State::create([
            'state_subdivision_id' => 15369,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Mejit',
            'state_subdivision_alternate_names' => 'Mājeej',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-MEJ',
        ]);

        State::create([
            'state_subdivision_id' => 15371,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Ujae',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-UJA',
        ]);

        State::create([
            'state_subdivision_id' => 15372,
            'country_code_char2' => 'MH',
            'country_code_char3' => 'MHL',
            'state_subdivision_name' => 'Utirik',
            'state_subdivision_alternate_names' => 'Utirik, Utrōk, Utrōnk',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'MH-UTI',
        ]);

        State::create([
            'state_subdivision_id' => 15377,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Trarza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-06',
        ]);

        State::create([
            'state_subdivision_id' => 15378,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Assaba',
            'state_subdivision_alternate_names' => 'aş-Şabah, Açaba',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-03',
        ]);

        State::create([
            'state_subdivision_id' => 15379,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Adrar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-07',
        ]);

        State::create([
            'state_subdivision_id' => 15380,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Dakhlet Nouâdhibou',
            'state_subdivision_alternate_names' => 'Dakhlat Nawadibu, Đaẖlat Nawadību, Dakhlet Nouadhibou',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-08',
        ]);

        State::create([
            'state_subdivision_id' => 15381,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Hodh el Gharbi',
            'state_subdivision_alternate_names' => 'Hud-al-Garbi, Huđ-al-Ġarbi, Hodh el Gharbi',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-02',
        ]);

        State::create([
            'state_subdivision_id' => 15382,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Inchiri',
            'state_subdivision_alternate_names' => 'Inširi, Inchiri',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-12',
        ]);

        State::create([
            'state_subdivision_id' => 15383,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Tiris Zemmour',
            'state_subdivision_alternate_names' => 'Tiris Zammur, Tiris Zammūr, Tiris Zemmour',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-11',
        ]);

        State::create([
            'state_subdivision_id' => 15384,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Brakna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-05',
        ]);

        State::create([
            'state_subdivision_id' => 15385,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Tagant',
            'state_subdivision_alternate_names' => 'Tagant',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-09',
        ]);

        State::create([
            'state_subdivision_id' => 15386,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Hodh ech Chargui',
            'state_subdivision_alternate_names' => 'Hud-ash-Sharqi, Huđ-aš-Šarqi, Hodh ech Chargui',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-01',
        ]);

        State::create([
            'state_subdivision_id' => 15387,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Gorgol',
            'state_subdivision_alternate_names' => 'Gorgol',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-04',
        ]);

        State::create([
            'state_subdivision_id' => 15388,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Guidimaka',
            'state_subdivision_alternate_names' => 'Guidimaka',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'MR-10',
        ]);

        State::create([
            'state_subdivision_id' => 15389,
            'country_code_char2' => 'MR',
            'country_code_char3' => 'MRT',
            'state_subdivision_name' => 'Nouakchott',
            'state_subdivision_alternate_names' => 'Anwāksūţ, Nawakshut, Nawākšūţ, Nouakchott',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MR-NKC',
        ]);

        State::create([
            'state_subdivision_id' => 15390,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Savanne',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-SA',
        ]);

        State::create([
            'state_subdivision_id' => 15391,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Plaines Wilhems',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-PW',
        ]);

        State::create([
            'state_subdivision_id' => 15392,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Black River',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-BL',
        ]);

        State::create([
            'state_subdivision_id' => 15393,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Grand Port',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-GP',
        ]);

        State::create([
            'state_subdivision_id' => 15394,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Moka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-MO',
        ]);

        State::create([
            'state_subdivision_id' => 15395,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Flacq',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-FL',
        ]);

        State::create([
            'state_subdivision_id' => 15396,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Pamplemousses',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-PA',
        ]);

        State::create([
            'state_subdivision_id' => 15397,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Port Louis City',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-PL',
        ]);

        State::create([
            'state_subdivision_id' => 15398,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Rodrigues Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'dependency',
            'state_subdivision_code' => 'MU-RO',
        ]);

        State::create([
            'state_subdivision_id' => 15399,
            'country_code_char2' => 'MU',
            'country_code_char3' => 'MUS',
            'state_subdivision_name' => 'Rivière du Rempart',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MU-RR',
        ]);

        State::create([
            'state_subdivision_id' => 15402,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Yucatán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-YUC',
        ]);

        State::create([
            'state_subdivision_id' => 15403,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Nuevo León',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-NLE',
        ]);

        State::create([
            'state_subdivision_id' => 15404,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Puebla',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-PUE',
        ]);

        State::create([
            'state_subdivision_id' => 15405,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Querétaro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-QUE',
        ]);

        State::create([
            'state_subdivision_id' => 15406,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'San Luis Potosí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-SLP',
        ]);

        State::create([
            'state_subdivision_id' => 15407,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Sinaloa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-SIN',
        ]);

        State::create([
            'state_subdivision_id' => 15408,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Tabasco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-TAB',
        ]);

        State::create([
            'state_subdivision_id' => 15409,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Tlaxcala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-TLA',
        ]);

        State::create([
            'state_subdivision_id' => 15410,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Veracruz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-VER',
        ]);

        State::create([
            'state_subdivision_id' => 15411,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Zacatecas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-ZAC',
        ]);

        State::create([
            'state_subdivision_id' => 15412,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Baja California',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-BCN',
        ]);

        State::create([
            'state_subdivision_id' => 15413,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Baja California Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-BCS',
        ]);

        State::create([
            'state_subdivision_id' => 15414,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Chiapas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-CHP',
        ]);

        State::create([
            'state_subdivision_id' => 15415,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Coahuila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-COA',
        ]);

        State::create([
            'state_subdivision_id' => 15416,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Colima',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-COL',
        ]);

        State::create([
            'state_subdivision_id' => 15417,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Durango',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-DUR',
        ]);

        State::create([
            'state_subdivision_id' => 15418,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Guanajuato',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-GUA',
        ]);

        State::create([
            'state_subdivision_id' => 15419,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Hidalgo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-HID',
        ]);

        State::create([
            'state_subdivision_id' => 15420,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Jalisco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-JAL',
        ]);

        State::create([
            'state_subdivision_id' => 15421,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Michoacán',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-MIC',
        ]);

        State::create([
            'state_subdivision_id' => 15422,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Morelos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-MOR',
        ]);

        State::create([
            'state_subdivision_id' => 15423,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Aguascalientes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-AGU',
        ]);

        State::create([
            'state_subdivision_id' => 15424,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Campeche',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-CAM',
        ]);

        State::create([
            'state_subdivision_id' => 15425,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Chihuahua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-CHH',
        ]);

        State::create([
            'state_subdivision_id' => 15426,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Distrito Federal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'federal district',
            'state_subdivision_code' => 'MX-DIF',
        ]);

        State::create([
            'state_subdivision_id' => 15427,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Guerrero',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-GRO',
        ]);

        State::create([
            'state_subdivision_id' => 15428,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'México',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-MEX',
        ]);

        State::create([
            'state_subdivision_id' => 15429,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Nayarit',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-NAY',
        ]);

        State::create([
            'state_subdivision_id' => 15430,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Oaxaca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-OAX',
        ]);

        State::create([
            'state_subdivision_id' => 15431,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Tamaulipas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-TAM',
        ]);

        State::create([
            'state_subdivision_id' => 15432,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Quintana Roo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-ROO',
        ]);

        State::create([
            'state_subdivision_id' => 15433,
            'country_code_char2' => 'MX',
            'country_code_char3' => 'MEX',
            'state_subdivision_name' => 'Sonora',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MX-SON',
        ]);

        State::create([
            'state_subdivision_id' => 15434,
            'country_code_char2' => 'FM',
            'country_code_char3' => 'FSM',
            'state_subdivision_name' => 'Pohnpei',
            'state_subdivision_alternate_names' => 'Ascension, Ponape',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'FM-PNI',
        ]);

        State::create([
            'state_subdivision_id' => 15436,
            'country_code_char2' => 'FM',
            'country_code_char3' => 'FSM',
            'state_subdivision_name' => 'Kosrae',
            'state_subdivision_alternate_names' => 'Kosrae',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'FM-KSA',
        ]);

        State::create([
            'state_subdivision_id' => 15437,
            'country_code_char2' => 'FM',
            'country_code_char3' => 'FSM',
            'state_subdivision_name' => 'Chuuk',
            'state_subdivision_alternate_names' => 'Chuk, Hogoley Islands, Truk',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'FM-TRK',
        ]);

        State::create([
            'state_subdivision_id' => 15438,
            'country_code_char2' => 'FM',
            'country_code_char3' => 'FSM',
            'state_subdivision_name' => 'Yap',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'FM-YAP',
        ]);

        State::create([
            'state_subdivision_id' => 15440,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Ştefan Vodă',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-SV',
        ]);

        State::create([
            'state_subdivision_id' => 15441,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Rezina',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-RE',
        ]);

        State::create([
            'state_subdivision_id' => 15442,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Ialoveni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-IA',
        ]);

        State::create([
            'state_subdivision_id' => 15443,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Floreşti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-FL',
        ]);

        State::create([
            'state_subdivision_id' => 15444,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Dubăsari',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-DU',
        ]);

        State::create([
            'state_subdivision_id' => 15445,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Făleşti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-FA',
        ]);

        State::create([
            'state_subdivision_id' => 15446,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Gagauzia, Unitatea teritoriala autonoma (UTAG)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'autonomous territorial unit',
            'state_subdivision_code' => 'MD-GA',
        ]);

        State::create([
            'state_subdivision_id' => 15448,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Hînceşti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-HI',
        ]);

        State::create([
            'state_subdivision_id' => 15449,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Leova',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-LE',
        ]);

        State::create([
            'state_subdivision_id' => 15450,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Nisporeni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-NI',
        ]);

        State::create([
            'state_subdivision_id' => 15451,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Orhei',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-OR',
        ]);

        State::create([
            'state_subdivision_id' => 15453,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Bălţi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'MD-BA',
        ]);

        State::create([
            'state_subdivision_id' => 15454,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Basarabeasca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-BS',
        ]);

        State::create([
            'state_subdivision_id' => 15455,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Cahul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CA',
        ]);

        State::create([
            'state_subdivision_id' => 15458,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Cantemir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CT',
        ]);

        State::create([
            'state_subdivision_id' => 15459,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Chisinau',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'MD-CU',
        ]);

        State::create([
            'state_subdivision_id' => 15460,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Cimişlia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CM',
        ]);

        State::create([
            'state_subdivision_id' => 15461,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Donduşeni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-DO',
        ]);

        State::create([
            'state_subdivision_id' => 15463,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Rîşcani',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-RI',
        ]);

        State::create([
            'state_subdivision_id' => 15464,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Sîngerei',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-SI',
        ]);

        State::create([
            'state_subdivision_id' => 15465,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Şoldăneşti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-SD',
        ]);

        State::create([
            'state_subdivision_id' => 15466,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Soroca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-SO',
        ]);

        State::create([
            'state_subdivision_id' => 15467,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Străşeni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-ST',
        ]);

        State::create([
            'state_subdivision_id' => 15468,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Teleneşti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-TE',
        ]);

        State::create([
            'state_subdivision_id' => 15469,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Tighina',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'MD-BD',
        ]);

        State::create([
            'state_subdivision_id' => 15470,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Ungheni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-UN',
        ]);

        State::create([
            'state_subdivision_id' => 15471,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Anenii Noi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-AN',
        ]);

        State::create([
            'state_subdivision_id' => 15472,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Briceni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-BR',
        ]);

        State::create([
            'state_subdivision_id' => 15473,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Călăraşi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CL',
        ]);

        State::create([
            'state_subdivision_id' => 15474,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Căuşeni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CS',
        ]);

        State::create([
            'state_subdivision_id' => 15475,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Criuleni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-CR',
        ]);

        State::create([
            'state_subdivision_id' => 15476,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Drochia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-DR',
        ]);

        State::create([
            'state_subdivision_id' => 15477,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Edinet',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-ED',
        ]);

        State::create([
            'state_subdivision_id' => 15478,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Glodeni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-GL',
        ]);

        State::create([
            'state_subdivision_id' => 15479,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Ocniţa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-OC',
        ]);

        State::create([
            'state_subdivision_id' => 15481,
            'country_code_char2' => 'MD',
            'country_code_char3' => 'MDA',
            'state_subdivision_name' => 'Taraclia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'MD-TA',
        ]);

        State::create([
            'state_subdivision_id' => 15486,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Ulaanbaatar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'MN-1',
        ]);

        State::create([
            'state_subdivision_id' => 15487,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Selenge',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-049',
        ]);

        State::create([
            'state_subdivision_id' => 15488,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Hövsgöl',
            'state_subdivision_alternate_names' => 'Hovsgol, Khuvsgul',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-041',
        ]);

        State::create([
            'state_subdivision_id' => 15489,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Dundgovi',
            'state_subdivision_alternate_names' => 'Dundgobi',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-059',
        ]);

        State::create([
            'state_subdivision_id' => 15490,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Bayan-Ölgiy',
            'state_subdivision_alternate_names' => 'Bayan-Olgii, Bayan-Ulgii',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-071',
        ]);

        State::create([
            'state_subdivision_id' => 15491,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Bayanhongor',
            'state_subdivision_alternate_names' => 'Bayanhongor, Bayankhongor',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-069',
        ]);

        State::create([
            'state_subdivision_id' => 15492,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Bulgan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-067',
        ]);

        State::create([
            'state_subdivision_id' => 15493,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Darhan uul',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'MN-037',
        ]);

        State::create([
            'state_subdivision_id' => 15494,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Dornogovi',
            'state_subdivision_alternate_names' => 'Dornogobi',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-063',
        ]);

        State::create([
            'state_subdivision_id' => 15495,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Govi-Altay',
            'state_subdivision_alternate_names' => 'Gobi-Altai, Gobi-Altay',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-065',
        ]);

        State::create([
            'state_subdivision_id' => 15496,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Hentiy',
            'state_subdivision_alternate_names' => 'Hentii, Khentii',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-039',
        ]);

        State::create([
            'state_subdivision_id' => 15497,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Hovd',
            'state_subdivision_alternate_names' => 'Khovd',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-043',
        ]);

        State::create([
            'state_subdivision_id' => 15498,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Ömnögovi',
            'state_subdivision_alternate_names' => 'Omnogobi',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-053',
        ]);

        State::create([
            'state_subdivision_id' => 15499,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Övörhangay',
            'state_subdivision_alternate_names' => 'Ovorhangai, Uvurkhangai',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-055',
        ]);

        State::create([
            'state_subdivision_id' => 15500,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Sühbaatar',
            'state_subdivision_alternate_names' => 'Sukhbaatar',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-051',
        ]);

        State::create([
            'state_subdivision_id' => 15501,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Töv',
            'state_subdivision_alternate_names' => 'Tov',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-047',
        ]);

        State::create([
            'state_subdivision_id' => 15502,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Uvs',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-046',
        ]);

        State::create([
            'state_subdivision_id' => 15503,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Dzavhan',
            'state_subdivision_alternate_names' => 'Zavkhan',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-057',
        ]);

        State::create([
            'state_subdivision_id' => 15504,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Arhangay',
            'state_subdivision_alternate_names' => 'Arhangai, Arhangay, Arkhangai',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-073',
        ]);

        State::create([
            'state_subdivision_id' => 15505,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Dornod',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MN-061',
        ]);

        State::create([
            'state_subdivision_id' => 15506,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Govi-Sümber',
            'state_subdivision_alternate_names' => 'Gobisumber',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'MN-064',
        ]);

        State::create([
            'state_subdivision_id' => 15507,
            'country_code_char2' => 'MN',
            'country_code_char3' => 'MNG',
            'state_subdivision_name' => 'Orhon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'municipality',
            'state_subdivision_code' => 'MN-035',
        ]);

        State::create([
            'state_subdivision_id' => 15516,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Al Hoceïma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-HOC',
        ]);

        State::create([
            'state_subdivision_id' => 15517,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Tiznit',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TIZ',
        ]);

        State::create([
            'state_subdivision_id' => 15520,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Guelmim',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-GUE',
        ]);

        State::create([
            'state_subdivision_id' => 15521,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Tata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TAT',
        ]);

        State::create([
            'state_subdivision_id' => 15522,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Chichaoua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-CHI',
        ]);

        State::create([
            'state_subdivision_id' => 15523,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Essaouira',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-ESI',
        ]);

        State::create([
            'state_subdivision_id' => 15524,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'El Hajeb',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-HAJ',
        ]);

        State::create([
            'state_subdivision_id' => 15525,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Ifrane',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-IFR',
        ]);

        State::create([
            'state_subdivision_id' => 15526,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Berkane',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-BER',
        ]);

        State::create([
            'state_subdivision_id' => 15527,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Nador',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-NAD',
        ]);

        State::create([
            'state_subdivision_id' => 15528,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Taourirt',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TAI',
        ]);

        State::create([
            'state_subdivision_id' => 15535,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Safi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-SAF',
        ]);

        State::create([
            'state_subdivision_id' => 15537,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Moulay Yacoub',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => '',
            'state_subdivision_code' => 'MA-MOU',
        ]);

        State::create([
            'state_subdivision_id' => 15538,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Sidi Kacem',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-SIK',
        ]);

        State::create([
            'state_subdivision_id' => 15539,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Mediouna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-MED',
        ]);

        State::create([
            'state_subdivision_id' => 15541,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Assa-Zag',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-ASZ',
        ]);

        State::create([
            'state_subdivision_id' => 15542,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Tan-Tan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TNT',
        ]);

        State::create([
            'state_subdivision_id' => 15543,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Al Haouz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-HAO',
        ]);

        State::create([
            'state_subdivision_id' => 15544,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Kelaat es Sraghna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-KES',
        ]);

        State::create([
            'state_subdivision_id' => 15547,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Errachidia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-ERR',
        ]);

        State::create([
            'state_subdivision_id' => 15548,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Khenifra',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-KHN',
        ]);

        State::create([
            'state_subdivision_id' => 15549,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Figuig',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-FIG',
        ]);

        State::create([
            'state_subdivision_id' => 15550,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Jrada',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-JRA',
        ]);

        State::create([
            'state_subdivision_id' => 15552,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Khemisset',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-KHE',
        ]);

        State::create([
            'state_subdivision_id' => 15556,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Taroudant',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TAR',
        ]);

        State::create([
            'state_subdivision_id' => 15557,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Zagora',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-ZAG',
        ]);

        State::create([
            'state_subdivision_id' => 15558,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Beni Mellal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-BEM',
        ]);

        State::create([
            'state_subdivision_id' => 15559,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Chefchaouen',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-CHE',
        ]);

        State::create([
            'state_subdivision_id' => 15560,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Larache',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-LAR',
        ]);

        State::create([
            'state_subdivision_id' => 15562,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Taounate',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TAO',
        ]);

        State::create([
            'state_subdivision_id' => 15563,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Taza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-TAZ',
        ]);

        State::create([
            'state_subdivision_id' => 15572,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Ben Slimane',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-BES',
        ]);

        State::create([
            'state_subdivision_id' => 15573,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Khouribga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-KHO',
        ]);

        State::create([
            'state_subdivision_id' => 15574,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Settat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-SET',
        ]);

        State::create([
            'state_subdivision_id' => 15575,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'El Jadida',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-JDI',
        ]);

        State::create([
            'state_subdivision_id' => 15576,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Boulemane',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-BOM',
        ]);

        State::create([
            'state_subdivision_id' => 15577,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Sefrou',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => '',
            'state_subdivision_code' => 'MA-SEF',
        ]);

        State::create([
            'state_subdivision_id' => 15578,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Kénitra',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-KEN',
        ]);

        State::create([
            'state_subdivision_id' => 15580,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Nouaceur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-NOU',
        ]);

        State::create([
            'state_subdivision_id' => 15582,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Ouarzazate',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-OUA',
        ]);

        State::create([
            'state_subdivision_id' => 15583,
            'country_code_char2' => 'MA',
            'country_code_char3' => 'MAR',
            'state_subdivision_name' => 'Azilal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MA-AZI',
        ]);

        State::create([
            'state_subdivision_id' => 15589,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Inhambane',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-I',
        ]);

        State::create([
            'state_subdivision_id' => 15590,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Manica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-B',
        ]);

        State::create([
            'state_subdivision_id' => 15591,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Maputo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-L',
        ]);

        State::create([
            'state_subdivision_id' => 15592,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Niassa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-A',
        ]);

        State::create([
            'state_subdivision_id' => 15593,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Tete',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-T',
        ]);

        State::create([
            'state_subdivision_id' => 15599,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Cabo Delgado',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-P',
        ]);

        State::create([
            'state_subdivision_id' => 15602,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Gaza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-G',
        ]);

        State::create([
            'state_subdivision_id' => 15603,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Maputo City',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'MZ-MPM',
        ]);

        State::create([
            'state_subdivision_id' => 15604,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Nampula',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-N',
        ]);

        State::create([
            'state_subdivision_id' => 15605,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Sofala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-S',
        ]);

        State::create([
            'state_subdivision_id' => 15606,
            'country_code_char2' => 'MZ',
            'country_code_char3' => 'MOZ',
            'state_subdivision_name' => 'Zambézia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'MZ-Q',
        ]);

        State::create([
            'state_subdivision_id' => 15607,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Tanintharyi',
            'state_subdivision_alternate_names' => 'Tenasserim',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-05',
        ]);

        State::create([
            'state_subdivision_id' => 15608,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Ayeyarwady',
            'state_subdivision_alternate_names' => 'Irrawaddy',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-07',
        ]);

        State::create([
            'state_subdivision_id' => 15609,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Bago',
            'state_subdivision_alternate_names' => 'Pegu',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-02',
        ]);

        State::create([
            'state_subdivision_id' => 15610,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Kachin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-11',
        ]);

        State::create([
            'state_subdivision_id' => 15611,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Kayin',
            'state_subdivision_alternate_names' => 'Karen',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-13',
        ]);

        State::create([
            'state_subdivision_id' => 15612,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Magway',
            'state_subdivision_alternate_names' => 'Magwe',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-03',
        ]);

        State::create([
            'state_subdivision_id' => 15613,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Mon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-15',
        ]);

        State::create([
            'state_subdivision_id' => 15614,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Sagaing',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-01',
        ]);

        State::create([
            'state_subdivision_id' => 15615,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Shan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-17',
        ]);

        State::create([
            'state_subdivision_id' => 15616,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Yangon',
            'state_subdivision_alternate_names' => 'Yangon, Rangun, Rangoun, Rangún',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-06',
        ]);

        State::create([
            'state_subdivision_id' => 15617,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Chin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-14',
        ]);

        State::create([
            'state_subdivision_id' => 15618,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Kayah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-12',
        ]);

        State::create([
            'state_subdivision_id' => 15619,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Rakhine',
            'state_subdivision_alternate_names' => 'Arakan',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'MM-16',
        ]);

        State::create([
            'state_subdivision_id' => 15620,
            'country_code_char2' => 'MM',
            'country_code_char3' => 'MMR',
            'state_subdivision_name' => 'Mandalay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'division',
            'state_subdivision_code' => 'MM-04',
        ]);

        State::create([
            'state_subdivision_id' => 15621,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Oshikoto',
            'state_subdivision_alternate_names' => 'Otjikoto',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OT',
        ]);

        State::create([
            'state_subdivision_id' => 15622,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Omaheke',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OH',
        ]);

        State::create([
            'state_subdivision_id' => 15623,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Hardap',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-HA',
        ]);

        State::create([
            'state_subdivision_id' => 15624,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Khomas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-KH',
        ]);

        State::create([
            'state_subdivision_id' => 15625,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Caprivi',
            'state_subdivision_alternate_names' => 'Liambezi',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-CA',
        ]);

        State::create([
            'state_subdivision_id' => 15626,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Erongo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-ER',
        ]);

        State::create([
            'state_subdivision_id' => 15627,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Karas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-KA',
        ]);

        State::create([
            'state_subdivision_id' => 15628,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Okavango',
            'state_subdivision_alternate_names' => 'Okavango',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OK',
        ]);

        State::create([
            'state_subdivision_id' => 15629,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Kunene',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-KU',
        ]);

        State::create([
            'state_subdivision_id' => 15630,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Ohangwena',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OW',
        ]);

        State::create([
            'state_subdivision_id' => 15631,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Omusati',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OS',
        ]);

        State::create([
            'state_subdivision_id' => 15632,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Oshana',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-ON',
        ]);

        State::create([
            'state_subdivision_id' => 15633,
            'country_code_char2' => 'NA',
            'country_code_char3' => 'NAM',
            'state_subdivision_name' => 'Otjozondjupa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NA-OD',
        ]);

        State::create([
            'state_subdivision_id' => 15635,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Yaren',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-14',
        ]);

        State::create([
            'state_subdivision_id' => 15636,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Nibok',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-12',
        ]);

        State::create([
            'state_subdivision_id' => 15637,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Anabar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-02',
        ]);

        State::create([
            'state_subdivision_id' => 15638,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Uaboe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-13',
        ]);

        State::create([
            'state_subdivision_id' => 15639,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Ijuw',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-10',
        ]);

        State::create([
            'state_subdivision_id' => 15640,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Buada',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-07',
        ]);

        State::create([
            'state_subdivision_id' => 15641,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Anibare',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-04',
        ]);

        State::create([
            'state_subdivision_id' => 15642,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Aiwo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-01',
        ]);

        State::create([
            'state_subdivision_id' => 15643,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Anetan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-03',
        ]);

        State::create([
            'state_subdivision_id' => 15644,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Boe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-06',
        ]);

        State::create([
            'state_subdivision_id' => 15645,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Ewa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-09',
        ]);

        State::create([
            'state_subdivision_id' => 15646,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Meneng',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-11',
        ]);

        State::create([
            'state_subdivision_id' => 15647,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Baiti',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-05',
        ]);

        State::create([
            'state_subdivision_id' => 15648,
            'country_code_char2' => 'NR',
            'country_code_char3' => 'NRU',
            'state_subdivision_name' => 'Denigomodu',
            'state_subdivision_alternate_names' => 'Denigomodu',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'NR-08',
        ]);

        State::create([
            'state_subdivision_id' => 15725,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Utrecht',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-UT',
        ]);

        State::create([
            'state_subdivision_id' => 15726,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Gelderland',
            'state_subdivision_alternate_names' => 'Gueldre',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-GE',
        ]);

        State::create([
            'state_subdivision_id' => 15727,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Drenthe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-DR',
        ]);

        State::create([
            'state_subdivision_id' => 15728,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Zeeland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-ZE',
        ]);

        State::create([
            'state_subdivision_id' => 15729,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Noord-Brabant',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-NB',
        ]);

        State::create([
            'state_subdivision_id' => 15730,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Friesland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-FR',
        ]);

        State::create([
            'state_subdivision_id' => 15731,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Flevoland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-FL',
        ]);

        State::create([
            'state_subdivision_id' => 15732,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Groningen',
            'state_subdivision_alternate_names' => 'Groningue, Groninga',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-GR',
        ]);

        State::create([
            'state_subdivision_id' => 15733,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Overijssel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-OV',
        ]);

        State::create([
            'state_subdivision_id' => 15734,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Zuid-Holland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-ZH',
        ]);

        State::create([
            'state_subdivision_id' => 15735,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Limburg',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-LI',
        ]);

        State::create([
            'state_subdivision_id' => 15736,
            'country_code_char2' => 'NL',
            'country_code_char3' => 'NLD',
            'state_subdivision_name' => 'Noord-Holland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'NL-NH',
        ]);

        State::create([
            'state_subdivision_id' => 15746,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Waikato',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-WKO',
        ]);

        State::create([
            'state_subdivision_id' => 15747,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Northland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-NTL',
        ]);

        State::create([
            'state_subdivision_id' => 15748,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Auckland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-AUK',
        ]);

        State::create([
            'state_subdivision_id' => 15749,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Gisborne District',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Unitary Authority',
            'state_subdivision_code' => 'NZ-GIS',
        ]);

        State::create([
            'state_subdivision_id' => 15750,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Manawatu-Wanganui',
            'state_subdivision_alternate_names' => 'Wanganui-Manawatu',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-MWT',
        ]);

        State::create([
            'state_subdivision_id' => 15751,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Taranaki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-TKI',
        ]);

        State::create([
            'state_subdivision_id' => 15753,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Bay of Plenty',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-BOP',
        ]);

        State::create([
            'state_subdivision_id' => 15754,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Canterbury',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-CAN',
        ]);

        State::create([
            'state_subdivision_id' => 15755,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Hawkes s Bay',
            'state_subdivision_alternate_names' => 'Hawkes s Bay',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-HKB',
        ]);

        State::create([
            'state_subdivision_id' => 15756,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Marlborough District',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Unitary Authority',
            'state_subdivision_code' => 'NZ-MBH',
        ]);

        State::create([
            'state_subdivision_id' => 15757,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Nelson City',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Unitary Authority',
            'state_subdivision_code' => 'NZ-NSN',
        ]);

        State::create([
            'state_subdivision_id' => 15758,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Otago',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-OTA',
        ]);

        State::create([
            'state_subdivision_id' => 15759,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Southland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-STL',
        ]);

        State::create([
            'state_subdivision_id' => 15760,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Tasman District',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Unitary Authority',
            'state_subdivision_code' => 'NZ-TAS',
        ]);

        State::create([
            'state_subdivision_id' => 15761,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'Wellington',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-WGN',
        ]);

        State::create([
            'state_subdivision_id' => 15762,
            'country_code_char2' => 'NZ',
            'country_code_char3' => 'NZL',
            'state_subdivision_name' => 'West Coast',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Regional Council',
            'state_subdivision_code' => 'NZ-WTC',
        ]);

        State::create([
            'state_subdivision_id' => 15763,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Río San Juan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-SJ',
        ]);

        State::create([
            'state_subdivision_id' => 15764,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Masaya',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-MS',
        ]);

        State::create([
            'state_subdivision_id' => 15765,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Chontales',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-CO',
        ]);

        State::create([
            'state_subdivision_id' => 15766,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Carazo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-CA',
        ]);

        State::create([
            'state_subdivision_id' => 15767,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Nueva Segovia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-NS',
        ]);

        State::create([
            'state_subdivision_id' => 15768,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Managua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-MN',
        ]);

        State::create([
            'state_subdivision_id' => 15769,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Jinotega',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-JI',
        ]);

        State::create([
            'state_subdivision_id' => 15770,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Chinandega',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-CI',
        ]);

        State::create([
            'state_subdivision_id' => 15771,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Atlántico Sur*',
            'state_subdivision_alternate_names' => 'RAAS, Región Autónoma Atlántico Sur, Zelaya Sur',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'NI-AS',
        ]);

        State::create([
            'state_subdivision_id' => 15772,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Boaco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-BO',
        ]);

        State::create([
            'state_subdivision_id' => 15773,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Estelí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-ES',
        ]);

        State::create([
            'state_subdivision_id' => 15774,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'León',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-LE',
        ]);

        State::create([
            'state_subdivision_id' => 15775,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Matagalpa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-MT',
        ]);

        State::create([
            'state_subdivision_id' => 15776,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Rivas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-RI',
        ]);

        State::create([
            'state_subdivision_id' => 15777,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Granada',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-GR',
        ]);

        State::create([
            'state_subdivision_id' => 15778,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Madriz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'NI-MD',
        ]);

        State::create([
            'state_subdivision_id' => 15779,
            'country_code_char2' => 'NI',
            'country_code_char3' => 'NIC',
            'state_subdivision_name' => 'Atlántico Norte*',
            'state_subdivision_alternate_names' => 'RAAN, Región Autónoma Atlántico Norte, Zelaya Norte',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'NI-AN',
        ]);

        State::create([
            'state_subdivision_id' => 15782,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Agadez',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-1',
        ]);

        State::create([
            'state_subdivision_id' => 15783,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Diffa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-2',
        ]);

        State::create([
            'state_subdivision_id' => 15784,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Dosso',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-3',
        ]);

        State::create([
            'state_subdivision_id' => 15785,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Maradi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-4',
        ]);

        State::create([
            'state_subdivision_id' => 15786,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Niamey',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Capital District',
            'state_subdivision_code' => 'NE-8',
        ]);

        State::create([
            'state_subdivision_id' => 15787,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Tahoua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-5',
        ]);

        State::create([
            'state_subdivision_id' => 15788,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Tillabéri',
            'state_subdivision_alternate_names' => 'Tillaberi',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-6',
        ]);

        State::create([
            'state_subdivision_id' => 15789,
            'country_code_char2' => 'NE',
            'country_code_char3' => 'NER',
            'state_subdivision_name' => 'Zinder',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'NE-7',
        ]);

        State::create([
            'state_subdivision_id' => 15792,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Zamfara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-ZA',
        ]);

        State::create([
            'state_subdivision_id' => 15793,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Taraba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-TA',
        ]);

        State::create([
            'state_subdivision_id' => 15794,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Nassarawa',
            'state_subdivision_alternate_names' => 'Nasarawa',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-NA',
        ]);

        State::create([
            'state_subdivision_id' => 15795,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Ondo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-ON',
        ]);

        State::create([
            'state_subdivision_id' => 15796,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Plateau',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-PL',
        ]);

        State::create([
            'state_subdivision_id' => 15797,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Kogi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-KO',
        ]);

        State::create([
            'state_subdivision_id' => 15798,
            'country_code_char2' => 'NG',
            'country_code_char3' => 'NGA',
            'state_subdivision_name' => 'Imo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'state',
            'state_subdivision_code' => 'NG-IM',
        ]);

        State::create([
            'state_subdivision_id' => 15898,
            'country_code_char2' => 'PW',
            'country_code_char3' => 'PLW',
            'state_subdivision_name' => 'Airai',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'PW-004',
        ]);

        State::create([
            'state_subdivision_id' => 15899,
            'country_code_char2' => 'PW',
            'country_code_char3' => 'PLW',
            'state_subdivision_name' => 'Ngarchelong',
            'state_subdivision_alternate_names' => 'Ngarchelong',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'PW-218',
        ]);

        State::create([
            'state_subdivision_id' => 15916,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Panamá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-8',
        ]);

        State::create([
            'state_subdivision_id' => 15917,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Los Santos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-7',
        ]);

        State::create([
            'state_subdivision_id' => 15919,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Ngöbe-Buglé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'indigenous region',
            'state_subdivision_code' => 'PA-NB',
        ]);

        State::create([
            'state_subdivision_id' => 15920,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Chiriquí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-4',
        ]);

        State::create([
            'state_subdivision_id' => 15921,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Colón',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-3',
        ]);

        State::create([
            'state_subdivision_id' => 15922,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Darién',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-5',
        ]);

        State::create([
            'state_subdivision_id' => 15923,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Herrera',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-6',
        ]);

        State::create([
            'state_subdivision_id' => 15926,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Emberá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'indigenous region',
            'state_subdivision_code' => 'PA-EM',
        ]);

        State::create([
            'state_subdivision_id' => 15927,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Veraguas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-9',
        ]);

        State::create([
            'state_subdivision_id' => 15928,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Bocas del Toro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-1',
        ]);

        State::create([
            'state_subdivision_id' => 15929,
            'country_code_char2' => 'PA',
            'country_code_char3' => 'PAN',
            'state_subdivision_name' => 'Coclé',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PA-2',
        ]);

        State::create([
            'state_subdivision_id' => 15931,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Chimbu',
            'state_subdivision_alternate_names' => 'Chimbu, Simbúa',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-CPK',
        ]);

        State::create([
            'state_subdivision_id' => 15932,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'New Ireland',
            'state_subdivision_alternate_names' => 'Niu Ailan',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-NIK',
        ]);

        State::create([
            'state_subdivision_id' => 15933,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Manus',
            'state_subdivision_alternate_names' => 'Great Admiralty Island, Mwanus',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-MRL',
        ]);

        State::create([
            'state_subdivision_id' => 15934,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Enga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-EPW',
        ]);

        State::create([
            'state_subdivision_id' => 15935,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Central',
            'state_subdivision_alternate_names' => 'Papua Central',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-CPM',
        ]);

        State::create([
            'state_subdivision_id' => 15936,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Eastern Highlands',
            'state_subdivision_alternate_names' => 'Highlands East',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-EHG',
        ]);

        State::create([
            'state_subdivision_id' => 15937,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'East Sepik',
            'state_subdivision_alternate_names' => 'Sepik East',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-ESW',
        ]);

        State::create([
            'state_subdivision_id' => 15938,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Western',
            'state_subdivision_alternate_names' => 'Papua West, Western',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-WPD',
        ]);

        State::create([
            'state_subdivision_id' => 15939,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Madang',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-MPM',
        ]);

        State::create([
            'state_subdivision_id' => 15940,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Milne Bay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-MBA',
        ]);

        State::create([
            'state_subdivision_id' => 15941,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Morobe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-MPL',
        ]);

        State::create([
            'state_subdivision_id' => 15942,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'North Solomons',
            'state_subdivision_alternate_names' => 'Bougainville, Mekamui, North Solomons',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-NSA',
        ]);

        State::create([
            'state_subdivision_id' => 15943,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Sandaun [West Sepik]',
            'state_subdivision_alternate_names' => 'West Sepik',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-SAN',
        ]);

        State::create([
            'state_subdivision_id' => 15944,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Southern Highlands',
            'state_subdivision_alternate_names' => 'Highlands South',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-SHM',
        ]);

        State::create([
            'state_subdivision_id' => 15945,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'West New Britain',
            'state_subdivision_alternate_names' => 'New Britain West',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-WBK',
        ]);

        State::create([
            'state_subdivision_id' => 15946,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'East New Britain',
            'state_subdivision_alternate_names' => 'New Britain East',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-EBR',
        ]);

        State::create([
            'state_subdivision_id' => 15947,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Gulf',
            'state_subdivision_alternate_names' => 'Papua Gulf',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-GPK',
        ]);

        State::create([
            'state_subdivision_id' => 15948,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'National Capital District (Port Moresby)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'PG-NCD',
        ]);

        State::create([
            'state_subdivision_id' => 15949,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Northern',
            'state_subdivision_alternate_names' => 'Northern',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-NPP',
        ]);

        State::create([
            'state_subdivision_id' => 15950,
            'country_code_char2' => 'PG',
            'country_code_char3' => 'PNG',
            'state_subdivision_name' => 'Western Highlands',
            'state_subdivision_alternate_names' => 'Highlands West',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PG-WHM',
        ]);

        State::create([
            'state_subdivision_id' => 15951,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Paraguarí',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-9',
        ]);

        State::create([
            'state_subdivision_id' => 15952,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Central',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-11',
        ]);

        State::create([
            'state_subdivision_id' => 15953,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Boquerón',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-19',
        ]);

        State::create([
            'state_subdivision_id' => 15954,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Asunción',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'capital',
            'state_subdivision_code' => 'PY-ASU',
        ]);

        State::create([
            'state_subdivision_id' => 15955,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Caaguazú',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-5',
        ]);

        State::create([
            'state_subdivision_id' => 15956,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Canindeyú',
            'state_subdivision_alternate_names' => 'Canindeyú, Canindiyú',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-14',
        ]);

        State::create([
            'state_subdivision_id' => 15957,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Concepción',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-1',
        ]);

        State::create([
            'state_subdivision_id' => 15958,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Cordillera',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-3',
        ]);

        State::create([
            'state_subdivision_id' => 15959,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Itapúa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-7',
        ]);

        State::create([
            'state_subdivision_id' => 15960,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Ñeembucú',
            'state_subdivision_alternate_names' => 'Neembucú',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-12',
        ]);

        State::create([
            'state_subdivision_id' => 15961,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Presidente Hayes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-15',
        ]);

        State::create([
            'state_subdivision_id' => 15962,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'San Pedro',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-2',
        ]);

        State::create([
            'state_subdivision_id' => 15963,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Alto Paraguay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-16',
        ]);

        State::create([
            'state_subdivision_id' => 15964,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Alto Paraná',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-10',
        ]);

        State::create([
            'state_subdivision_id' => 15965,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Amambay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-13',
        ]);

        State::create([
            'state_subdivision_id' => 15966,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Caazapá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-6',
        ]);

        State::create([
            'state_subdivision_id' => 15967,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Guairá',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-4',
        ]);

        State::create([
            'state_subdivision_id' => 15968,
            'country_code_char2' => 'PY',
            'country_code_char3' => 'PRY',
            'state_subdivision_name' => 'Misiones',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PY-8',
        ]);

        State::create([
            'state_subdivision_id' => 15969,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Lima',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-LIM',
        ]);

        State::create([
            'state_subdivision_id' => 15970,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Madre de Dios',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-MDD',
        ]);

        State::create([
            'state_subdivision_id' => 15971,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Ica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-ICA',
        ]);

        State::create([
            'state_subdivision_id' => 15972,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Arequipa',
            'state_subdivision_alternate_names' => 'Areqepa',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-ARE',
        ]);

        State::create([
            'state_subdivision_id' => 15973,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Lambayeque',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-LAM',
        ]);

        State::create([
            'state_subdivision_id' => 15974,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Loreto',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-LOR',
        ]);

        State::create([
            'state_subdivision_id' => 15975,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Moquegua',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-MOQ',
        ]);

        State::create([
            'state_subdivision_id' => 15976,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Piura',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-PIU',
        ]);

        State::create([
            'state_subdivision_id' => 15977,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Puno',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-PUN',
        ]);

        State::create([
            'state_subdivision_id' => 15978,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Tacna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-TAC',
        ]);

        State::create([
            'state_subdivision_id' => 15979,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Tumbes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-TUM',
        ]);

        State::create([
            'state_subdivision_id' => 15981,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Ancash',
            'state_subdivision_alternate_names' => 'Anqas',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-ANC',
        ]);

        State::create([
            'state_subdivision_id' => 15982,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Amazonas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-AMA',
        ]);

        State::create([
            'state_subdivision_id' => 15983,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Apurímac',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-APU',
        ]);

        State::create([
            'state_subdivision_id' => 15984,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Ayacucho',
            'state_subdivision_alternate_names' => 'Ayakuchu',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-AYA',
        ]);

        State::create([
            'state_subdivision_id' => 15985,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Cajamarca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-CAJ',
        ]);

        State::create([
            'state_subdivision_id' => 15986,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Huancavelica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-HUV',
        ]);

        State::create([
            'state_subdivision_id' => 15987,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Huánuco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-HUC',
        ]);

        State::create([
            'state_subdivision_id' => 15988,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Junín',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-JUN',
        ]);

        State::create([
            'state_subdivision_id' => 15989,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Cusco [Cuzco]',
            'state_subdivision_alternate_names' => 'Cuzco, Qosqo',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-CUS',
        ]);

        State::create([
            'state_subdivision_id' => 15990,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'La Libertad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-LAL',
        ]);

        State::create([
            'state_subdivision_id' => 15991,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Pasco',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-PAS',
        ]);

        State::create([
            'state_subdivision_id' => 15992,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'El Callao',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'constitutional province',
            'state_subdivision_code' => 'PE-CAL',
        ]);

        State::create([
            'state_subdivision_id' => 15993,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'Ucayali',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-UCA',
        ]);

        State::create([
            'state_subdivision_id' => 15994,
            'country_code_char2' => 'PE',
            'country_code_char3' => 'PER',
            'state_subdivision_name' => 'San Martín',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'department',
            'state_subdivision_code' => 'PE-SAM',
        ]);

        State::create([
            'state_subdivision_id' => 16144,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Quirino',
            'state_subdivision_alternate_names' => 'Angkaki',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-QUI',
        ]);

        State::create([
            'state_subdivision_id' => 16298,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Cagayan Valley (Region II)',
            'state_subdivision_alternate_names' => 'Cagayan',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-02',
        ]);

        State::create([
            'state_subdivision_id' => 16299,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Caraga (Region XIII)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-13',
        ]);

        State::create([
            'state_subdivision_id' => 16301,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Lanao del Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-LAS',
        ]);

        State::create([
            'state_subdivision_id' => 16302,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Sulu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SLU',
        ]);

        State::create([
            'state_subdivision_id' => 16303,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Albay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ALB',
        ]);

        State::create([
            'state_subdivision_id' => 16304,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Camarines Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAN',
        ]);

        State::create([
            'state_subdivision_id' => 16305,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Catanduanes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAT',
        ]);

        State::create([
            'state_subdivision_id' => 16306,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Sorsogon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SOR',
        ]);

        State::create([
            'state_subdivision_id' => 16307,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Batanes',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BTN',
        ]);

        State::create([
            'state_subdivision_id' => 16308,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Isabela',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ISA',
        ]);

        State::create([
            'state_subdivision_id' => 16310,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Agusan del Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-AGN',
        ]);

        State::create([
            'state_subdivision_id' => 16311,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Surigao del Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SUN',
        ]);

        State::create([
            'state_subdivision_id' => 16312,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Bataan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BAN',
        ]);

        State::create([
            'state_subdivision_id' => 16313,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Nueva Ecija',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NUE',
        ]);

        State::create([
            'state_subdivision_id' => 16314,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Pampanga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-PAM',
        ]);

        State::create([
            'state_subdivision_id' => 16315,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Benguet',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BEN',
        ]);

        State::create([
            'state_subdivision_id' => 16316,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Ifugao',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-IFU',
        ]);

        State::create([
            'state_subdivision_id' => 16317,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Mountain Province',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MOU',
        ]);

        State::create([
            'state_subdivision_id' => 16318,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Eastern Samar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-EAS',
        ]);

        State::create([
            'state_subdivision_id' => 16320,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Western Samar',
            'state_subdivision_alternate_names' => 'Western Samar',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-WSA',
        ]);

        State::create([
            'state_subdivision_id' => 16321,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Ilocos Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ILN',
        ]);

        State::create([
            'state_subdivision_id' => 16322,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'La Union',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-LUN',
        ]);

        State::create([
            'state_subdivision_id' => 16323,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Pangasinan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-PAN',
        ]);

        State::create([
            'state_subdivision_id' => 16333,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Bukidnon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BUK',
        ]);

        State::create([
            'state_subdivision_id' => 16334,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Camiguin',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAM',
        ]);

        State::create([
            'state_subdivision_id' => 16335,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Misamis Oriental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MSR',
        ]);

        State::create([
            'state_subdivision_id' => 16336,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Davao del Norte',
            'state_subdivision_alternate_names' => 'Davao',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-DAV',
        ]);

        State::create([
            'state_subdivision_id' => 16337,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Davao Oriental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-DAO',
        ]);

        State::create([
            'state_subdivision_id' => 16338,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'South Cotabato',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SCO',
        ]);

        State::create([
            'state_subdivision_id' => 16340,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Cavite',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAV',
        ]);

        State::create([
            'state_subdivision_id' => 16341,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Laguna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-LAG',
        ]);

        State::create([
            'state_subdivision_id' => 16342,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Mindoro Occidental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MDC',
        ]);

        State::create([
            'state_subdivision_id' => 16343,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Palawan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-PLW',
        ]);

        State::create([
            'state_subdivision_id' => 16346,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Zamboanga del Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ZAN',
        ]);

        State::create([
            'state_subdivision_id' => 16347,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Aklan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-AKL',
        ]);

        State::create([
            'state_subdivision_id' => 16348,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Antique',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ANT',
        ]);

        State::create([
            'state_subdivision_id' => 16349,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Guimaras',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-GUI',
        ]);

        State::create([
            'state_subdivision_id' => 16350,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Negros Occidental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NEC',
        ]);

        State::create([
            'state_subdivision_id' => 16521,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Dinagat Islands',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-DIN',
        ]);

        State::create([
            'state_subdivision_id' => 16765,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Leyte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-LEY',
        ]);

        State::create([
            'state_subdivision_id' => 17082,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Romblon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ROM',
        ]);

        State::create([
            'state_subdivision_id' => 17115,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Aurora',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-AUR',
        ]);

        State::create([
            'state_subdivision_id' => 17201,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Bicol (Region V)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-05',
        ]);

        State::create([
            'state_subdivision_id' => 17202,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Central Luzon (Region III)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-03',
        ]);

        State::create([
            'state_subdivision_id' => 17203,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Central Visayas (Region VII)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-07',
        ]);

        State::create([
            'state_subdivision_id' => 17204,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Cordillera Administrative Region (CAR)',
            'state_subdivision_alternate_names' => 'Cordillera, Cordillera Administrative Region',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-15',
        ]);

        State::create([
            'state_subdivision_id' => 17205,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Eastern Visayas (Region VIII)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-08',
        ]);

        State::create([
            'state_subdivision_id' => 17206,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Ilocos (Region I)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-01',
        ]);

        State::create([
            'state_subdivision_id' => 17207,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Autonomous Region in Muslim Mindanao (ARMM)',
            'state_subdivision_alternate_names' => 'Muslim Mindanao',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-14',
        ]);

        State::create([
            'state_subdivision_id' => 17208,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'National Capital Region',
            'state_subdivision_alternate_names' => 'National Capital Region, National Capital Region Region',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-00',
        ]);

        State::create([
            'state_subdivision_id' => 17209,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Northern Mindanao (Region X)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-10',
        ]);

        State::create([
            'state_subdivision_id' => 17213,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Western Visayas (Region VI)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'PH-06',
        ]);

        State::create([
            'state_subdivision_id' => 17214,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Abra',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ABR',
        ]);

        State::create([
            'state_subdivision_id' => 17215,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Apayao',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-APA',
        ]);

        State::create([
            'state_subdivision_id' => 17216,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Kalinga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-KAL',
        ]);

        State::create([
            'state_subdivision_id' => 17217,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Biliran',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BIL',
        ]);

        State::create([
            'state_subdivision_id' => 17218,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Northern Samar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NSA',
        ]);

        State::create([
            'state_subdivision_id' => 17219,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Southern Leyte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SLE',
        ]);

        State::create([
            'state_subdivision_id' => 17220,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Ilocos Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ILS',
        ]);

        State::create([
            'state_subdivision_id' => 17229,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Misamis Occidental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MSC',
        ]);

        State::create([
            'state_subdivision_id' => 17230,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Compostela Valley',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-COM',
        ]);

        State::create([
            'state_subdivision_id' => 17231,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Davao del Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-DAS',
        ]);

        State::create([
            'state_subdivision_id' => 17232,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Sarangani',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SAR',
        ]);

        State::create([
            'state_subdivision_id' => 17233,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Batangas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BTG',
        ]);

        State::create([
            'state_subdivision_id' => 17234,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Marinduque',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MAD',
        ]);

        State::create([
            'state_subdivision_id' => 17235,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Mindoro Oriental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MDR',
        ]);

        State::create([
            'state_subdivision_id' => 17237,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Basilan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BAS',
        ]);

        State::create([
            'state_subdivision_id' => 17238,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Zamboanga del Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ZAS',
        ]);

        State::create([
            'state_subdivision_id' => 17239,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Capiz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAP',
        ]);

        State::create([
            'state_subdivision_id' => 17240,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Iloilo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ILI',
        ]);

        State::create([
            'state_subdivision_id' => 17287,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Maguindanao',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MAG',
        ]);

        State::create([
            'state_subdivision_id' => 17288,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Tawi-Tawi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-TAW',
        ]);

        State::create([
            'state_subdivision_id' => 17289,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Camarines Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAS',
        ]);

        State::create([
            'state_subdivision_id' => 17290,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Masbate',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-MAS',
        ]);

        State::create([
            'state_subdivision_id' => 17291,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Cagayan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CAG',
        ]);

        State::create([
            'state_subdivision_id' => 17292,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Nueva Vizcaya',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NUV',
        ]);

        State::create([
            'state_subdivision_id' => 17293,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Agusan del Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-AGS',
        ]);

        State::create([
            'state_subdivision_id' => 17294,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Surigao del Sur',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SUR',
        ]);

        State::create([
            'state_subdivision_id' => 17295,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Bulacan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BUL',
        ]);

        State::create([
            'state_subdivision_id' => 17296,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Tarlac',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-TAR',
        ]);

        State::create([
            'state_subdivision_id' => 17297,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Zambales',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ZMB',
        ]);

        State::create([
            'state_subdivision_id' => 17298,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'North Cotabato',
            'state_subdivision_alternate_names' => 'North Cotabato',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NCO',
        ]);

        State::create([
            'state_subdivision_id' => 17300,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Lanao del Norte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-LAN',
        ]);

        State::create([
            'state_subdivision_id' => 17302,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Sultan Kudarat',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SUK',
        ]);

        State::create([
            'state_subdivision_id' => 17303,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Bohol',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-BOH',
        ]);

        State::create([
            'state_subdivision_id' => 17304,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Cebu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-CEB',
        ]);

        State::create([
            'state_subdivision_id' => 17305,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Negros Oriental',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-NER',
        ]);

        State::create([
            'state_subdivision_id' => 17306,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Siquijor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-SIG',
        ]);

        State::create([
            'state_subdivision_id' => 17583,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Rizal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-RIZ',
        ]);

        State::create([
            'state_subdivision_id' => 17595,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Quezon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-QUE',
        ]);

        State::create([
            'state_subdivision_id' => 17672,
            'country_code_char2' => 'PH',
            'country_code_char3' => 'PHL',
            'state_subdivision_name' => 'Zamboanga Sibuguey [Zamboanga Sibugay]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'PH-ZSI',
        ]);

        State::create([
            'state_subdivision_id' => 17730,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Zachodniopomorskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-ZP',
        ]);

        State::create([
            'state_subdivision_id' => 17731,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Dolnoslaskie',
            'state_subdivision_alternate_names' => 'Dolnoslaskie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-DS',
        ]);

        State::create([
            'state_subdivision_id' => 17732,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Kujawsko-pomorskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-KP',
        ]);

        State::create([
            'state_subdivision_id' => 17733,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Lubelskie',
            'state_subdivision_alternate_names' => 'Lodzkie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-LU',
        ]);

        State::create([
            'state_subdivision_id' => 17734,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Lódzkie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-LD',
        ]);

        State::create([
            'state_subdivision_id' => 17735,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Mazowieckie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-MZ',
        ]);

        State::create([
            'state_subdivision_id' => 17736,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Podkarpackie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-PK',
        ]);

        State::create([
            'state_subdivision_id' => 17737,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Pomorskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-PM',
        ]);

        State::create([
            'state_subdivision_id' => 17738,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Swietokrzyskie',
            'state_subdivision_alternate_names' => 'Swietokrzyskie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-SK',
        ]);

        State::create([
            'state_subdivision_id' => 17739,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Wielkopolskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-WP',
        ]);

        State::create([
            'state_subdivision_id' => 17740,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Lubuskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-LB',
        ]);

        State::create([
            'state_subdivision_id' => 17741,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Warminsko-mazurskie',
            'state_subdivision_alternate_names' => 'Warminsko-Mazurskie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-WN',
        ]);

        State::create([
            'state_subdivision_id' => 17742,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Podlaskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-PD',
        ]);

        State::create([
            'state_subdivision_id' => 17743,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Opolskie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-OP',
        ]);

        State::create([
            'state_subdivision_id' => 17744,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Malopolskie',
            'state_subdivision_alternate_names' => 'Malopolskie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-MA',
        ]);

        State::create([
            'state_subdivision_id' => 17745,
            'country_code_char2' => 'PL',
            'country_code_char3' => 'POL',
            'state_subdivision_name' => 'Slaskie',
            'state_subdivision_alternate_names' => 'Slaskie',
            'primary_level_name' => 'Voivodship',
            'state_subdivision_code' => 'PL-SL',
        ]);

        State::create([
            'state_subdivision_id' => 17748,
            'country_code_char2' => 'PT',
            'country_code_char3' => 'PRT',
            'state_subdivision_name' => 'Região Autónoma dos Açores',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'PT-20',
        ]);

        State::create([
            'state_subdivision_id' => 17751,
            'country_code_char2' => 'PT',
            'country_code_char3' => 'PRT',
            'state_subdivision_name' => 'Região Autónoma da Madeira',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'PT-30',
        ]);

        State::create([
            'state_subdivision_id' => 17762,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Umm Salal',
            'state_subdivision_alternate_names' => 'Umm Shalal',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-US',
        ]);

        State::create([
            'state_subdivision_id' => 17763,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Al Wakrah',
            'state_subdivision_alternate_names' => 'Wakra, Wakrah',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-WA',
        ]);

        State::create([
            'state_subdivision_id' => 17764,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Al Jumayliyah',
            'state_subdivision_alternate_names' => 'al-Jumayliyah',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-JU',
        ]);

        State::create([
            'state_subdivision_id' => 17765,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Al Khawr',
            'state_subdivision_alternate_names' => 'H̱ūr, al-Khawr, al-Khour, al-H̱awr',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-KH',
        ]);

        State::create([
            'state_subdivision_id' => 17766,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Madinat ash Shamal',
            'state_subdivision_alternate_names' => 'ash-Shamal',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-MS',
        ]);

        State::create([
            'state_subdivision_id' => 17767,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Umm Sa id',
            'state_subdivision_alternate_names' => 'Mesaieed',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-X1~',
        ]);

        State::create([
            'state_subdivision_id' => 17768,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Jariyan al Batnah',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-JB',
        ]);

        State::create([
            'state_subdivision_id' => 17769,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Ad Dawhah',
            'state_subdivision_alternate_names' => 'Dawhah, Doha, ad-Dawhah, ad-Dawh̨ah, Doha, Doha, Doha',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-DA',
        ]);

        State::create([
            'state_subdivision_id' => 17770,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Al Ghuwayriyah',
            'state_subdivision_alternate_names' => 'al-Ghuwayriyah',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-GH',
        ]);

        State::create([
            'state_subdivision_id' => 17771,
            'country_code_char2' => 'QA',
            'country_code_char3' => 'QAT',
            'state_subdivision_name' => 'Ar Rayyan',
            'state_subdivision_alternate_names' => 'Rayyan, ar-Rayyan',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'QA-RA',
        ]);

        State::create([
            'state_subdivision_id' => 17781,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Vrancea',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-VN',
        ]);

        State::create([
            'state_subdivision_id' => 17782,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Ialomita',
            'state_subdivision_alternate_names' => 'Ialomita',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-IL',
        ]);

        State::create([
            'state_subdivision_id' => 17783,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Brasov',
            'state_subdivision_alternate_names' => 'Brasov',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BV',
        ]);

        State::create([
            'state_subdivision_id' => 17784,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Buzau',
            'state_subdivision_alternate_names' => 'Buzau',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BZ',
        ]);

        State::create([
            'state_subdivision_id' => 17785,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Caras-Severin',
            'state_subdivision_alternate_names' => 'Caras-Severin',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-CS',
        ]);

        State::create([
            'state_subdivision_id' => 17786,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Constanta',
            'state_subdivision_alternate_names' => 'Constanta, Konstanza',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-CT',
        ]);

        State::create([
            'state_subdivision_id' => 17787,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Dâmbovita',
            'state_subdivision_alternate_names' => 'Dambovita, Dimbovita, Dîmboviţa',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-DB',
        ]);

        State::create([
            'state_subdivision_id' => 17788,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Dolj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-DJ',
        ]);

        State::create([
            'state_subdivision_id' => 17789,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Giurgiu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-GR',
        ]);

        State::create([
            'state_subdivision_id' => 17790,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Gorj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-GJ',
        ]);

        State::create([
            'state_subdivision_id' => 17791,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Hunedoara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-HD',
        ]);

        State::create([
            'state_subdivision_id' => 17792,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Iasi',
            'state_subdivision_alternate_names' => 'Iasi, Jasch, Jassy, Yassy',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-IS',
        ]);

        State::create([
            'state_subdivision_id' => 17793,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Maramures',
            'state_subdivision_alternate_names' => 'Maramures',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-MM',
        ]);

        State::create([
            'state_subdivision_id' => 17794,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Alba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-AB',
        ]);

        State::create([
            'state_subdivision_id' => 17795,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Arad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-AR',
        ]);

        State::create([
            'state_subdivision_id' => 17796,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Arges',
            'state_subdivision_alternate_names' => 'Arges',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-AG',
        ]);

        State::create([
            'state_subdivision_id' => 17797,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Bacau',
            'state_subdivision_alternate_names' => 'Bacau',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BC',
        ]);

        State::create([
            'state_subdivision_id' => 17798,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Bihor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BH',
        ]);

        State::create([
            'state_subdivision_id' => 17799,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Bistrita-Nasaud',
            'state_subdivision_alternate_names' => 'Bistrita-Nasaud',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BN',
        ]);

        State::create([
            'state_subdivision_id' => 17800,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Botosani',
            'state_subdivision_alternate_names' => 'Botosani',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BT',
        ]);

        State::create([
            'state_subdivision_id' => 17801,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Braila',
            'state_subdivision_alternate_names' => 'Braila',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-BR',
        ]);

        State::create([
            'state_subdivision_id' => 17802,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Bucuresti',
            'state_subdivision_alternate_names' => 'Bucuresti, Bucureşti, Bukarest, Bucarest, Bucarest',
            'primary_level_name' => 'City',
            'state_subdivision_code' => 'RO-B',
        ]);

        State::create([
            'state_subdivision_id' => 17803,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Calarasi',
            'state_subdivision_alternate_names' => 'Calarasi',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-CL',
        ]);

        State::create([
            'state_subdivision_id' => 17804,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Cluj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-CJ',
        ]);

        State::create([
            'state_subdivision_id' => 17805,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Covasna',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-CV',
        ]);

        State::create([
            'state_subdivision_id' => 17806,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Galati',
            'state_subdivision_alternate_names' => 'Galati, Galatz',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-GL',
        ]);

        State::create([
            'state_subdivision_id' => 17807,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Harghita',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-HR',
        ]);

        State::create([
            'state_subdivision_id' => 17808,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Mehedinti',
            'state_subdivision_alternate_names' => 'Mehedinti',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-MH',
        ]);

        State::create([
            'state_subdivision_id' => 17809,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Neamt',
            'state_subdivision_alternate_names' => 'Neamt',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-NT',
        ]);

        State::create([
            'state_subdivision_id' => 17810,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Prahova',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-PH',
        ]);

        State::create([
            'state_subdivision_id' => 17811,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Salaj',
            'state_subdivision_alternate_names' => 'Salaj',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-SJ',
        ]);

        State::create([
            'state_subdivision_id' => 17812,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Sibiu',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-SB',
        ]);

        State::create([
            'state_subdivision_id' => 17813,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Suceava',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-SV',
        ]);

        State::create([
            'state_subdivision_id' => 17814,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Timis',
            'state_subdivision_alternate_names' => 'Timis',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-TM',
        ]);

        State::create([
            'state_subdivision_id' => 17815,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Tulcea',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-TL',
        ]);

        State::create([
            'state_subdivision_id' => 17816,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Vaslui',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-VS',
        ]);

        State::create([
            'state_subdivision_id' => 17817,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Ilfov',
            'state_subdivision_alternate_names' => 'Sectorul Agricol Ilfov',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-IF',
        ]);

        State::create([
            'state_subdivision_id' => 17818,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Vâlcea',
            'state_subdivision_alternate_names' => 'Vîlcea',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-VL',
        ]);

        State::create([
            'state_subdivision_id' => 17819,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Satu Mare',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-SM',
        ]);

        State::create([
            'state_subdivision_id' => 17820,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Olt',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-OT',
        ]);

        State::create([
            'state_subdivision_id' => 17821,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Mures',
            'state_subdivision_alternate_names' => 'Mures',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-MS',
        ]);

        State::create([
            'state_subdivision_id' => 17822,
            'country_code_char2' => 'RO',
            'country_code_char3' => 'ROU',
            'state_subdivision_name' => 'Teleorman',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'RO-TR',
        ]);

        State::create([
            'state_subdivision_id' => 17823,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Vologodskaya oblast',
            'state_subdivision_alternate_names' => 'Vologodskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-VL',
        ]);

        State::create([
            'state_subdivision_id' => 17824,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tatarstan, Respublika',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-TA',
        ]);

        State::create([
            'state_subdivision_id' => 17825,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Primorskiy kray',
            'state_subdivision_alternate_names' => 'Primorskij, Primorskij Kraj, Primorje',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-PRI',
        ]);

        State::create([
            'state_subdivision_id' => 17826,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Ryazanskaya oblast',
            'state_subdivision_alternate_names' => 'Rjazanskaja Oblast, Rjazan',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-RYA',
        ]);

        State::create([
            'state_subdivision_id' => 17827,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Sakha, Respublika [Yakutiya]',
            'state_subdivision_alternate_names' => 'Jakutija, Sakha, Yakutsk-Sakha, Saha',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-SA',
        ]);

        State::create([
            'state_subdivision_id' => 17828,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Sakhalinskaya oblast',
            'state_subdivision_alternate_names' => 'Sahalinskaya Oblast, Sahalin',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-SAK',
        ]);

        State::create([
            'state_subdivision_id' => 17829,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Sankt-Peterburg',
            'state_subdivision_alternate_names' => 'San Pietroburgo, Sankt-Peterburg, Sankt Petersburg, Saint-Pétersbourg, San Petersburgo',
            'primary_level_name' => 'autonomous city',
            'state_subdivision_code' => 'RU-SPE',
        ]);

        State::create([
            'state_subdivision_id' => 17830,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Smolenskaya oblast',
            'state_subdivision_alternate_names' => 'Smolenskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-SMO',
        ]);

        State::create([
            'state_subdivision_id' => 17831,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Stavropol skiy kray',
            'state_subdivision_alternate_names' => 'Stavropolskij Kraj',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-STA',
        ]);

        State::create([
            'state_subdivision_id' => 17832,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Sverdlovskaya oblast',
            'state_subdivision_alternate_names' => 'Sverdlovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-SVE',
        ]);

        State::create([
            'state_subdivision_id' => 17833,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tambovskaya oblast',
            'state_subdivision_alternate_names' => 'Tambovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-TAM',
        ]);

        State::create([
            'state_subdivision_id' => 17834,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Mariy El, Respublika',
            'state_subdivision_alternate_names' => 'Mariy El, Marij El',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-ME',
        ]);

        State::create([
            'state_subdivision_id' => 17835,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Moskva',
            'state_subdivision_alternate_names' => 'Moskovskaja Oblast',
            'primary_level_name' => 'autonomous city',
            'state_subdivision_code' => 'RU-MOW',
        ]);

        State::create([
            'state_subdivision_id' => 17836,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Moskovskaya oblast',
            'state_subdivision_alternate_names' => 'Moskva, Moskau, Moscou, Moscú',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-MOS',
        ]);

        State::create([
            'state_subdivision_id' => 17837,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Nenetskiy avtonomnyy okrug',
            'state_subdivision_alternate_names' => 'Nenetskij Avtonomnyj Okrug',
            'primary_level_name' => 'autonomous district',
            'state_subdivision_code' => 'RU-NEN',
        ]);

        State::create([
            'state_subdivision_id' => 17838,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Nizhegorodskaya oblast',
            'state_subdivision_alternate_names' => 'Gorki, Gorkij, Gorky, Nizhegorodskaya Oblast, Nižegorodskaja Oblast, Nižnij Novgorod',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-NIZ',
        ]);

        State::create([
            'state_subdivision_id' => 17839,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Novosibirskaya oblast',
            'state_subdivision_alternate_names' => 'Novosibirskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-NVS',
        ]);

        State::create([
            'state_subdivision_id' => 17840,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Orenburgskaya oblast',
            'state_subdivision_alternate_names' => 'Orenburgskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-ORE',
        ]);

        State::create([
            'state_subdivision_id' => 17841,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Orlovskaya oblast',
            'state_subdivision_alternate_names' => 'Orlovskaja Oblast, Orjol',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-ORL',
        ]);

        State::create([
            'state_subdivision_id' => 17842,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Perm',
            'state_subdivision_alternate_names' => 'Permskaja Oblast',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-PER',
        ]);

        State::create([
            'state_subdivision_id' => 17843,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kemerovskaya oblast',
            'state_subdivision_alternate_names' => 'Kemerovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KEM',
        ]);

        State::create([
            'state_subdivision_id' => 17844,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kirovskaya oblast',
            'state_subdivision_alternate_names' => 'Kirovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KIR',

        ]);

        State::create([
            'state_subdivision_id' => 17845,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Komi, Respublika',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KO',
        ]);

        State::create([
            'state_subdivision_id' => 17846,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Komi-Permyak',
            'state_subdivision_alternate_names' => 'Komi-Permjatskij Avtonomnyj Okrug, Komi-Permjak',
            'primary_level_name' => '',
            'state_subdivision_code' => 'RU-X1~',
        ]);

        State::create([
            'state_subdivision_id' => 17847,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kostromskaya oblast',
            'state_subdivision_alternate_names' => 'Kostromskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KOS',

        ]);

        State::create([
            'state_subdivision_id' => 17848,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Krasnoyarskiy kray',
            'state_subdivision_alternate_names' => 'Krasnojarsk, Krasnojarskij Kraj, Krasnojarsk',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-KYA',
        ]);

        State::create([
            'state_subdivision_id' => 17849,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kurganskaya oblast',
            'state_subdivision_alternate_names' => 'Kurganskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KGN',
        ]);

        State::create([
            'state_subdivision_id' => 17850,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Leningradskaya oblast',
            'state_subdivision_alternate_names' => 'Leningradskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-LEN',

        ]);

        State::create([
            'state_subdivision_id' => 17851,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Magadanskaya oblast',
            'state_subdivision_alternate_names' => 'Magadanskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-MAG',

        ]);

        State::create([
            'state_subdivision_id' => 17852,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Severnaya Osetiya, Respublika [Alaniya] [Respublika Severnaya Osetiya-Alaniya]',
            'state_subdivision_alternate_names' => 'Alania, North Ossetian Republic, Osetija, Alanija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-SE',
        ]);

        State::create([
            'state_subdivision_id' => 17853,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Arkhangel skaya oblast',
            'state_subdivision_alternate_names' => 'Arhangelskaja Oblast, Arhangelsk',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-ARK',
        ]);

        State::create([
            'state_subdivision_id' => 17854,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Belgorodskaya oblast',
            'state_subdivision_alternate_names' => 'Belgorodskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-BEL',
        ]);

        State::create([
            'state_subdivision_id' => 17855,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Chechenskaya Respublika',
            'state_subdivision_alternate_names' => 'Chechen, Chechenia, Ichkeria, Ičkeria, Čečens, Čečenskaja Respublika, Čečenija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-CE',
        ]);

        State::create([
            'state_subdivision_id' => 17856,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Chuvashskaya Respublika',
            'state_subdivision_alternate_names' => 'Chuvash Republic, Chuvashskaya Respublika, Čuvašskaja Respublika, Čuvašija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-CU',
        ]);

        State::create([
            'state_subdivision_id' => 17857,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Altay, Respublika',
            'state_subdivision_alternate_names' => 'Altaj, Altay, Gorno-Altaj',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-AL',
        ]);

        State::create([
            'state_subdivision_id' => 17858,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Khanty-Mansiyskiy avtonomnyy okrug [Yugra]',
            'state_subdivision_alternate_names' => 'Hanty-Mansijskij Avtonomnyj Okrug, Hanty-Mansija',
            'primary_level_name' => 'autonomous district',
            'state_subdivision_code' => 'RU-KHM',
        ]);

        State::create([
            'state_subdivision_id' => 17859,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Ivanovskaya oblast',
            'state_subdivision_alternate_names' => 'Ivanovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-IVA',

        ]);

        State::create([
            'state_subdivision_id' => 17861,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Krasnodarskiy kray',
            'state_subdivision_alternate_names' => 'Krasnodarskij Kraj',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-KDA',
        ]);

        State::create([
            'state_subdivision_id' => 17862,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kurskaya oblast',
            'state_subdivision_alternate_names' => 'Kurskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KRS',
        ]);

        State::create([
            'state_subdivision_id' => 17863,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Lipetskaya oblast',
            'state_subdivision_alternate_names' => 'Lipeckaja Oblast, Lipec',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-LIP',
        ]);

        State::create([
            'state_subdivision_id' => 17864,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Mordoviya, Respublika',
            'state_subdivision_alternate_names' => 'Mordovian Republic, Mordovija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-MO',
        ]);

        State::create([
            'state_subdivision_id' => 17865,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Murmanskaya oblast',
            'state_subdivision_alternate_names' => 'Murmanskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-MUR',

        ]);

        State::create([
            'state_subdivision_id' => 17866,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Novgorodskaya oblast',
            'state_subdivision_alternate_names' => 'Novgorodskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-NGR',

        ]);

        State::create([
            'state_subdivision_id' => 17867,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Omskaya oblast',
            'state_subdivision_alternate_names' => 'Omskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-OMS',

        ]);

        State::create([
            'state_subdivision_id' => 17868,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Penzenskaya oblast',
            'state_subdivision_alternate_names' => 'Penzenskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-PNZ',

        ]);

        State::create([
            'state_subdivision_id' => 17869,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Pskovskaya oblast',
            'state_subdivision_alternate_names' => 'Pihkva, Pleskau, Pskovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-PSK',

        ]);

        State::create([
            'state_subdivision_id' => 17870,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Rostovskaya oblast',
            'state_subdivision_alternate_names' => 'Rostovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-ROS',

        ]);

        State::create([
            'state_subdivision_id' => 17871,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Samarskaya oblast',
            'state_subdivision_alternate_names' => 'Samarskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-SAM',

        ]);

        State::create([
            'state_subdivision_id' => 17872,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Saratovskaya oblast',
            'state_subdivision_alternate_names' => 'Saratovskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-SAR',

        ]);

        State::create([
            'state_subdivision_id' => 17874,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tyumenskaya oblast',
            'state_subdivision_alternate_names' => 'Tjumenskaja Oblast, Tumen, Tjumen',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-TYU',

        ]);

        State::create([
            'state_subdivision_id' => 17875,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tul skaya oblast',
            'state_subdivision_alternate_names' => 'Tulskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-TUL',
        ]);

        State::create([
            'state_subdivision_id' => 17876,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Udmurtskaya Respublika',
            'state_subdivision_alternate_names' => 'Udmurt Republic, Udmurtskaya Respublika, Udmurtija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-UD',
        ]);

        State::create([
            'state_subdivision_id' => 17877,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Vladimirskaya oblast',
            'state_subdivision_alternate_names' => 'Vladimirskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-VLA',

        ]);

        State::create([
            'state_subdivision_id' => 17878,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Voronezhskaya oblast',
            'state_subdivision_alternate_names' => 'Voronežskaja Oblast, Voronež',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-VOR',

        ]);

        State::create([
            'state_subdivision_id' => 17879,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Adygeya, Respublika',
            'state_subdivision_alternate_names' => 'Adygei Republic, Adygeja',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-AD',
        ]);

        State::create([
            'state_subdivision_id' => 17881,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Altayskiy kray',
            'state_subdivision_alternate_names' => 'Altai Kray, Altajskij Kray, Altajskiy Kraj, Altaj',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-ALT',
        ]);

        State::create([
            'state_subdivision_id' => 17882,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Amurskaya oblast',
            'state_subdivision_alternate_names' => 'Amurskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-AMU',

        ]);

        State::create([
            'state_subdivision_id' => 17883,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Astrakhanskaya oblast',
            'state_subdivision_alternate_names' => 'Astrahanska Oblast, Astrahan',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-AST',

        ]);

        State::create([
            'state_subdivision_id' => 17884,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Bashkortostan, Respublika',
            'state_subdivision_alternate_names' => 'Baškortostan',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-BA',
        ]);

        State::create([
            'state_subdivision_id' => 17885,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Bryanskaya oblast',
            'state_subdivision_alternate_names' => 'Brjanskaja Oblast, Brjansk',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-BRY',

        ]);

        State::create([
            'state_subdivision_id' => 17886,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Buryatiya, Respublika',
            'state_subdivision_alternate_names' => 'Buryat Republic, Burjatija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-BU',
        ]);

        State::create([
            'state_subdivision_id' => 17887,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Chelyabinskaya oblast',
            'state_subdivision_alternate_names' => 'Cheljabinsk, Čeljabinskaja Oblast, Čeljabinsk',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-CHE',

        ]);

        State::create([
            'state_subdivision_id' => 17889,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Chukotskiy avtonomnyy okrug',
            'state_subdivision_alternate_names' => 'Chuckchi, Čukotskij Avtonomnyj Okrug, Čukči, Čukotka',
            'primary_level_name' => 'autonomous district',
            'state_subdivision_code' => 'RU-CHU',
        ]);

        State::create([
            'state_subdivision_id' => 17890,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Dagestan, Respublika',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-DA',
        ]);

        State::create([
            'state_subdivision_id' => 17892,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Khabarovskiy kray',
            'state_subdivision_alternate_names' => 'Habarovskij Kray, Habarovsk',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-KHA',
        ]);

        State::create([
            'state_subdivision_id' => 17893,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Khakasiya, Respublika',
            'state_subdivision_alternate_names' => 'Khakass Republic, Hakasija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KK',
        ]);

        State::create([
            'state_subdivision_id' => 17894,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Ingushskaya Respublika [Respublika Ingushetiya]',
            'state_subdivision_alternate_names' => 'Ingushetija, Ingušetija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-IN',
        ]);

        State::create([
            'state_subdivision_id' => 17895,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Irkutskaya oblast',
            'state_subdivision_alternate_names' => 'Irkutskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-IRK',

        ]);

        State::create([
            'state_subdivision_id' => 17896,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Yamalo-Nenetskiy avtonomnyy okrug',
            'state_subdivision_alternate_names' => 'Jamalija, Jamalo-Nenetskij Avtonomnyj Okrug, Jamalo-Nenets',
            'primary_level_name' => 'autonomous district',
            'state_subdivision_code' => 'RU-YAN',
        ]);

        State::create([
            'state_subdivision_id' => 17897,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Yaroslavskaya oblast',
            'state_subdivision_alternate_names' => 'Jaroslavskaja Oblast, Jaroslavl',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-YAR',

        ]);

        State::create([
            'state_subdivision_id' => 17898,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Yevreyskaya avtonomnaya oblast',
            'state_subdivision_alternate_names' => 'Jevrejskaja Oblast, Jevrejskaja Respublika, Jewish Autonomous Oblast, Yevreyskaya Oblast, Jevrej',
            'primary_level_name' => 'autonomous region',
            'state_subdivision_code' => 'RU-YEV',

        ]);

        State::create([
            'state_subdivision_id' => 17899,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kabardino-Balkarskaya Respublika',
            'state_subdivision_alternate_names' => 'Kabardino-Balkarian Republic, Kabardino-Balkarskaja Respublika, Kabardino-Balkarija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KB',
        ]);

        State::create([
            'state_subdivision_id' => 17900,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kaliningradskaya oblast',
            'state_subdivision_alternate_names' => 'Kaliningradskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KGD',

        ]);

        State::create([
            'state_subdivision_id' => 17901,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kalmykiya, Respublika',
            'state_subdivision_alternate_names' => 'Halmg-Tangč, Kalmyk Republic, Khalmg-Tangch, Kalmykija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KL',
        ]);

        State::create([
            'state_subdivision_id' => 17902,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kaluzhskaya oblast',
            'state_subdivision_alternate_names' => 'Kaluzhskaya Oblast, Kalužskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-KLU',

        ]);

        State::create([
            'state_subdivision_id' => 17903,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kamchatskaya oblast',
            'state_subdivision_alternate_names' => 'Kamchatskaya Oblast, Kamčatskaja Oblast, Kamčatka',
            'primary_level_name' => 'administrative territory',
            'state_subdivision_code' => 'RU-KAM',

        ]);

        State::create([
            'state_subdivision_id' => 17904,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Karachayevo-Cherkesskaya Respublika',
            'state_subdivision_alternate_names' => 'Karachay-Cherkessian, Karačajevo-Čerkesskaja Respublika, Karačaj-Čerkessija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KC',
        ]);

        State::create([
            'state_subdivision_id' => 17905,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Kareliya, Respublika',
            'state_subdivision_alternate_names' => 'Karelian Republic, Karelija',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-KR',
        ]);

        State::create([
            'state_subdivision_id' => 17906,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tomskaya oblast',
            'state_subdivision_alternate_names' => 'Tomskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-TOM',

        ]);

        State::create([
            'state_subdivision_id' => 17907,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Volgogradskaya oblast',
            'state_subdivision_alternate_names' => 'Volgogradskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-VGG',

        ]);

        State::create([
            'state_subdivision_id' => 17908,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Ul yanovskaya oblast',
            'state_subdivision_alternate_names' => 'Uljanovskaja Oblast, Uljanovsk',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-ULY',
        ]);

        State::create([
            'state_subdivision_id' => 17909,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tyva, Respublika [Tuva]',
            'state_subdivision_alternate_names' => 'Tuva',
            'primary_level_name' => 'republic',
            'state_subdivision_code' => 'RU-TY',
        ]);

        State::create([
            'state_subdivision_id' => 17910,
            'country_code_char2' => 'RU',
            'country_code_char3' => 'RUS',
            'state_subdivision_name' => 'Tverskaya oblast',
            'state_subdivision_alternate_names' => 'Tverskaja Oblast',
            'primary_level_name' => 'administrative region',
            'state_subdivision_code' => 'RU-TVE',

        ]);

        State::create([
            'state_subdivision_id' => 17912,
            'country_code_char2' => 'RW',
            'country_code_char3' => 'RWA',
            'state_subdivision_name' => 'Sud',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'RW-05',
        ]);

        State::create([
            'state_subdivision_id' => 17923,
            'country_code_char2' => 'RW',
            'country_code_char3' => 'RWA',
            'state_subdivision_name' => 'Ville de Kigali',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'RW-01',
        ]);

        State::create([
            'state_subdivision_id' => 17925,
            'country_code_char2' => 'SH',
            'country_code_char3' => 'SHN',
            'state_subdivision_name' => 'Tristan da Cunha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'dependency',
            'state_subdivision_code' => 'SH-TA',
        ]);

        State::create([
            'state_subdivision_id' => 17926,
            'country_code_char2' => 'SH',
            'country_code_char3' => 'SHN',
            'state_subdivision_name' => 'Ascension',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'dependency',
            'state_subdivision_code' => 'SH-AC',
        ]);

        State::create([
            'state_subdivision_id' => 17930,
            'country_code_char2' => 'SH',
            'country_code_char3' => 'SHN',
            'state_subdivision_name' => 'Saint Helena',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'administrative area',
            'state_subdivision_code' => 'SH-SH',
        ]);

        State::create([
            'state_subdivision_id' => 17931,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Trinity Palmetto Point',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-15',
        ]);

        State::create([
            'state_subdivision_id' => 17932,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint John Capisterre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-06',
        ]);

        State::create([
            'state_subdivision_id' => 17933,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Christ Church Nichola Town',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-01',
        ]);

        State::create([
            'state_subdivision_id' => 17934,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint George Basseterre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-03',
        ]);

        State::create([
            'state_subdivision_id' => 17935,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint James Windward',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-05',
        ]);

        State::create([
            'state_subdivision_id' => 17936,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint John Figtree',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-07',
        ]);

        State::create([
            'state_subdivision_id' => 17937,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Paul Capisterre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-09',
        ]);

        State::create([
            'state_subdivision_id' => 17938,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Peter Basseterre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-11',
        ]);

        State::create([
            'state_subdivision_id' => 17939,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Thomas Middle Island',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-13',
        ]);

        State::create([
            'state_subdivision_id' => 17940,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Anne Sandy Point',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-02',
        ]);

        State::create([
            'state_subdivision_id' => 17941,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint George Gingerland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-04',
        ]);

        State::create([
            'state_subdivision_id' => 17942,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Mary Cayon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-08',
        ]);

        State::create([
            'state_subdivision_id' => 17943,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Thomas Lowland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-12',
        ]);

        State::create([
            'state_subdivision_id' => 17944,
            'country_code_char2' => 'KN',
            'country_code_char3' => 'KNA',
            'state_subdivision_name' => 'Saint Paul Charlestown',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'KN-10',
        ]);

        State::create([
            'state_subdivision_id' => 17957,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Saint George',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-04',
        ]);

        State::create([
            'state_subdivision_id' => 17958,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Grenadines',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-06',
        ]);

        State::create([
            'state_subdivision_id' => 17959,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Saint David',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-03',
        ]);

        State::create([
            'state_subdivision_id' => 17960,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Saint Patrick',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-05',
        ]);

        State::create([
            'state_subdivision_id' => 17962,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Saint Andrew',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-02',
        ]);

        State::create([
            'state_subdivision_id' => 17963,
            'country_code_char2' => 'VC',
            'country_code_char3' => 'VCT',
            'state_subdivision_name' => 'Charlotte',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'VC-01',
        ]);

        State::create([
            'state_subdivision_id' => 17968,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Serravalle',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-09',
        ]);

        State::create([
            'state_subdivision_id' => 17969,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Fiorentino',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-05',
        ]);

        State::create([
            'state_subdivision_id' => 17970,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Domagnano',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-03',
        ]);

        State::create([
            'state_subdivision_id' => 17971,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Borgo Maggiore',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-06',
        ]);

        State::create([
            'state_subdivision_id' => 17972,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Acquaviva',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-01',
        ]);

        State::create([
            'state_subdivision_id' => 17973,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Chiesanuova',
            'state_subdivision_alternate_names' => 'Poggio di Chiesanuova',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-02',
        ]);

        State::create([
            'state_subdivision_id' => 17974,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Faetano',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-04',
        ]);

        State::create([
            'state_subdivision_id' => 17975,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'Montegiardino',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-08',
        ]);

        State::create([
            'state_subdivision_id' => 17976,
            'country_code_char2' => 'SM',
            'country_code_char3' => 'SMR',
            'state_subdivision_name' => 'San Marino',
            'state_subdivision_alternate_names' => 'Saint Marin',
            'primary_level_name' => 'Municipality',
            'state_subdivision_code' => 'SM-07',
        ]);

        State::create([
            'state_subdivision_id' => 17977,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Tabuk',
            'state_subdivision_alternate_names' => 'Tabook',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-07',
        ]);

        State::create([
            'state_subdivision_id' => 17978,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Ar Riya?',
            'state_subdivision_alternate_names' => 'ar-Riyad, ar-Riyād̨, Riad, Riyadh, Riad',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-01',
        ]);

        State::create([
            'state_subdivision_id' => 17979,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Al Madinah',
            'state_subdivision_alternate_names' => 'Medina, Médine, al-Madinah',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-03',
        ]);

        State::create([
            'state_subdivision_id' => 17980,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Al Ba?ah',
            'state_subdivision_alternate_names' => 'Baha',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-11',
        ]);

        State::create([
            'state_subdivision_id' => 17981,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => '?a il',
            'state_subdivision_alternate_names' => 'Hail',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-06',

        ]);

        State::create([
            'state_subdivision_id' => 17982,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Al Jawf',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-12',
        ]);

        State::create([
            'state_subdivision_id' => 17983,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Najran',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-10',
        ]);

        State::create([
            'state_subdivision_id' => 17984,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => '?Asir',
            'state_subdivision_alternate_names' => 'Aseer',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-14',
        ]);

        State::create([
            'state_subdivision_id' => 17985,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Al ?udud ash Shamaliyah',
            'state_subdivision_alternate_names' => 'Northern, al-Hudud ash-Shamaliyah',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-08',
        ]);

        State::create([
            'state_subdivision_id' => 17986,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Jizan',
            'state_subdivision_alternate_names' => 'Jizan',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-09',
        ]);

        State::create([
            'state_subdivision_id' => 17987,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Makkah',
            'state_subdivision_alternate_names' => 'La Meca',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-02',
        ]);

        State::create([
            'state_subdivision_id' => 17988,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Al Qasim',
            'state_subdivision_alternate_names' => 'Qaseem',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-05',
        ]);

        State::create([
            'state_subdivision_id' => 17989,
            'country_code_char2' => 'SA',
            'country_code_char3' => 'SAU',
            'state_subdivision_name' => 'Ash Sharqiyah',
            'state_subdivision_alternate_names' => 'Eastern, ash-Sharqiyah',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SA-04',
        ]);

        State::create([
            'state_subdivision_id' => 17990,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Ziguinchor',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-ZG',
        ]);

        State::create([
            'state_subdivision_id' => 17991,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Diourbel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-DB',
        ]);

        State::create([
            'state_subdivision_id' => 17992,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Saint-Louis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-SL',
        ]);

        State::create([
            'state_subdivision_id' => 17993,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Kolda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-KD',
        ]);

        State::create([
            'state_subdivision_id' => 17994,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Matam',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-MT',
        ]);

        State::create([
            'state_subdivision_id' => 17995,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Thiès',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-TH',
        ]);

        State::create([
            'state_subdivision_id' => 17996,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Louga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-LG',
        ]);

        State::create([
            'state_subdivision_id' => 17997,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Fatick',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-FK',
        ]);

        State::create([
            'state_subdivision_id' => 17998,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Dakar',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-DK',
        ]);

        State::create([
            'state_subdivision_id' => 17999,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Kaolack',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-KL',
        ]);

        State::create([
            'state_subdivision_id' => 18000,
            'country_code_char2' => 'SN',
            'country_code_char3' => 'SEN',
            'state_subdivision_name' => 'Tambacounda',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'SN-TC',
        ]);

        State::create([
            'state_subdivision_id' => 18004,
            'country_code_char2' => 'RS',
            'country_code_char3' => 'SRB',
            'state_subdivision_name' => 'Kosovski okrug',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'RS-25',
        ]);

        State::create([
            'state_subdivision_id' => 18005,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Takamaka',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-23',
        ]);

        State::create([
            'state_subdivision_id' => 18006,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Baie Lazare',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-06',
        ]);

        State::create([
            'state_subdivision_id' => 18007,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Grand Anse Praslin',
            'state_subdivision_alternate_names' => 'Grand Anse Praslin, Grand Anse (Praslin), Grand Ans Pralen',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-14',

        ]);

        State::create([
            'state_subdivision_id' => 18009,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Mont Buxton',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-17',
        ]);

        State::create([
            'state_subdivision_id' => 18011,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Pointe La Rue',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-20',
        ]);

        State::create([
            'state_subdivision_id' => 18012,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Port Glaud',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-21',
        ]);

        State::create([
            'state_subdivision_id' => 18013,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Saint Louis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-22',
        ]);

        State::create([
            'state_subdivision_id' => 18014,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Anse aux Pins',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-01',
        ]);

        State::create([
            'state_subdivision_id' => 18015,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Anse Royale',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-05',
        ]);

        State::create([
            'state_subdivision_id' => 18017,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Anse Boileau',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-02',
        ]);

        State::create([
            'state_subdivision_id' => 18018,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Anse Étoile',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-03',
        ]);

        State::create([
            'state_subdivision_id' => 18019,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Au Cap',
            'state_subdivision_alternate_names' => 'O Kap, Au Cap, Anse Louis',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-04',
        ]);

        State::create([
            'state_subdivision_id' => 18020,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Baie Sainte Anne',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-07',
        ]);

        State::create([
            'state_subdivision_id' => 18021,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Beau Vallon',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-08',
        ]);

        State::create([
            'state_subdivision_id' => 18022,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Bel Ombre',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-10',
        ]);

        State::create([
            'state_subdivision_id' => 18023,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'English River',
            'state_subdivision_alternate_names' => 'English River/ La Riviere Anglaise',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-16',
        ]);

        State::create([
            'state_subdivision_id' => 18024,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Glacis',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-12',
        ]);

        State::create([
            'state_subdivision_id' => 18025,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Bel Air',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-09',
        ]);

        State::create([
            'state_subdivision_id' => 18026,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Plaisance',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-19',
        ]);

        State::create([
            'state_subdivision_id' => 18027,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'La Digue',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-15',
        ]);

        State::create([
            'state_subdivision_id' => 18028,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Grand Anse Mahe Grand Anse Mahe, Grand',
            'state_subdivision_alternate_names' => 'Anse (Mahé), Grand Ans Mae',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-13',

        ]);

        State::create([
            'state_subdivision_id' => 18029,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Cascade',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-11',
        ]);

        State::create([
            'state_subdivision_id' => 18030,
            'country_code_char2' => 'SC',
            'country_code_char3' => 'SYC',
            'state_subdivision_name' => 'Mont Fleuri',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SC-18',
        ]);

        State::create([
            'state_subdivision_id' => 18033,
            'country_code_char2' => 'SL',
            'country_code_char3' => 'SLE',
            'state_subdivision_name' => 'Northern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SL-N',
        ]);

        State::create([
            'state_subdivision_id' => 18034,
            'country_code_char2' => 'SL',
            'country_code_char3' => 'SLE',
            'state_subdivision_name' => 'Western Area (Freetown)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'area',
            'state_subdivision_code' => 'SL-W',
        ]);

        State::create([
            'state_subdivision_id' => 18044,
            'country_code_char2' => 'SL',
            'country_code_char3' => 'SLE',
            'state_subdivision_name' => 'Eastern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SL-E',
        ]);

        State::create([
            'state_subdivision_id' => 18045,
            'country_code_char2' => 'SL',
            'country_code_char3' => 'SLE',
            'state_subdivision_name' => 'Southern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SL-S',
        ]);

        State::create([
            'state_subdivision_id' => 18052,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Trenciansky kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-TC',
        ]);

        State::create([
            'state_subdivision_id' => 18053,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Bratislavský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-BL',
        ]);

        State::create([
            'state_subdivision_id' => 18054,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Nitriansky kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-NI',
        ]);

        State::create([
            'state_subdivision_id' => 18055,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Prešovský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-PV',
        ]);

        State::create([
            'state_subdivision_id' => 18056,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Trnavský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-TA',
        ]);

        State::create([
            'state_subdivision_id' => 18057,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Žilinský kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-ZI',
        ]);

        State::create([
            'state_subdivision_id' => 18059,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Košický kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-KI',
        ]);

        State::create([
            'state_subdivision_id' => 18060,
            'country_code_char2' => 'SK',
            'country_code_char3' => 'SVK',
            'state_subdivision_name' => 'Banskobystrický kraj',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SK-BC',
        ]);

        State::create([
            'state_subdivision_id' => 18073,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Temotu',
            'state_subdivision_alternate_names' => 'Eastern Islands',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-TE',
        ]);

        State::create([
            'state_subdivision_id' => 18074,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Central',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-CE',
        ]);

        State::create([
            'state_subdivision_id' => 18075,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Choiseul',
            'state_subdivision_alternate_names' => 'Lauru',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-CH',
        ]);

        State::create([
            'state_subdivision_id' => 18076,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Guadalcanal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-GU',
        ]);

        State::create([
            'state_subdivision_id' => 18077,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Western',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-WE',
        ]);

        State::create([
            'state_subdivision_id' => 18078,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Rennell and Bellona',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-RB',
        ]);

        State::create([
            'state_subdivision_id' => 18079,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Capital Territory (Honiara)',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'capital territory',
            'state_subdivision_code' => 'SB-CT',
        ]);

        State::create([
            'state_subdivision_id' => 18080,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Makira',
            'state_subdivision_alternate_names' => 'San Cristóbal',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-MK',
        ]);

        State::create([
            'state_subdivision_id' => 18081,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Isabel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-IS',
        ]);

        State::create([
            'state_subdivision_id' => 18082,
            'country_code_char2' => 'SB',
            'country_code_char3' => 'SLB',
            'state_subdivision_name' => 'Malaita',
            'state_subdivision_alternate_names' => 'Mala',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'SB-ML',
        ]);

        State::create([
            'state_subdivision_id' => 18083,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Sool',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-SO',
        ]);

        State::create([
            'state_subdivision_id' => 18084,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Galguduud',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-GA',
        ]);

        State::create([
            'state_subdivision_id' => 18085,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Gedo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-GE',
        ]);

        State::create([
            'state_subdivision_id' => 18086,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Jubbada Dhexe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-JD',
        ]);

        State::create([
            'state_subdivision_id' => 18087,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Jubbada Hoose',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-JH',
        ]);

        State::create([
            'state_subdivision_id' => 18088,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Nugaal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-NU',
        ]);

        State::create([
            'state_subdivision_id' => 18089,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Shabeellaha Hoose',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-SH',
        ]);

        State::create([
            'state_subdivision_id' => 18090,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Sanaag',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-SA',
        ]);

        State::create([
            'state_subdivision_id' => 18091,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Togdheer',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-TO',
        ]);

        State::create([
            'state_subdivision_id' => 18092,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Woqooyi Galbeed',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-WO',
        ]);

        State::create([
            'state_subdivision_id' => 18093,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Awdal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-AW',
        ]);

        State::create([
            'state_subdivision_id' => 18094,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Bakool',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-BK',
        ]);

        State::create([
            'state_subdivision_id' => 18095,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Bari',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-BR',
        ]);

        State::create([
            'state_subdivision_id' => 18096,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Mudug',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-MU',
        ]);

        State::create([
            'state_subdivision_id' => 18097,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Bay',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-BY',
        ]);

        State::create([
            'state_subdivision_id' => 18098,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Banaadir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-BN',
        ]);

        State::create([
            'state_subdivision_id' => 18099,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Hiiraan',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-HI',
        ]);

        State::create([
            'state_subdivision_id' => 18100,
            'country_code_char2' => 'SO',
            'country_code_char3' => 'SOM',
            'state_subdivision_name' => 'Shabeellaha Dhexe',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Region',
            'state_subdivision_code' => 'SO-SD',
        ]);

        State::create([
            'state_subdivision_id' => 18101,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'North-West',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-NW',
        ]);

        State::create([
            'state_subdivision_id' => 18102,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Mpumalanga',
            'state_subdivision_alternate_names' => 'eMpumalanga',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-MP',
        ]);

        State::create([
            'state_subdivision_id' => 18103,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Eastern Cape',
            'state_subdivision_alternate_names' => 'Oos Kaap',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-EC',
        ]);

        State::create([
            'state_subdivision_id' => 18104,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Free State',
            'state_subdivision_alternate_names' => 'Foreistata, Vrystaat',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-FS',
        ]);

        State::create([
            'state_subdivision_id' => 18105,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Kwazulu-Natal',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-NL',
        ]);

        State::create([
            'state_subdivision_id' => 18106,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Limpopo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-LP',
        ]);

        State::create([
            'state_subdivision_id' => 18107,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Northern Cape',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-NC',
        ]);

        State::create([
            'state_subdivision_id' => 18108,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Western Cape',
            'state_subdivision_alternate_names' => 'Wes Kaap',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-WC',
        ]);

        State::create([
            'state_subdivision_id' => 18109,
            'country_code_char2' => 'ZA',
            'country_code_char3' => 'ZAF',
            'state_subdivision_name' => 'Gauteng',
            'state_subdivision_alternate_names' => 'Pretoria-Witwatersrand-Vereeniging',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'ZA-GT',
        ]);

        State::create([
            'state_subdivision_id' => 18110,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Zaragoza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-Z',
        ]);

        State::create([
            'state_subdivision_id' => 18111,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Valencia',
            'state_subdivision_alternate_names' => 'Valencia, València (ca)',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-V',
        ]);

        State::create([
            'state_subdivision_id' => 18112,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Tarragona',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-T',
        ]);

        State::create([
            'state_subdivision_id' => 18113,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Vizcaya',
            'state_subdivision_alternate_names' => 'Vizcaya,  Bizkaia',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-BI',
        ]);

        State::create([
            'state_subdivision_id' => 18114,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Zamora',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-ZA',
        ]);

        State::create([
            'state_subdivision_id' => 18115,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Segovia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-SG',
        ]);

        State::create([
            'state_subdivision_id' => 18116,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Ourense',
            'state_subdivision_alternate_names' => 'Orense',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-OR',
        ]);

        State::create([
            'state_subdivision_id' => 18117,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Soria',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-SO',
        ]);

        State::create([
            'state_subdivision_id' => 18118,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Teruel',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-TE',
        ]);

        State::create([
            'state_subdivision_id' => 18119,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Toledo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-TO',
        ]);

        State::create([
            'state_subdivision_id' => 18120,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Valladolid',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-VA',
        ]);

        State::create([
            'state_subdivision_id' => 18121,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Álava',
            'state_subdivision_alternate_names' => 'Araba',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-VI',
        ]);

        State::create([
            'state_subdivision_id' => 18122,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Asturias',
            'state_subdivision_alternate_names' => 'Oviedo',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-O',
        ]);

        State::create([
            'state_subdivision_id' => 18123,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Baleares',
            'state_subdivision_alternate_names' => 'Baleares, Illes Balears, Islas Baleares',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-PM',
        ]);

        State::create([
            'state_subdivision_id' => 18124,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Burgos',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-BU',
        ]);

        State::create([
            'state_subdivision_id' => 18125,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Cantabria',
            'state_subdivision_alternate_names' => 'Santander',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-S',
        ]);

        State::create([
            'state_subdivision_id' => 18126,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Ciudad Real',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CR',
        ]);

        State::create([
            'state_subdivision_id' => 18127,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Córdoba',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CO',
        ]);

        State::create([
            'state_subdivision_id' => 18128,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Girona',
            'state_subdivision_alternate_names' => 'Gerona',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-GI',
        ]);

        State::create([
            'state_subdivision_id' => 18129,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Granada',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-GR',
        ]);

        State::create([
            'state_subdivision_id' => 18130,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Guipúzcoa',
            'state_subdivision_alternate_names' => 'Gipuzkoa, Guipúzcoa',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-SS',
        ]);

        State::create([
            'state_subdivision_id' => 18131,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Huesca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-HU',
        ]);

        State::create([
            'state_subdivision_id' => 18132,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Jaén',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-J',
        ]);

        State::create([
            'state_subdivision_id' => 18133,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Las Palmas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-GC',
        ]);

        State::create([
            'state_subdivision_id' => 18134,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Lleida',
            'state_subdivision_alternate_names' => 'Lérida',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-L',
        ]);

        State::create([
            'state_subdivision_id' => 18135,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'A Coruña',
            'state_subdivision_alternate_names' => 'Corunna, La Coruña',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-C',
        ]);

        State::create([
            'state_subdivision_id' => 18136,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Alicante',
            'state_subdivision_alternate_names' => 'Alicante, Alacant',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-A',
        ]);

        State::create([
            'state_subdivision_id' => 18137,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Albacete',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-AB',
        ]);

        State::create([
            'state_subdivision_id' => 18138,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Almería',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-AL',
        ]);

        State::create([
            'state_subdivision_id' => 18139,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Ávila',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-AV',
        ]);

        State::create([
            'state_subdivision_id' => 18140,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Badajoz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-BA',
        ]);

        State::create([
            'state_subdivision_id' => 18141,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Barcelona',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-B',
        ]);

        State::create([
            'state_subdivision_id' => 18142,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Cáceres',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CC',
        ]);

        State::create([
            'state_subdivision_id' => 18143,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Cádiz',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CA',
        ]);

        State::create([
            'state_subdivision_id' => 18144,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Castellón',
            'state_subdivision_alternate_names' => 'Castellón',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CS',
        ]);

        State::create([
            'state_subdivision_id' => 18145,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Lugo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-LU',
        ]);

        State::create([
            'state_subdivision_id' => 18146,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Málaga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-MA',
        ]);

        State::create([
            'state_subdivision_id' => 18148,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Navarra',
            'state_subdivision_alternate_names' => 'Navarre, Navarra, Nafarroa',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-NA',
        ]);

        State::create([
            'state_subdivision_id' => 18149,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Palencia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-P',
        ]);

        State::create([
            'state_subdivision_id' => 18150,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Pontevedra',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-PO',
        ]);

        State::create([
            'state_subdivision_id' => 18151,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Santa Cruz de Tenerife',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-TF',
        ]);

        State::create([
            'state_subdivision_id' => 18152,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Sevilla',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-SE',
        ]);

        State::create([
            'state_subdivision_id' => 18154,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Cuenca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-CU',
        ]);

        State::create([
            'state_subdivision_id' => 18155,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Guadalajara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-GU',
        ]);

        State::create([
            'state_subdivision_id' => 18156,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Huelva',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-H',
        ]);

        State::create([
            'state_subdivision_id' => 18157,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'La Rioja',
            'state_subdivision_alternate_names' => 'Logroño',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-LO',
        ]);

        State::create([
            'state_subdivision_id' => 18158,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'León',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-LE',
        ]);

        State::create([
            'state_subdivision_id' => 18159,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Madrid',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-M',
        ]);

        State::create([
            'state_subdivision_id' => 18160,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Murcia',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-MU',
        ]);

        State::create([
            'state_subdivision_id' => 18161,
            'country_code_char2' => 'ES',
            'country_code_char3' => 'ESP',
            'state_subdivision_name' => 'Salamanca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'ES-SA',
        ]);

        State::create([
            'state_subdivision_id' => 18162,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Vavuniyāva',
            'state_subdivision_alternate_names' => 'Vavuniyāwa',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-44',
        ]);

        State::create([
            'state_subdivision_id' => 18163,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mulativ',
            'state_subdivision_alternate_names' => 'Mulativu',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-45',
        ]);

        State::create([
            'state_subdivision_id' => 18164,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mātara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-32',
        ]);

        State::create([
            'state_subdivision_id' => 18165,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Pŏḷŏnnaruva',
            'state_subdivision_alternate_names' => 'Polonaruwa, Polunnaruwa',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-72',
        ]);

        State::create([
            'state_subdivision_id' => 18166,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Ratnapura',
            'state_subdivision_alternate_names' => 'Ratnapūraya',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-91',
        ]);

        State::create([
            'state_subdivision_id' => 18167,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mahanuvara',
            'state_subdivision_alternate_names' => 'Mahe Nuwara, Maha Nuwara',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-21',
        ]);

        State::create([
            'state_subdivision_id' => 18168,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Kilinŏchchi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-42',
        ]);

        State::create([
            'state_subdivision_id' => 18169,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mātale',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-22',
        ]);

        State::create([
            'state_subdivision_id' => 18170,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mŏṇarāgala',
            'state_subdivision_alternate_names' => 'Monaragalla, Moneragala',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-82',
        ]);

        State::create([
            'state_subdivision_id' => 18171,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Nuvara Ĕliya',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-23',
        ]);

        State::create([
            'state_subdivision_id' => 18172,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Puttalama',
            'state_subdivision_alternate_names' => 'Puttalam',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-62',
        ]);

        State::create([
            'state_subdivision_id' => 18173,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Trikuṇāmalaya',
            'state_subdivision_alternate_names' => 'Trinkomali, Tirikunāmalaya',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-53',
        ]);

        State::create([
            'state_subdivision_id' => 18174,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Yāpanaya',
            'state_subdivision_alternate_names' => 'Yapnaya, Yāpanaya',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-41',
        ]);

        State::create([
            'state_subdivision_id' => 18175,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Anurādhapura',
            'state_subdivision_alternate_names' => 'Anuradhapur, Anurādhapūraya',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-71',
        ]);

        State::create([
            'state_subdivision_id' => 18176,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Gampaha',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-12',
        ]);

        State::create([
            'state_subdivision_id' => 18177,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Kalutara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-13',
        ]);

        State::create([
            'state_subdivision_id' => 18178,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Ampāra',
            'state_subdivision_alternate_names' => 'Ampara',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-52',
        ]);

        State::create([
            'state_subdivision_id' => 18179,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Badulla',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-81',
        ]);

        State::create([
            'state_subdivision_id' => 18180,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Gālla',
            'state_subdivision_alternate_names' => 'Gālla',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-31',
        ]);

        State::create([
            'state_subdivision_id' => 18181,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Hambantŏṭa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-33',
        ]);

        State::create([
            'state_subdivision_id' => 18182,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Kegalla',
            'state_subdivision_alternate_names' => 'Kegalle',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-92',
        ]);

        State::create([
            'state_subdivision_id' => 18183,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Kŏḷamba',
            'state_subdivision_alternate_names' => 'Kolamba, Colombo, Colombo',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-11',
        ]);

        State::create([
            'state_subdivision_id' => 18184,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Madakalapuva',
            'state_subdivision_alternate_names' => 'Mad̨akalpūwa',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-51',
        ]);

        State::create([
            'state_subdivision_id' => 18185,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Mannārama',
            'state_subdivision_alternate_names' => 'Manar, Mannar, Mannerama',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-43',
        ]);

        State::create([
            'state_subdivision_id' => 18186,
            'country_code_char2' => 'LK',
            'country_code_char3' => 'LKA',
            'state_subdivision_name' => 'Kuruṇægala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'district',
            'state_subdivision_code' => 'LK-61',
        ]);

        State::create([
            'state_subdivision_id' => 18187,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Wa?dah',
            'state_subdivision_alternate_names' => 'Ittihād, Unity',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-22',
        ]);

        State::create([
            'state_subdivision_id' => 18188,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Shamal Ba?r al Ghazal',
            'state_subdivision_alternate_names' => 'North Bahr al Ghazal',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-15',
        ]);

        State::create([
            'state_subdivision_id' => 18189,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Qa?arif',
            'state_subdivision_alternate_names' => 'Gadaref, Gedaref, Qadārif, al-Qadarif',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-06',
        ]);

        State::create([
            'state_subdivision_id' => 18190,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Ash Shamaliyah',
            'state_subdivision_alternate_names' => 'Northern, Shamaliyah',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-01',
        ]);

        State::create([
            'state_subdivision_id' => 18191,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Sharq al Istiwa iyah',
            'state_subdivision_alternate_names' => 'East Equatoria, Sharq al Istiwaiyya, Šarq-al-Istiwaiyyah',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-19',

        ]);

        State::create([
            'state_subdivision_id' => 18192,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'An Nil al Abya?',
            'state_subdivision_alternate_names' => 'White Nile',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-08',
        ]);

        State::create([
            'state_subdivision_id' => 18193,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Jazirah',
            'state_subdivision_alternate_names' => 'Al Jazira, Gezira',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-07',
        ]);

        State::create([
            'state_subdivision_id' => 18194,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'A?ali an Nil',
            'state_subdivision_alternate_names' => 'Upper Nile, ʿAli an Nil',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-23',
        ]);

        State::create([
            'state_subdivision_id' => 18195,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Ba?r al Jabal',
            'state_subdivision_alternate_names' => 'Bahr al Jebel',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-17',
        ]);

        State::create([
            'state_subdivision_id' => 18196,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Gharb Ba?r al Ghazal',
            'state_subdivision_alternate_names' => 'West Bahr al Ghazal',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-14',
        ]);

        State::create([
            'state_subdivision_id' => 18197,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Khartum',
            'state_subdivision_alternate_names' => 'al-Khartum, al-H̱arţūm, Khartum, Khartoum, Jartum',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-03',
        ]);

        State::create([
            'state_subdivision_id' => 18198,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Janub Kurdufan',
            'state_subdivision_alternate_names' => 'South Kordofan',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-13',
        ]);

        State::create([
            'state_subdivision_id' => 18199,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Junqali',
            'state_subdivision_alternate_names' => 'Jonglei, Jongli',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-20',
        ]);

        State::create([
            'state_subdivision_id' => 18200,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'An Nil',
            'state_subdivision_alternate_names' => 'River Nile',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-04',
        ]);

        State::create([
            'state_subdivision_id' => 18201,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'An Nil al Azraq',
            'state_subdivision_alternate_names' => 'Blue Nile',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-24',
        ]);

        State::create([
            'state_subdivision_id' => 18202,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Shamal Darfur',
            'state_subdivision_alternate_names' => 'North Darfur, Shimal Darfur',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-02',
        ]);

        State::create([
            'state_subdivision_id' => 18203,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Shamal Kurdufan',
            'state_subdivision_alternate_names' => 'North Kordofan, Shimal Kurdufan',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-09',
        ]);

        State::create([
            'state_subdivision_id' => 18204,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Sinnar',
            'state_subdivision_alternate_names' => 'Sannar, Sennar',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-25',
        ]);

        State::create([
            'state_subdivision_id' => 18205,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Warab',
            'state_subdivision_alternate_names' => 'Warap',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-21',
        ]);

        State::create([
            'state_subdivision_id' => 18206,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Ba?r al A?mar',
            'state_subdivision_alternate_names' => 'Red Sea',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-26',
        ]);

        State::create([
            'state_subdivision_id' => 18207,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Al Bu?ayrat',
            'state_subdivision_alternate_names' => 'Bihār, Lakes',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-18',
        ]);

        State::create([
            'state_subdivision_id' => 18208,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Gharb al Istiwa iyah',
            'state_subdivision_alternate_names' => 'Garb-al-Istiwaiyyah, Gharb al Istiwaiyya, West Equatoria',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-16',

        ]);

        State::create([
            'state_subdivision_id' => 18209,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Gharb Darfur',
            'state_subdivision_alternate_names' => 'West Darfur',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-12',
        ]);

        State::create([
            'state_subdivision_id' => 18210,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Gharb Kurdufan',
            'state_subdivision_alternate_names' => 'Gharb al Kurdufan, West Kordofan',
            'primary_level_name' => '',
            'state_subdivision_code' => 'SD-10',
        ]);

        State::create([
            'state_subdivision_id' => 18211,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Janub Darfur',
            'state_subdivision_alternate_names' => 'South Darfur',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-11',
        ]);

        State::create([
            'state_subdivision_id' => 18212,
            'country_code_char2' => 'SD',
            'country_code_char3' => 'SDN',
            'state_subdivision_name' => 'Kassala',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'State',
            'state_subdivision_code' => 'SD-05',
        ]);

        State::create([
            'state_subdivision_id' => 18213,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Sipaliwini',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-SI',
        ]);

        State::create([
            'state_subdivision_id' => 18214,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Commewijne',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-CM',
        ]);

        State::create([
            'state_subdivision_id' => 18215,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Coronie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-CR',
        ]);

        State::create([
            'state_subdivision_id' => 18216,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Nickerie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-NI',
        ]);

        State::create([
            'state_subdivision_id' => 18217,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Paramaribo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-PM',
        ]);

        State::create([
            'state_subdivision_id' => 18218,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Saramacca',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-SA',
        ]);

        State::create([
            'state_subdivision_id' => 18219,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Wanica',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-WA',
        ]);

        State::create([
            'state_subdivision_id' => 18220,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Brokopondo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-BR',
        ]);

        State::create([
            'state_subdivision_id' => 18221,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Marowijne',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-MA',
        ]);

        State::create([
            'state_subdivision_id' => 18222,
            'country_code_char2' => 'SR',
            'country_code_char3' => 'SUR',
            'state_subdivision_name' => 'Para',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SR-PR',
        ]);

        State::create([
            'state_subdivision_id' => 18225,
            'country_code_char2' => 'SZ',
            'country_code_char3' => 'SWZ',
            'state_subdivision_name' => 'Manzini',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SZ-MA',
        ]);

        State::create([
            'state_subdivision_id' => 18226,
            'country_code_char2' => 'SZ',
            'country_code_char3' => 'SWZ',
            'state_subdivision_name' => 'Hhohho',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SZ-HH',
        ]);

        State::create([
            'state_subdivision_id' => 18227,
            'country_code_char2' => 'SZ',
            'country_code_char3' => 'SWZ',
            'state_subdivision_name' => 'Shiselweni',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SZ-SH',
        ]);

        State::create([
            'state_subdivision_id' => 18228,
            'country_code_char2' => 'SZ',
            'country_code_char3' => 'SWZ',
            'state_subdivision_name' => 'Lubombo',
            'state_subdivision_alternate_names' => 'Lebombo',
            'primary_level_name' => 'District',
            'state_subdivision_code' => 'SZ-LU',
        ]);

        State::create([
            'state_subdivision_id' => 18230,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Östergötlands län [SE-05]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-E',
        ]);

        State::create([
            'state_subdivision_id' => 18231,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Södermanlands län [SE-04]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-D',
        ]);

        State::create([
            'state_subdivision_id' => 18232,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Uppsala län [SE-03]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-C',
        ]);

        State::create([
            'state_subdivision_id' => 18233,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Värmlands län [SE-17]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-S',
        ]);

        State::create([
            'state_subdivision_id' => 18234,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Västernorrlands län [SE-22]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-Y',
        ]);

        State::create([
            'state_subdivision_id' => 18235,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Västra Götalands län [SE-14]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-O',
        ]);

        State::create([
            'state_subdivision_id' => 18240,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Blekinge län [SE-10]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-K',
        ]);

        State::create([
            'state_subdivision_id' => 18241,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Gävleborgs län [SE-21]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-X',
        ]);

        State::create([
            'state_subdivision_id' => 18242,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Hallands län [SE-13]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-N',
        ]);

        State::create([
            'state_subdivision_id' => 18243,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Jämtlands län [SE-23]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-Z',
        ]);

        State::create([
            'state_subdivision_id' => 18244,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Kalmar län [SE-08]',
            'state_subdivision_alternate_names' => 'Calmar',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-H',
        ]);

        State::create([
            'state_subdivision_id' => 18245,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Kronobergs län [SE-07]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-G',
        ]);

        State::create([
            'state_subdivision_id' => 18246,
            'country_code_char2' => 'SE',
            'country_code_char3' => 'SWE',
            'state_subdivision_name' => 'Örebro län [SE-18]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'County',
            'state_subdivision_code' => 'SE-T',
        ]);

        State::create([
            'state_subdivision_id' => 14911,
            'country_code_char2' => 'JM',
            'country_code_char3' => 'JAM',
            'state_subdivision_name' => 'Saint Mary',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'JM-05',
        ]);

        State::create([
            'state_subdivision_id' => 14912,
            'country_code_char2' => 'JM',
            'country_code_char3' => 'JAM',
            'state_subdivision_name' => 'Trelawny',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'JM-07',
        ]);

        State::create([
            'state_subdivision_id' => 14913,
            'country_code_char2' => 'JM',
            'country_code_char3' => 'JAM',
            'state_subdivision_name' => 'Westmoreland',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'JM-10',
        ]);

        State::create([
            'state_subdivision_id' => 14914,
            'country_code_char2' => 'JM',
            'country_code_char3' => 'JAM',
            'state_subdivision_name' => 'Manchester',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'JM-12',
        ]);

        State::create([
            'state_subdivision_id' => 14915,
            'country_code_char2' => 'JM',
            'country_code_char3' => 'JAM',
            'state_subdivision_name' => 'Hanover',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Parish',
            'state_subdivision_code' => 'JM-09',
        ]);

        State::create([
            'state_subdivision_id' => 14916,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Yamagata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-06',
        ]);

        State::create([
            'state_subdivision_id' => 14917,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Tottori',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-31',
        ]);

        State::create([
            'state_subdivision_id' => 14918,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Totigi [Tochigi]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-09',
        ]);

        State::create([
            'state_subdivision_id' => 14919,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Siga [Shiga]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-25',
        ]);

        State::create([
            'state_subdivision_id' => 14920,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Ôita [Oita]',
            'state_subdivision_alternate_names' => 'Oita',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-44',
        ]);

        State::create([
            'state_subdivision_id' => 14921,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Miyagi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-04',
        ]);

        State::create([
            'state_subdivision_id' => 14922,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Saitama',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-11',
        ]);

        State::create([
            'state_subdivision_id' => 14923,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Simane [Shimane]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-32',
        ]);

        State::create([
            'state_subdivision_id' => 14924,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Sizuoka [Shizuoka]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-22',
        ]);

        State::create([
            'state_subdivision_id' => 14925,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Tokusima [Tokushima]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-36',
        ]);

        State::create([
            'state_subdivision_id' => 14926,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Tôkyô [Tokyo]',
            'state_subdivision_alternate_names' => 'Tokyo, Tokio',
            'primary_level_name' => 'Metropolis',
            'state_subdivision_code' => 'JP-13',
        ]);

        State::create([
            'state_subdivision_id' => 14927,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Toyama',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-16',
        ]);

        State::create([
            'state_subdivision_id' => 14928,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Wakayama',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-30',
        ]);

        State::create([
            'state_subdivision_id' => 14929,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Yamaguti [Yamaguchi]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-35',
        ]);

        State::create([
            'state_subdivision_id' => 14930,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Yamanasi [Yamanashi]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-19',
        ]);

        State::create([
            'state_subdivision_id' => 14931,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Aiti [Aichi]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-23',
        ]);

        State::create([
            'state_subdivision_id' => 14932,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Aomori',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-02',
        ]);

        State::create([
            'state_subdivision_id' => 14933,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Tiba [Chiba]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-12',
        ]);

        State::create([
            'state_subdivision_id' => 14934,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hukui [Fukui]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-18',
        ]);

        State::create([
            'state_subdivision_id' => 14935,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hukuoka [Fukuoka]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-40',
        ]);

        State::create([
            'state_subdivision_id' => 14936,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Gihu [Gifu]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-21',
        ]);

        State::create([
            'state_subdivision_id' => 14937,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Gunma',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-10',
        ]);

        State::create([
            'state_subdivision_id' => 14938,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hirosima [Hiroshima]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-34',
        ]);

        State::create([
            'state_subdivision_id' => 14939,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hyôgo [Hyogo]',
            'state_subdivision_alternate_names' => 'Hyogo',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-28',
        ]);

        State::create([
            'state_subdivision_id' => 14940,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Isikawa [Ishikawa]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-17',
        ]);

        State::create([
            'state_subdivision_id' => 14941,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Iwate',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-03',
        ]);

        State::create([
            'state_subdivision_id' => 14942,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kagawa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-37',
        ]);

        State::create([
            'state_subdivision_id' => 14943,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kanagawa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-14',
        ]);

        State::create([
            'state_subdivision_id' => 14944,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kôti [Kochi]',
            'state_subdivision_alternate_names' => 'Kochi',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-39',
        ]);

        State::create([
            'state_subdivision_id' => 14945,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kyôto [Kyoto]',
            'state_subdivision_alternate_names' => 'Kyoto',
            'primary_level_name' => 'Urban Perfecture',
            'state_subdivision_code' => 'JP-26',
        ]);

        State::create([
            'state_subdivision_id' => 14946,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Mie',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-24',
        ]);

        State::create([
            'state_subdivision_id' => 14947,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Miyazaki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-45',
        ]);

        State::create([
            'state_subdivision_id' => 14948,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Nagano',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-20',
        ]);

        State::create([
            'state_subdivision_id' => 14949,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Nara',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-29',
        ]);

        State::create([
            'state_subdivision_id' => 14950,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Niigata',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-15',
        ]);

        State::create([
            'state_subdivision_id' => 14951,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Okayama',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-33',
        ]);

        State::create([
            'state_subdivision_id' => 14952,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Okinawa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-47',
        ]);

        State::create([
            'state_subdivision_id' => 14953,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Saga',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-41',
        ]);

        State::create([
            'state_subdivision_id' => 14954,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Akita',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-05',
        ]);

        State::create([
            'state_subdivision_id' => 14955,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Ehime',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-38',
        ]);

        State::create([
            'state_subdivision_id' => 14956,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hukusima [Fukushima]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-07',
        ]);

        State::create([
            'state_subdivision_id' => 14957,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Hokkaidô [Hokkaido]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Territory',
            'state_subdivision_code' => 'JP-01',
        ]);

        State::create([
            'state_subdivision_id' => 14958,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Ibaraki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-08',
        ]);

        State::create([
            'state_subdivision_id' => 14959,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kagosima [Kagoshima]',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-46',
        ]);

        State::create([
            'state_subdivision_id' => 14960,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Kumamoto',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-43',
        ]);

        State::create([
            'state_subdivision_id' => 14961,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Nagasaki',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Perfecture',
            'state_subdivision_code' => 'JP-42',
        ]);

        State::create([
            'state_subdivision_id' => 14962,
            'country_code_char2' => 'JP',
            'country_code_char3' => 'JPN',
            'state_subdivision_name' => 'Ôsaka [Osaka]',
            'state_subdivision_alternate_names' => 'Osaka',
            'primary_level_name' => 'Urban Perfecture',
            'state_subdivision_code' => 'JP-27',
        ]);

        State::create([
            'state_subdivision_id' => 14975,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'At Tafilah',
            'state_subdivision_alternate_names' => 'Tafiela, Tafileh, at-Tafilah',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-AT',
        ]);

        State::create([
            'state_subdivision_id' => 14976,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Az Zarqa',
            'state_subdivision_alternate_names' => 'Zarka, Zarqa, Zerka',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-AZ',
        ]);

        State::create([
            'state_subdivision_id' => 14977,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Ma`an',
            'state_subdivision_alternate_names' => 'Maan, Maʿan',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-MN',
        ]);

        State::create([
            'state_subdivision_id' => 14978,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => '?Amman',
            'state_subdivision_alternate_names' => 'Amman',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-AM',
        ]);

        State::create([
            'state_subdivision_id' => 14979,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Al Balqa',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-BA',
        ]);

        State::create([
            'state_subdivision_id' => 14980,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Jarash',
            'state_subdivision_alternate_names' => 'Jarash, Jerash',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-JA',
        ]);

        State::create([
            'state_subdivision_id' => 14981,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Al Mafraq',
            'state_subdivision_alternate_names' => 'Mafraq',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-MA',
        ]);

        State::create([
            'state_subdivision_id' => 14982,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => '?Ajlun',
            'state_subdivision_alternate_names' => 'Ajloan, Ajlun',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-AJ',
        ]);

        State::create([
            'state_subdivision_id' => 14983,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Madaba',
            'state_subdivision_alternate_names' => 'Madaba, Maʿdaba, Maʿdabâ',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-MD',
        ]);

        State::create([
            'state_subdivision_id' => 14984,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Al Karak',
            'state_subdivision_alternate_names' => 'Karak, Kerak',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-KA',
        ]);

        State::create([
            'state_subdivision_id' => 14985,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Aqaba',
            'state_subdivision_alternate_names' => 'Akaba, Aqaba, Aqqaba, al-Aqaba',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-AQ',
        ]);

        State::create([
            'state_subdivision_id' => 14986,
            'country_code_char2' => 'JO',
            'country_code_char3' => 'JOR',
            'state_subdivision_name' => 'Irbid',
            'state_subdivision_alternate_names' => 'Irbit',
            'primary_level_name' => 'Province',
            'state_subdivision_code' => 'JO-IR',
        ]);

        State::create([
            'state_subdivision_id' => 14987,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Zhambyl oblysy',
            'state_subdivision_alternate_names' => 'Aulie-Ata, Auliye-Ata, Cambil, Cambıl, Djambul, Dzhambul, Zhambul, Zhambyl, Zhambül',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-ZHA',
        ]);

        State::create([
            'state_subdivision_id' => 14988,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Batys Qazaqstan oblysy',
            'state_subdivision_alternate_names' => 'Batis Kazakstan, Batys Qazaqstan, Uralsk, Uralskaya Oblast, Western Kazakhstan',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-ZAP',
        ]);

        State::create([
            'state_subdivision_id' => 14989,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Soltüstik Qazaqstan oblysy',
            'state_subdivision_alternate_names' => 'Northern Kazakhstan, Soltüstik Kazakstan, Soltüstik Qazaqstan',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-SEV',
        ]);

        State::create([
            'state_subdivision_id' => 14990,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Qyzylorda oblysy',
            'state_subdivision_alternate_names' => 'Ak-Mechet, Kizilorda, Kyzyl-Orda, Kyzylorda, Kzyl-Orda, Qyzylorda, Ķızılorda',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-KZY',
        ]);

        State::create([
            'state_subdivision_id' => 14991,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Aqmola oblysy',
            'state_subdivision_alternate_names' => 'Akmola, Akmolinsk, Aķmola, Celinograd, Tselinograd',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-AKM',
        ]);

        State::create([
            'state_subdivision_id' => 14992,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Aqtöbe oblysy',
            'state_subdivision_alternate_names' => 'Aktjubinsk, Aktyubinsk, Aktöbe, Aktʿubinsk',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-AKT',
        ]);

        State::create([
            'state_subdivision_id' => 14993,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Almaty',
            'state_subdivision_alternate_names' => 'Almati Oblasti, Almaty Oblasty, Almatı',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'KZ-ALA',
        ]);

        State::create([
            'state_subdivision_id' => 14994,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Atyrau oblysy',
            'state_subdivision_alternate_names' => 'Ateransk, Aterau, Atirau, Atırau, Gurjev, Guryev',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-ATY',
        ]);

        State::create([
            'state_subdivision_id' => 14995,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Mangghystau oblysy',
            'state_subdivision_alternate_names' => 'Mangghystau, Mangistau, Mangkistau, Mangqystau',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-MAN',
        ]);

        State::create([
            'state_subdivision_id' => 14996,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Shyghys Qazaqstan oblysy',
            'state_subdivision_alternate_names' => 'Eastern Kazakhstan, Shyghys Qazaqstan, Sigis Kazakstan',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-VOS',
        ]);

        State::create([
            'state_subdivision_id' => 14997,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Qaraghandy oblysy',
            'state_subdivision_alternate_names' => 'Karaganda, Karagandi, Karagandy, Qaraghandy, Ķaragandı',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-KAR',
        ]);

        State::create([
            'state_subdivision_id' => 14998,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Pavlodar oblysy',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-PAV',
        ]);

        State::create([
            'state_subdivision_id' => 14999,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Ongtüstik Qazaqstan oblysy',
            'state_subdivision_alternate_names' => 'Ongtüstik Qazaqstan, Ongtüstük Kazakstan, Southern Kazakhstan',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-YUZ',
        ]);

        State::create([
            'state_subdivision_id' => 15000,
            'country_code_char2' => 'KZ',
            'country_code_char3' => 'KAZ',
            'state_subdivision_name' => 'Qostanay oblysy',
            'state_subdivision_alternate_names' => 'Kostanay, Kustanai, Kustanaj, Kustanay, Ķostanay',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KZ-KUS',
        ]);

        State::create([
            'state_subdivision_id' => 15001,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Nyanza',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-600',
        ]);

        State::create([
            'state_subdivision_id' => 15003,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Western',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-900',
        ]);

        State::create([
            'state_subdivision_id' => 15004,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'North-Eastern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-500',
        ]);

        State::create([
            'state_subdivision_id' => 15005,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Coast',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-300',
        ]);

        State::create([
            'state_subdivision_id' => 15006,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Eastern',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-400',
        ]);

        State::create([
            'state_subdivision_id' => 15007,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Rift Valley',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-700',
        ]);

        State::create([
            'state_subdivision_id' => 15009,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Central',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-200',
        ]);

        State::create([
            'state_subdivision_id' => 15010,
            'country_code_char2' => 'KE',
            'country_code_char3' => 'KEN',
            'state_subdivision_name' => 'Nairobi',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KE-110',
        ]);

        State::create([
            'state_subdivision_id' => 15018,
            'country_code_char2' => 'KI',
            'country_code_char3' => 'KIR',
            'state_subdivision_name' => 'Line Islands',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Island group',
            'state_subdivision_code' => 'KI-L',
        ]);

        State::create([
            'state_subdivision_id' => 15033,
            'country_code_char2' => 'KI',
            'country_code_char3' => 'KIR',
            'state_subdivision_name' => 'Phoenix Islands',
            'state_subdivision_alternate_names' => 'Abariringa, Canton, Gardner, Hull, Kanton, Kemins, Manra, Mary Island, Nikumaroro, Orona, Phoenix, Rawaki, Sydney',
            'primary_level_name' => 'Island group',
            'state_subdivision_code' => 'KI-P',
        ]);

        State::create([
            'state_subdivision_id' => 15042,
            'country_code_char2' => 'KR',
            'country_code_char3' => 'KOR',
            'state_subdivision_name' => 'Gang weondo [Kang-won-do]',
            'state_subdivision_alternate_names' => 'Kangwon',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KR-42',
        ]);

        State::create([
            'state_subdivision_id' => 15053,
            'country_code_char2' => 'KP',
            'country_code_char3' => 'PRK',
            'state_subdivision_name' => 'Kangwon-do',
            'state_subdivision_alternate_names' => 'Gangweon, Kangweon, Kangwon',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'KP-07',
        ]);

        State::create([
            'state_subdivision_id' => 15063,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Al Jahrah',
            'state_subdivision_alternate_names' => 'Al Jahra, Jahra, Jahrah, al-Jahra, al-Jahraʿ',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-JA',
        ]);

        State::create([
            'state_subdivision_id' => 15064,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Al Ahmadi',
            'state_subdivision_alternate_names' => 'Ahmadi',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-AH',
        ]);

        State::create([
            'state_subdivision_id' => 15065,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Mubarak al-Kabir',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-MU',
        ]);

        State::create([
            'state_subdivision_id' => 15066,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Al Kuwayt',
            'state_subdivision_alternate_names' => 'Capital, Koweit, Kuwait, Kuwayt, al-Kuwayt',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-KU',
        ]);

        State::create([
            'state_subdivision_id' => 15067,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Hawalli',
            'state_subdivision_alternate_names' => 'Hawalli',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-HA',
        ]);

        State::create([
            'state_subdivision_id' => 15068,
            'country_code_char2' => 'KW',
            'country_code_char3' => 'KWT',
            'state_subdivision_name' => 'Al Farwaniyah',
            'state_subdivision_alternate_names' => 'Farwaniyah',
            'primary_level_name' => 'Governorate',
            'state_subdivision_code' => 'KW-FA',
        ]);

        State::create([
            'state_subdivision_id' => 15069,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Osh',
            'state_subdivision_alternate_names' => 'Osh',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-O',
        ]);

        State::create([
            'state_subdivision_id' => 15070,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Batken',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-B',
        ]);

        State::create([
            'state_subdivision_id' => 15071,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Naryn',
            'state_subdivision_alternate_names' => 'Naryn',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-N',
        ]);

        State::create([
            'state_subdivision_id' => 15072,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Bishkek',
            'state_subdivision_alternate_names' => 'Bishkek, Kenesh Bishkek',
            'primary_level_name' => 'city',
            'state_subdivision_code' => 'KG-GB',
        ]);

        State::create([
            'state_subdivision_id' => 15074,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Ysyk-Köl',
            'state_subdivision_alternate_names' => 'Balikchi, Balykchy, Balıkçı, Issyk-Kul',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-Y',
        ]);

        State::create([
            'state_subdivision_id' => 15075,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Talas',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-T',
        ]);

        State::create([
            'state_subdivision_id' => 15077,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Jalal-Abad',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-J',
        ]);

        State::create([
            'state_subdivision_id' => 15078,
            'country_code_char2' => 'KG',
            'country_code_char3' => 'KGZ',
            'state_subdivision_name' => 'Chü',
            'state_subdivision_alternate_names' => 'Chu, Chui, Chuy, Çüy',
            'primary_level_name' => 'region',
            'state_subdivision_code' => 'KG-C',
        ]);

        State::create([
            'state_subdivision_id' => 15079,
            'country_code_char2' => 'LA',
            'country_code_char3' => 'LAO',
            'state_subdivision_name' => 'Xiangkhoang [Xieng Khouang]',
            'state_subdivision_alternate_names' => 'Xiang Khouang, Xiangkhouang, Xiangkhuang, Xieng Khouang',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'LA-XI',
        ]);

        State::create([
            'state_subdivision_id' => 15080,
            'country_code_char2' => 'LA',
            'country_code_char3' => 'LAO',
            'state_subdivision_name' => 'Attapu [Attopeu]',
            'state_subdivision_alternate_names' => 'Attapu, Attopei, Attopeu',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'LA-AT',
        ]);

        State::create([
            'state_subdivision_id' => 15081,
            'country_code_char2' => 'LA',
            'country_code_char3' => 'LAO',
            'state_subdivision_name' => 'Bokèo',
            'state_subdivision_alternate_names' => '',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'LA-BK',
        ]);

        State::create([
            'state_subdivision_id' => 15082,
            'country_code_char2' => 'LA',
            'country_code_char3' => 'LAO',
            'state_subdivision_name' => 'Champasak [Champassak]',
            'state_subdivision_alternate_names' => 'Champasack, Champassak',
            'primary_level_name' => 'province',
            'state_subdivision_code' => 'LA-CH',
        ]);
    }
}
