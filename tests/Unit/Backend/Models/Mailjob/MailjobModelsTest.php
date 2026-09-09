<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Mailjob;

use App\Model\Mailjob\Condition;
use App\Model\Mailjob\FaveoQueue;
use App\Model\Mailjob\QueueService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class MailjobModelsTest extends TestCase
{
    // =========================================================================
    // Condition
    // =========================================================================

    public function test_condition_table_is_conditions(): void
    {
        $model = new Condition();
        $this->assertSame('conditions', $model->getTable());
    }

    public function test_condition_fillable_contains_expected_fields(): void
    {
        $model = new Condition();
        $fillable = $model->getFillable();
        $this->assertContains('job', $fillable);
        $this->assertContains('value', $fillable);
    }

    public function test_condition_check_array_returns_value_when_key_exists(): void
    {
        $model = new Condition();
        $result = $model->checkArray(0, ['foo', 'bar']);
        $this->assertSame('foo', $result);
    }

    public function test_condition_check_array_returns_second_element(): void
    {
        $model = new Condition();
        $result = $model->checkArray(1, ['foo', 'bar']);
        $this->assertSame('bar', $result);
    }

    public function test_condition_check_array_returns_empty_string_when_key_missing(): void
    {
        $model = new Condition();
        $result = $model->checkArray(5, ['foo']);
        $this->assertSame('', $result);
    }

    public function test_condition_check_array_returns_empty_string_for_non_array(): void
    {
        $model = new Condition();
        $result = $model->checkArray(0, 'not-an-array');
        $this->assertSame('', $result);
    }

    // =========================================================================
    // QueueService
    // =========================================================================

    public function test_queue_service_table_is_queue_services(): void
    {
        $model = new QueueService();
        $this->assertSame('queue_services', $model->getTable());
    }

    public function test_queue_service_fillable_contains_expected_fields(): void
    {
        $model = new QueueService();
        $fillable = $model->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('short_name', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_queue_service_extra_field_relation_is_has_many(): void
    {
        $model = new QueueService();
        $this->assertInstanceOf(HasMany::class, $model->extraFieldRelation());
    }

    public function test_queue_service_extra_field_relation_uses_service_id_foreign_key(): void
    {
        $model = new QueueService();
        $relation = $model->extraFieldRelation();
        $this->assertSame('service_id', $relation->getForeignKeyName());
    }

    public function test_queue_service_extra_field_relation_related_class_is_faveo_queue(): void
    {
        $model = new QueueService();
        $relation = $model->extraFieldRelation();
        $this->assertInstanceOf(FaveoQueue::class, $relation->getRelated());
    }

    public function test_queue_service_get_queue_details_returns_array(): void
    {
        $model = new QueueService();
        $model->forceFill(['id' => 1, 'name' => 'Database', 'status' => 1]);
        $details = $model->getQueueDetails();

        $this->assertIsArray($details);
        $this->assertArrayHasKey('id', $details);
        $this->assertArrayHasKey('name', $details);
        $this->assertArrayHasKey('status', $details);
        $this->assertSame(1, $details['id']);
        $this->assertSame('Database', $details['name']['text']);
        $this->assertNull($details['name']['link']); // Database → no link
        $this->assertSame(1, $details['status']['code']);
    }
}
