<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class loginAuthenticationController extends Controller
{
   public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request) 
    {
        $request->validate([
            'code' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('code', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/')
                        ->with('success', 'Signed in successfully!');
        }

        return redirect()->route('login')->withErrors(['code' => 'Login details are not valid']);
    }


    public function registration()
    {
        return view('auth.registration');
    }


    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            //'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'code' => 'required'
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }


    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        //'email' => $data['email'],
        'code' => $data['code'],  
        'password' => Hash::make($data['password'])
      ]);
    }
}
