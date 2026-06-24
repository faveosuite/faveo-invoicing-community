<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

class TemplateControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    private function makeType(): TemplateType
    {
        $id = DB::table('template_types')->insertGetId(['name' => 'test_type_'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

        return TemplateType::find($id);
    }

    private function makeTemplate(int $typeId): Template
    {
        return Template::create([
            'name'    => 'Test Template '.uniqid(),
            'type'    => $typeId,
            'subject' => 'Subject',
            'data'    => '<p>Body</p>',
        ]);
    }

    // GET /template/list — getTemplates
    public function test_get_templates_returns_200_with_paginated_data(): void
    {
        $type = $this->makeType();
        $this->makeTemplate($type->id);

        $response = $this->getJson('/template/list');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['data', 'total']]);
    }

    public function test_get_templates_search_filters_by_name(): void
    {
        $type = $this->makeType();
        $tpl = $this->makeTemplate($type->id);

        $response = $this->getJson('/template/list?search-query='.urlencode($tpl->name));
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.data')));
    }

    public function test_get_templates_sort_by_id_descending(): void
    {
        $type = $this->makeType();
        $this->makeTemplate($type->id);

        $response = $this->getJson('/template/list?sort-field=id&sort-order=desc');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_templates_invalid_sort_field_falls_back_to_name(): void
    {
        $response = $this->getJson('/template/list?sort-field=nonexistent_column');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // GET /template/edit/{id} — showTemplate
    public function test_show_template_returns_200_with_template_data(): void
    {
        $type = $this->makeType();
        $tpl = $this->makeTemplate($type->id);

        $response = $this->getJson("/template/edit/{$tpl->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.template.id', $tpl->id);
    }

    public function test_show_template_nonexistent_id_returns_400(): void
    {
        $response = $this->getJson('/template/edit/999999');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // PUT /template/update/{id} — updateTemplate
    public function test_update_template_missing_name_returns_422(): void
    {
        $type = $this->makeType();
        $tpl = $this->makeTemplate($type->id);

        $response = $this->putJson("/template/update/{$tpl->id}", [
            'data' => '<p>content</p>',
            'type' => $type->id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_template_missing_data_returns_422(): void
    {
        $type = $this->makeType();
        $tpl = $this->makeTemplate($type->id);

        $response = $this->putJson("/template/update/{$tpl->id}", [
            'name' => 'Updated',
            'type' => $type->id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data']);
    }

    public function test_update_template_with_valid_data_returns_200(): void
    {
        $type = $this->makeType();
        $tpl = $this->makeTemplate($type->id);

        $response = $this->putJson("/template/update/{$tpl->id}", [
            'name' => 'Updated Name',
            'data' => '<p>New content</p>',
            'type' => $type->id,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_update_template_nonexistent_id_returns_400(): void
    {
        $type = $this->makeType();
        $response = $this->putJson('/template/update/999999', [
            'name' => 'Name',
            'data' => '<p>Body</p>',
            'type' => $type->id,
        ]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
