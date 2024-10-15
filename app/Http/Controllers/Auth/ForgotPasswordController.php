<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Rules\UniqueEmailPassword;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'account_type' => 'required|in:user,client',
        ]);

        if (isEmailConfigured()) {
            $provider = $request->input('account_type') . 's'; // 'users' or 'clients'

            try {
                $exists = $this->checkIfEmailExists($provider, $request->email);

                if ($exists) {
                    config(['auth.defaults.passwords' => $provider]);

                    $response = $this->broker($provider)->sendResetLink(
                        $request->only('email'),
                        function ($user, $token) use ($provider, $request) {
                            $resetUrl = $this->generateResetUrl($token, $user->email, $request->input('account_type'));
                            $user->notify(new ForgotPassword($user, $resetUrl));
                        }
                    );

                    config(['auth.defaults.passwords' => 'users']);

                    if ($response == Password::RESET_LINK_SENT) {
                        return response()->json(['error' => false, 'message' => __('Password reset link emailed successfully.')]);
                    } else {
                        return response()->json(['error' => true, 'message' => __($response)]);
                    }
                } else {
                    return response()->json(['error' => true, 'message' => 'Account not found.']);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Password reset link couldn\'t be sent, please check email settings.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Password reset link couldn\'t be sent, please configure email settings.']);
        }
    }


    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'account_type' => 'required|in:user,client',
        ]);

        $provider = $request->input('account_type') . 's'; // 'users' or 'clients'       

        // Check if email exists in the chosen provider's table
        $exists = $this->checkIfEmailExists($provider, $request->email);

        if (!$exists) {
            return response()->json(['error' => true, 'message' => 'Account not found.']);
        }

        $uniqueEmailPasswordRule = new UniqueEmailPassword($request->input('account_type'), 'forgot_password');
        if (!$uniqueEmailPasswordRule->passes('password', $request->input('password'), true)) {
            return response()->json([
                'error' => true,
                'message' => 'Validation errors occurred',
                'errors' => [
                    'email' => [$uniqueEmailPasswordRule->message()],
                ]
            ], 422);
        }
        if ($provider == 'users') {
            $status = Password::broker('users')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                }
            );
        } else {
            $status = Password::broker('clients')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (Client $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                }
            );
        }

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['error' => false, 'message' => __($status)]);
        } else {
            return response()->json(['error' => true, 'message' => __($status)]);
        }
    }


    protected function checkIfEmailExists($provider, $email)
    {
        $model = $provider === 'users' ? User::class : Client::class;
        return $model::where('email', $email)->exists();
    }

    // Generate the reset password URL
    protected function generateResetUrl($token, $email, $accountType)
    {
        return url('/reset-password/' . $token) . '?' . http_build_query([
            'email' => $email,
            'account_type' => $accountType
        ]);
    }
}
