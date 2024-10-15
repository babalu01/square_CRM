@extends('layout')
    <title>Login Page - {{$general_settings['company_title']}}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
    </style>
@section('content')
<div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-4xl flex flex-col md:flex-row">
        <div class="w-full md:w-1/2 p-8">
            <img alt="Illustration of a man and woman at a reception desk" class="w-full h-full object-cover" height="400" src="https://storage.googleapis.com/a1aa/image/YfeCt7e6yedeRgkRU9T8iYfZfsvURQK5F5lbw8WQ2kfJv1eNnA.jpg" width="400"/>
        </div>
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
            <div class="mb-8">
                <img alt="{{$general_settings['company_title']}} logo" class="mb-4" height="40" src="{{asset($general_settings['full_logo'])}}" width="100"/>
                <h2 class="text-3xl font-bold mb-2">{{get_label('welcome_to','Welcome to')}} {{$general_settings['company_title']}}! 👋</h2>
                <p class="text-gray-600">{{get_label('sign_into_your_account','Sign into your account')}}</p>
            </div>
            <form id="formAuthentication" class="mb-3 form-submit-event" action="{{url('users/authenticate')}}" method="POST">
                <input type="hidden" name="redirect_url" value="{{ url('home') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="email">{{get_label('email','Email')}} <span class="asterisk">*</span></label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <div class="px-3 py-2 bg-gray-100">
                            <i class="fas fa-envelope text-gray-500"></i>
                        </div>
                        <input class="w-full px-3 py-2 focus:outline-none" id="email" name="email" placeholder="<?= get_label('please_enter_email', 'Please enter email') ?>" value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? 'admin@gmail.com' : '' ?>" type="email" autofocus />
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="password">{{get_label('password','Password')}} <span class="asterisk">*</span></label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <div class="px-3 py-2 bg-gray-100">
                            <i class="fas fa-lock text-gray-500"></i>
                        </div>
                        <input class="w-full px-3 py-2 focus:outline-none" id="password" name="password" placeholder="<?= get_label('please_enter_password', 'Please enter password') ?>" value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? '123456' : '' ?>" type="password" />
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input class="form-checkbox text-blue-500" type="checkbox"/>
                        <span class="ml-2 text-gray-700">{{get_label('remember_me','Remember Me')}}</span>
                    </label>
                    <a class="text-blue-500" href="{{url('forgot-password')}}">{{get_label('forgot_password','Forgot Password')}}?</a>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <button class="bg-blue-500 text-white px-4 py-2 rounded-lg" id="submit_btn" type="submit">{{get_label('login','Login')}}</button>
                    @if (!isset($general_settings['allowSignup']) || $general_settings['allowSignup'] == 1)
                    <a href="{{url('signup')}}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">{{get_label('sign_up','Sign Up')}}</a>
                    @endif
                </div>
                @if (config('constants.ALLOW_MODIFICATION') === 0)
                <div class="mb-3">
                    <button class="btn btn-success d-grid w-100 admin-login" type="button">Login As Admin</button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-info d-grid w-100 member-login" type="button">Login As Team Member</button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-warning d-grid w-100 client-login" type="button">Login As Client</button>
                </div>
                @endif
            </form>
        </div>
    </div>
    </div>

@endsection