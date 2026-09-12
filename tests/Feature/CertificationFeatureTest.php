<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CertificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_store_certification_with_image_and_pdf(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $payload = [
            'title' => 'AWS Solutions Architect',
            'description' => 'Certificacion profesional AWS.',
            'image' => UploadedFile::fake()->image('aws.jpg'),
            'certificate_file' => UploadedFile::fake()->create('aws.pdf', 300, 'application/pdf'),
            'published' => true,
            'sort_order' => 2,
        ];

        $response = $this->actingAs($admin)->post('/admin/certifications', $payload);

        $response->assertRedirect();

        $certification = Certification::where('title', 'AWS Solutions Architect')->first();

        $this->assertNotNull($certification);
        $this->assertNotNull($certification->image);
        $this->assertNotNull($certification->certificate_file);
        Storage::disk('public')->assertExists($certification->image);
        Storage::disk('public')->assertExists($certification->certificate_file);
    }

    public function test_admin_update_keeps_existing_files_when_no_new_file_is_uploaded(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        Storage::disk('public')->put('certifications/images/original.jpg', 'fake-image');
        Storage::disk('public')->put('certifications/pdfs/original.pdf', 'fake-pdf');

        $certification = Certification::create([
            'title' => 'Original',
            'description' => 'Descripcion original',
            'image' => 'certifications/images/original.jpg',
            'certificate_file' => 'certifications/pdfs/original.pdf',
            'published' => true,
            'sort_order' => 1,
        ]);

        $payload = [
            '_method' => 'PUT',
            'title' => 'Original Actualizada',
            'description' => 'Descripcion actualizada',
            'published' => false,
            'sort_order' => 4,
        ];

        $response = $this->actingAs($admin)->post("/admin/certifications/{$certification->id}", $payload);

        $response->assertRedirect();

        $certification->refresh();

        $this->assertSame('certifications/images/original.jpg', $certification->image);
        $this->assertSame('certifications/pdfs/original.pdf', $certification->certificate_file);
        Storage::disk('public')->assertExists('certifications/images/original.jpg');
        Storage::disk('public')->assertExists('certifications/pdfs/original.pdf');
    }

    public function test_admin_update_replaces_files_and_deletes_old_ones(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        Storage::disk('public')->put('certifications/images/old.jpg', 'old-image');
        Storage::disk('public')->put('certifications/pdfs/old.pdf', 'old-pdf');

        $certification = Certification::create([
            'title' => 'Certificacion',
            'description' => 'Descripcion',
            'image' => 'certifications/images/old.jpg',
            'certificate_file' => 'certifications/pdfs/old.pdf',
            'published' => true,
            'sort_order' => 0,
        ]);

        $payload = [
            '_method' => 'PUT',
            'title' => 'Certificacion',
            'description' => 'Descripcion nueva',
            'image' => UploadedFile::fake()->image('new.jpg'),
            'certificate_file' => UploadedFile::fake()->create('new.pdf', 400, 'application/pdf'),
            'published' => true,
            'sort_order' => 0,
        ];

        $response = $this->actingAs($admin)->post("/admin/certifications/{$certification->id}", $payload);

        $response->assertRedirect();

        $certification->refresh();

        Storage::disk('public')->assertMissing('certifications/images/old.jpg');
        Storage::disk('public')->assertMissing('certifications/pdfs/old.pdf');
        Storage::disk('public')->assertExists($certification->image);
        Storage::disk('public')->assertExists((string) $certification->certificate_file);
    }

    public function test_admin_destroy_deletes_certification_and_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        Storage::disk('public')->put('certifications/images/to-delete.jpg', 'image');
        Storage::disk('public')->put('certifications/pdfs/to-delete.pdf', 'pdf');

        $certification = Certification::create([
            'title' => 'Eliminar',
            'description' => 'Eliminar item',
            'image' => 'certifications/images/to-delete.jpg',
            'certificate_file' => 'certifications/pdfs/to-delete.pdf',
            'published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/certifications/{$certification->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('certifications', ['id' => $certification->id]);
        Storage::disk('public')->assertMissing('certifications/images/to-delete.jpg');
        Storage::disk('public')->assertMissing('certifications/pdfs/to-delete.pdf');
    }

    public function test_home_only_includes_published_certifications_sorted_by_order(): void
    {
        Certification::create([
            'title' => 'No Visible',
            'description' => 'Debe ocultarse',
            'image' => 'certifications/images/no-visible.jpg',
            'certificate_file' => null,
            'published' => false,
            'sort_order' => 1,
        ]);

        $first = Certification::create([
            'title' => 'Primera',
            'description' => 'Orden 1',
            'image' => 'certifications/images/primera.jpg',
            'certificate_file' => null,
            'published' => true,
            'sort_order' => 1,
        ]);

        $second = Certification::create([
            'title' => 'Segunda',
            'description' => 'Orden 2',
            'image' => 'certifications/images/segunda.jpg',
            'certificate_file' => null,
            'published' => true,
            'sort_order' => 2,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->where('certifications', function ($items) use ($first, $second) {
                $values = collect($items)->values();

                return $values->count() === 2
                    && $values[0]['id'] === $first->id
                    && $values[1]['id'] === $second->id;
            })
        );
    }

    public function test_home_respects_certifications_section_visibility_setting(): void
    {
        SiteSetting::set('section_certifications_visible', '0');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->where('sectionVisibility.certifications', false)
        );
    }
}
