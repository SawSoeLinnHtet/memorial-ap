<?php

namespace Tests\Feature;

use App\Models\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeatureApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = config('app.api_key');
    }

    public function test_features_api_requires_api_key(): void
    {
        $this->getJson('/api/features')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_can_list_features(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/list-feature.jpg',
            'memorial_text' => 'A remembered life',
            'memorial_date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->getJson('/api/features')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Features fetched successfully',
                'data' => [
                    [
                        'id' => $feature->id,
                        'memorial_text' => 'A remembered life',
                        'memorial_date' => '2026-04-19',
                    ],
                ],
            ]);
    }

    public function test_can_create_feature(): void
    {
        Storage::fake('public');

        $this->withApiKey()
            ->postJson('/api/features', [
                'memorial_text' => 'Created through the API',
                'memorial_date' => '2026-04-19',
                'image' => UploadedFile::fake()->image('created-feature.jpg'),
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature created successfully',
                'data' => [
                    'memorial_text' => 'Created through the API',
                    'memorial_date' => '2026-04-19',
                ],
            ])
            ->assertJsonPath('data.id', 1);

        $this->assertDatabaseHas('features', [
            'memorial_text' => 'Created through the API',
            'memorial_date' => '2026-04-19',
        ]);
    }

    public function test_create_feature_validates_required_fields(): void
    {
        $this->withApiKey()
            ->postJson('/api/features')
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors([
                'memorial_text',
                'memorial_date',
                'image',
            ], 'errors');
    }

    public function test_can_show_feature(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/show-feature.jpg',
            'memorial_text' => 'Shown through binding',
            'memorial_date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->getJson("/api/features/{$feature->id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature fetched successfully',
                'data' => [
                    'id' => $feature->id,
                    'memorial_text' => 'Shown through binding',
                    'memorial_date' => '2026-04-19',
                ],
            ]);
    }

    public function test_can_update_feature(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/update-feature.jpg',
            'memorial_text' => 'Before update',
            'memorial_date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->putJson("/api/features/{$feature->id}", [
                'memorial_text' => 'After update',
                'memorial_date' => '2026-04-20',
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature updated successfully',
                'data' => [
                    'id' => $feature->id,
                    'memorial_text' => 'After update',
                    'memorial_date' => '2026-04-20',
                ],
            ]);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'memorial_text' => 'After update',
            'memorial_date' => '2026-04-20',
        ]);
    }

    public function test_can_soft_delete_and_list_trashed_features(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/delete-feature.jpg',
            'memorial_text' => 'Soft deleted',
            'memorial_date' => '2026-04-19',
        ]);

        $this->withApiKey()
            ->deleteJson("/api/features/{$feature->id}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature deleted successfully',
                'data' => null,
            ]);

        $this->assertSoftDeleted('features', [
            'id' => $feature->id,
        ]);

        $this->withApiKey()
            ->getJson('/api/features/trashed')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Trashed features fetched successfully',
                'data' => [
                    [
                        'id' => $feature->id,
                        'memorial_text' => 'Soft deleted',
                    ],
                ],
            ]);
    }

    public function test_can_restore_feature(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/restore-feature.jpg',
            'memorial_text' => 'Restored',
            'memorial_date' => '2026-04-19',
        ]);
        $feature->delete();

        $this->withApiKey()
            ->postJson("/api/features/{$feature->id}/restore")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature restored successfully',
                'data' => [
                    'id' => $feature->id,
                    'memorial_text' => 'Restored',
                ],
            ]);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_permanently_delete_feature(): void
    {
        $feature = Feature::create([
            'image_url' => 'uploads/permanent-feature.jpg',
            'memorial_text' => 'Permanent delete',
            'memorial_date' => '2026-04-19',
        ]);
        $feature->delete();

        $this->withApiKey()
            ->deleteJson("/api/features/{$feature->id}/permanent")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Feature deleted permanently',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('features', [
            'id' => $feature->id,
        ]);
    }

    private function withApiKey(): self
    {
        return $this->withHeader('X-API-KEY', $this->apiKey);
    }
}
