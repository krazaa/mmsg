<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_database_backup_tools(): void
    {
        $this->seed();

        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin)->get(route('database-backup.index'))
            ->assertOk()
            ->assertSee('Backup &amp; restore', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('database-backup.index'))->assertForbidden();
    }

    public function test_super_admin_can_create_list_download_and_delete_a_valid_backup(): void
    {
        $this->seed();
        Storage::fake('local');
        $superAdmin = $this->superAdmin();
        $filename = 'abdullah-town-backup-'.now()->format('Y-m-d-His').'.sql.gz';
        Storage::disk('local')->put('backups/'.$filename, gzencode('CREATE TABLE example (id BIGINT);'));
        $this->mock(DatabaseBackupService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('create')->once();
        });

        $this->actingAs($superAdmin)->post(route('database-backup.store'))
            ->assertRedirect(route('database-backup.index'))
            ->assertSessionHas('success');

        $path = collect(Storage::disk('local')->files('backups'))->sole();
        $this->assertSame($filename, basename($path));
        $this->actingAs($superAdmin)->get(route('database-backup.index'))
            ->assertOk()
            ->assertSee($filename);

        $response = $this->actingAs($superAdmin)->get(route('database-backup.download', $filename));

        $response->assertOk()->assertDownload($filename);
        $this->assertSame('CREATE TABLE example (id BIGINT);', gzdecode(Storage::disk('local')->get($path)));

        $this->actingAs($superAdmin)->delete(route('database-backup.destroy', $filename))
            ->assertRedirect(route('database-backup.index'));
        Storage::disk('local')->assertMissing($path);
    }

    public function test_restore_requires_the_current_password(): void
    {
        $this->seed();
        $superAdmin = $this->superAdmin();
        $this->mock(DatabaseBackupService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('restore');
        });

        $this->actingAs($superAdmin)->post(route('database-backup.restore'), [
            'backup' => UploadedFile::fake()->createWithContent('backup.sql.gz', gzencode('SELECT 1;')),
            'current_password' => 'wrong-password',
            'confirm_restore' => '1',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_super_admin_can_restore_a_native_database_dump(): void
    {
        $this->seed();
        $superAdmin = $this->superAdmin();
        $this->mock(DatabaseBackupService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('restore')->once()->andReturn(['database' => 'testing']);
        });

        $this->actingAs($superAdmin)->post(route('database-backup.restore'), [
            'backup' => UploadedFile::fake()->createWithContent('backup.sql.gz', gzencode('CREATE TABLE example (id BIGINT);')),
            'current_password' => 'password',
            'confirm_restore' => '1',
        ])->assertRedirect(route('database-backup.index'))->assertSessionHas('success');

    }

    private function superAdmin(): User
    {
        $user = User::where('role', 'admin')->firstOrFail();
        $user->update(['role' => 'super_admin']);
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));

        return $user->refresh();
    }
}
