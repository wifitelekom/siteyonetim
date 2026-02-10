<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Gunes Site',
            'is_active' => true,
        ]);

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'owner']);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $role . '@test.com',
            'password' => bcrypt('password'),
            'site_id' => $this->site->id,
        ]);

        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->put(route('management.site-settings.update'), [
                'name' => 'Yeni Site Adi',
                'phone' => '0212 000 00 00',
                'address' => 'Test adres',
            ])
            ->assertRedirect(route('management.site-settings.edit'));

        $this->assertDatabaseHas('sites', [
            'id' => $this->site->id,
            'name' => 'Yeni Site Adi',
            'phone' => '0212 000 00 00',
            'address' => 'Test adres',
        ]);
    }

    public function test_non_admin_cannot_access_site_settings(): void
    {
        $owner = $this->makeUser('owner');

        $this->actingAs($owner)
            ->get(route('management.site-settings.edit'))
            ->assertForbidden();
    }
}

