<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $credentials['is_admin'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'اطلاعات ورود مدیر معتبر نیست.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $submissions = FormSubmission::query()->latest()->paginate(20);
        $counts = [
            'all' => FormSubmission::count(),
            'new' => FormSubmission::where('status', 'new')->count(),
            'processed' => FormSubmission::whereIn('status', ['contacted', 'approved', 'rejected'])->count(),
        ];

        return view('admin.dashboard', compact('submissions', 'counts'));
    }

    public function settings()
    {
        $config = SiteSetting::read('site_config', [
            'phone' => '', 'whatsapp' => '', 'instagram' => '',
            'capacity' => ['s612' => null, 's35a' => null, 's35b' => null],
            'prices' => ['training' => '', 'nutrition' => '', 'assessment' => ''], 'coachPhoto' => '',
        ]);
        $address = SiteSetting::read('site_address', 'تهران، خیابان آزادی، نبش اسکندری شمالی، برج نگین، طبقه سوم');

        return view('admin.settings', compact('config', 'address'));
    }

    public function updateSettings(Request $request)
    {
        $values = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'price_training' => ['nullable', 'string', 'max:100'],
            'price_nutrition' => ['nullable', 'string', 'max:100'],
            'price_assessment' => ['nullable', 'string', 'max:100'],
            'capacity_s612' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'capacity_s35a' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'capacity_s35b' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'coachPhoto' => ['nullable', 'url', 'max:2048'],
        ]);
        $existing = SiteSetting::read('site_config', []);
        $config = array_replace_recursive($existing, [
            'phone' => trim((string) ($values['phone'] ?? '')),
            'whatsapp' => preg_replace('/\D+/', '', (string) ($values['whatsapp'] ?? '')),
            'instagram' => ltrim(trim((string) ($values['instagram'] ?? '')), '@'),
            'prices' => [
                'training' => trim((string) ($values['price_training'] ?? '')),
                'nutrition' => trim((string) ($values['price_nutrition'] ?? '')),
                'assessment' => trim((string) ($values['price_assessment'] ?? '')),
            ],
            'capacity' => [
                's612' => $values['capacity_s612'] ?? null,
                's35a' => $values['capacity_s35a'] ?? null,
                's35b' => $values['capacity_s35b'] ?? null,
            ],
            'coachPhoto' => trim((string) ($values['coachPhoto'] ?? '')),
        ]);
        SiteSetting::write('site_config', $config);
        SiteSetting::write('site_address', trim($values['address']));

        return back()->with('success', 'تنظیمات ذخیره شد و در سایت اعمال شد.');
    }

    public function updateSubmission(Request $request, FormSubmission $submission)
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,approved,rejected']]);
        $submission->update($data);

        return back()->with('success', 'وضعیت درخواست به‌روزرسانی شد.');
    }

    public function deleteSubmission(FormSubmission $submission)
    {
        $submission->delete();

        return back()->with('success', 'درخواست حذف شد.');
    }
}
