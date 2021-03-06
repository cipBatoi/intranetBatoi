<?php

namespace Intranet\Http\Controllers\Auth\Profesor;

use Intranet\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Intranet\Entities\Profesor;

class LoginController extends Controller
{
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function username()
    {
        return 'codigo';
    }

    protected function guard()
    {
        return Auth::guard('profesor');
    }

    public function showLoginForm()
    {
        return view('auth/profesor/login');
    }

    public function logout()
    {
        if (isPrivateAddress(getClientIpAddress())){
            Auth::guard('profesor')->logout();
            Session()->flush();
            return redirect('/login');
        }
        Auth::guard('profesor')->logout();
        Session()->flush();
        return redirect()->to('http://www.cipfpbatoi.es/index.php/ca/principal/')->send();

    }

    public function plogin(Request $request)
    {
        isset(Profesor::where('codigo',$request->codigo)->get()->first()->idioma)?session(['lang' => Profesor::where('codigo',$request->codigo)->get()->first()->idioma]):'ca';
        return $this->login($request);
        
    }
}
