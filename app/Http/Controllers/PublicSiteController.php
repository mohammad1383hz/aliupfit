<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicSiteController extends Controller
{
    private function defaults(): array
    {
        return [
            'phone' => '',
            'whatsapp' => '',
            'instagram' => '',
            'capacity' => ['s612' => null, 's35a' => null, 's35b' => null],
            'prices' => ['training' => '', 'nutrition' => '', 'assessment' => ''],
            'coachPhoto' => '',
        ];
    }

    public function home()
    {
        $siteConfig = array_replace_recursive($this->defaults(), SiteSetting::read('site_config', []));
        $siteAddress = SiteSetting::read('site_address', 'تهران، خیابان آزادی، نبش اسکندری شمالی، برج نگین، طبقه سوم');

        return response()
            ->view('site', compact('siteConfig', 'siteAddress'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function submit(Request $request): JsonResponse
    {
        $data = $request->input('data', []);
        if (! is_array($data)) {
            return response()->json(['message' => 'اطلاعات فرم معتبر نیست.'], 422);
        }

        // Normalize Persian and Arabic numerals before validating a mobile number.
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = strtr($value, [
                    '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
                    '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
                ]);
            }
        }

        $request->merge(['data' => $data]);
        $type = $request->input('type');
        $rules = [
            'type' => ['required', 'in:reg,trn,nut,ass,cnt'],
            'data' => ['required', 'array', 'max:30'],
            'data.phone' => ['required', 'regex:/^09\d{9}$/'],
        ];
        $requiredByType = [
            'reg' => ['child', 'birth', 'age', 'parent', 'group', 'session', 'pack'],
            'trn' => ['name', 'goal', 'exp', 'days', 'place'],
            'nut' => ['name', 'goal', 'level', 'meals'],
            'ass' => ['child', 'parent'],
            'cnt' => ['name', 'msg'],
        ];
        foreach ($requiredByType[$type] ?? [] as $field) {
            $rules['data.'.$field] = ['required', 'string', 'max:2000'];
        }
        foreach (array_keys($data) as $key) {
            if (is_string($key) && preg_match('/^[a-z_]+$/', $key)) {
                $rules['data.'.$key] ??= ['nullable', 'string', 'max:2000'];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات ارسالی کامل یا معتبر نیست. لطفاً فیلدها را بررسی کنید.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submission = FormSubmission::create([
            'type' => $type,
            'payload' => $data,
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json(['message' => 'درخواست شما ثبت شد.', 'id' => $submission->id], 201);
    }
}
