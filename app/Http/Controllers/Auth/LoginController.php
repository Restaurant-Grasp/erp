<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     * Allow login with either username or email
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Get the needed authorization credentials from the request.
     */
    protected function credentials(Request $request)
    {
        $login = $request->get('login');
        
        // Check if login is email or username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        return [
            $field => $login,
            'password' => $request->get('password'),
            'is_active' => 1
        ];
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if account is locked
        if ($user->isLocked()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => ['Your account is locked. Please try again later.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => ['Your account is inactive. Please contact administrator.'],
            ]);
        }

        // Update last login information
        $user->updateLastLogin($request->ip());

        // Redirect based on role
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('sales_manager') || $user->hasRole('sales_executive')) {
            return redirect()->route('dashboard'); // Change to sales dashboard when available
        } elseif ($user->hasRole('hr_manager')) {
            return redirect()->route('dashboard'); // Change to HR dashboard when available
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the failed login response instance.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = \App\Models\User::where('username', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if ($user) {
            $user->incrementLoginAttempts();
        }

        throw ValidationException::withMessages([
            'login' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/login');
    }
}