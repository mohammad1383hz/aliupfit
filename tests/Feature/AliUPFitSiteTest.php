<?php

namespace Tests\Feature;

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AliUPFitSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_public_page_renders_original_rtl_site_and_styles(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('lang="fa" dir="rtl"', false)
            ->assertSee('id="app"', false)
            ->assertSee('class="hero"', false)
            ->assertSee('class="form"', false)
            ->assertSee('Vazirmatn', false)
            ->assertSee('csrf-token', false);
    }

    public function test_valid_contact_request_is_saved_as_real_database_record(): void
    {
        $response = $this->postJson('/forms', [
            'type' => 'cnt',
            'data' => ['name' => 'آزمایش', 'phone' => '۰۹۱۲۳۴۵۶۷۸۹', 'msg' => 'پیام آزمایشی'],
        ]);

        $response->assertCreated()->assertJsonPath('message', 'درخواست شما ثبت شد.');
        $this->assertDatabaseCount('form_submissions', 1);
        $this->assertDatabaseHas('form_submissions', ['type' => 'cnt', 'status' => 'new']);
        $this->assertSame('09123456789', FormSubmission::firstOrFail()->payload['phone']);
    }

    public function test_invalid_phone_does_not_create_submission(): void
    {
        $this->postJson('/forms', [
            'type' => 'cnt',
            'data' => ['name' => 'آزمایش', 'phone' => '123', 'msg' => 'پیام'],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_admin_routes_require_authenticated_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $user = User::factory()->create();
        $user->forceFill(['is_admin' => false])->save();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_sign_in_with_created_admin_account(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $admin->forceFill(['is_admin' => true, 'password' => Hash::make('a-long-test-password')])->save();

        $this->post('/admin/login', [
            'email' => 'admin@example.test',
            'password' => 'a-long-test-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk()->assertSee('درخواست‌های ثبت‌شده');
    }

    public function test_admin_can_update_persisted_site_settings_and_review_submissions(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $submission = FormSubmission::create([
            'type' => 'cnt', 'payload' => ['phone' => '09123456789'], 'status' => 'new',
        ]);

        $this->actingAs($admin)->put('/admin/settings', [
            'phone' => '02112345678', 'whatsapp' => '989121234567', 'instagram' => '@aliupfit',
            'address' => 'تهران', 'price_training' => '۲٬۰۰۰٬۰۰۰ تومان',
            'price_nutrition' => '', 'price_assessment' => '',
            'capacity_s612' => '12', 'capacity_s35a' => '', 'capacity_s35b' => '',
            'coachPhoto' => '',
        ])->assertRedirect();

        $this->assertSame('02112345678', SiteSetting::read('site_config')['phone']);
        $this->assertSame('12', (string) SiteSetting::read('site_config')['capacity']['s612']);
        $this->assertSame('تهران', SiteSetting::read('site_address'));
        $this->actingAs($admin)->get('/')->assertOk()->assertSee('"phone":"02112345678"', false);

        $this->actingAs($admin)->patch('/admin/submissions/'.$submission->id, ['status' => 'contacted'])->assertRedirect();
        $this->assertDatabaseHas('form_submissions', ['id' => $submission->id, 'status' => 'contacted']);
    }
}
