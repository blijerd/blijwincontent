<?php

namespace App\Http\Controllers;

use App\Actions\Auth\CreateFirstUserAction;
use App\Http\Requests\StoreSetupUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class SetupController extends Controller
{
    public function show(): RedirectResponse|View
    {
        if (User::query()->exists()) {
            return redirect('/admin');
        }

        return view('setup.show');
    }

    public function store(StoreSetupUserRequest $request, CreateFirstUserAction $createFirstUser): RedirectResponse
    {
        try {
            $user = $createFirstUser->execute($request->validated());
        } catch (RuntimeException) {
            abort(403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/admin');
    }
}
