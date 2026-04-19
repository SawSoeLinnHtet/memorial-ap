<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollectionApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = config('app.api_key');
    }

    public function test_collections_api_requires_api_key(): void
    {
        $this->getJson('/api/collections')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_can_list_collections(): void
    {
        $collection = Collection::create([
            'name' => 'Family Memorial',
            'description' => 'A family collection',
            'date' => '2026-04-19',
        ]);

        $feature = Feature::create([
            'image_url' => 'uploads/collection-feature.jpg',
            'memorial_text' => 'A family collection',
            'memorial_date' => '2026-04-19',
        ]);

        $collection->features()->attach($feature);

        $this->withApiKey()
            ->getJson('/api/collections')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collections fetched successfully',
                'data' => [
                    [
                        'id' => $collection->id,
                        'name' => 'Family Memorial',
                        'description' => 'A family collection',
                        'memorial_date' => '2026-04-19',
                        'features' => [
                            [
                                'id' => $feature->id,
                                'memorial_text' => 'A family collection',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_create_collection(): void
    {
        Storage::fake('public');

        $this->withApiKey()
            ->postJson('/api/collections', [
                'name' => 'Family Memorial',
                'description' => 'A shared memorial collection',
                'memorial_date' => '2026-04-19',
                'images' => [
                    UploadedFile::fake()->image('first.jpg'),
                    UploadedFile::fake()->image('second.png'),
                ],
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection created successfully',
                'data' => [
                    'id' => 1,
                    'name' => 'Family Memorial',
                    'description' => 'A shared memorial collection',
                    'memorial_date' => '2026-04-19',
                ],
            ])
            ->assertJsonCount(2, 'data.features');

        $this->assertDatabaseHas('collections', [
            'name' => 'Family Memorial',
            'description' => 'A shared memorial collection',
            'date' => '2026-04-19',
        ]);
    }

    public function test_create_collection_validates_required_fields(): void
    {
        $this->withApiKey()
            ->postJson('/api/collections')
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors([
                'name',
                'description',
                'memorial_date',
                'images',
            ], 'errors');
    }

    public function test_can_show_collection(): void
    {
        $collection = Collection::create([
            'name' => 'Shown Collection',
            'description' => 'Shown through binding',
            'date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->getJson("/api/collections/{$collection->id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection fetched successfully',
                'data' => [
                    'id' => $collection->id,
                    'name' => 'Shown Collection',
                    'description' => 'Shown through binding',
                    'memorial_date' => '2026-04-19',
                ],
            ]);
    }

    public function test_can_update_collection(): void
    {
        Storage::fake('public');

        $collection = Collection::create([
            'name' => 'Before',
            'description' => 'Before update',
            'date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->postJson("/api/collections/{$collection->id}", [
                '_method' => 'PUT',
                'name' => 'After',
                'description' => 'After update',
                'memorial_date' => '2026-04-20',
                'images' => [
                    UploadedFile::fake()->image('updated.jpg'),
                ],
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection updated successfully',
                'data' => [
                    'id' => $collection->id,
                    'name' => 'After',
                    'description' => 'After update',
                    'memorial_date' => '2026-04-20',
                ],
            ])
            ->assertJsonCount(1, 'data.features');
    }

    public function test_can_soft_delete_collection(): void
    {
        $collection = Collection::create([
            'name' => 'Delete',
            'description' => 'Soft delete',
            'date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->deleteJson("/api/collections/{$collection->id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection deleted successfully',
                'data' => null,
            ]);

        $this->assertSoftDeleted('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_can_restore_collection(): void
    {
        $collection = Collection::create([
            'name' => 'Restore',
            'description' => 'Restore collection',
            'date' => '2026-04-19',
        ]);
        $collection->delete();

        $this->withApiKey()
            ->postJson("/api/collections/{$collection->id}/restore")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection restored successfully',
                'data' => [
                    'id' => $collection->id,
                    'name' => 'Restore',
                ],
            ]);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_permanently_delete_collection(): void
    {
        $collection = Collection::create([
            'name' => 'Permanent',
            'description' => 'Permanent delete',
            'date' => '2026-04-19',
        ]);
        $collection->delete();

        $this->withApiKey()
            ->deleteJson("/api/collections/{$collection->id}/permanent")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Collection deleted permanently',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('collections', [
            'id' => $collection->id,
        ]);
    }

    private function withApiKey(): self
    {
        return $this->withHeader('X-API-KEY', $this->apiKey);
    }
}
