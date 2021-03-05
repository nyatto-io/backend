<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->settings;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
        ]);

        $user->setSetting($data['key'], $data['value']);

        return $user->settings->fresh();
    }

    public function storeBulk(Request $request)
    {
        $user = $request->user();

        $settings = $request->validate([
            'payload' => ['nullable', 'array'],
            'payload.*.key' => ['required', 'string', 'max:255'],
            'payload.*.value' => ['required', 'string', 'max:255'],
        ])['payload'];

        foreach ($settings as $setting) {
            $user->setSetting($setting['key'], $setting['value']);
        }

        return $user->settings->fresh();
    }
}
