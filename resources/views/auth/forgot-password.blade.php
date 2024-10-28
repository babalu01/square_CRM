@extends('layout')
<title>{{get_label('forgot_password','Forgot Password')}} - {{$general_settings['company_title']}}</title>
@section('content')
<style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
    </style>
<!-- Content -->
<div class=" font-roboto min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white shadow-md rounded-lg p-8 max-w-md w-full text-center">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            {{get_label('forgot_password','Forgot Password')}}
        </h1>
        <p class="text-gray-600 mb-6">
            {{get_label('forgot_password_info','Enter your email and we\'ll send you password reset link')}}
        </p>
        <img alt="Illustration of a person looking confused in front of a computer screen with a password reset form" class="mx-auto mb-6" height="300" src="https://storage.googleapis.com/a1aa/image/9ZbM7LVTySbGOJwlb87oP2t6JPFR8fpe3X43Oc1SbGaIRToTA.jpg" width="400"/>
        <form id="formAuthentication" class="mb-3 form-submit-event" action="{{url('forgot-password-mail')}}" method="POST">
            <input type="hidden" name="dnr">
            @csrf
            <div class="mb-3">
                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                    <input type="radio" class="btn-check" id="account_type_user" name="account_type" value="user" checked>
                    <label class="btn btn-outline-primary" for="account_type_user"><?= get_label('user_account', 'User Account') ?></label>
                    <input type="radio" class="btn-check" id="account_type_client" name="account_type" value="client">
                    <label class="btn btn-outline-primary" for="account_type_client"><?= get_label('client_account', 'Client Account') ?></label>
                </div>
            </div>
            <div class="mb-3">
                <input type="email" class="border border-gray-300 rounded-lg py-2 px-4 mb-4 w-full" id="email" name="email" placeholder="<?= get_label('please_enter_email', 'Please enter email') ?>" value="{{ old('email') }}" autofocus />
            </div>
            <button type="submit" id="submit_btn" class="bg-green-500 text-white font-bold py-2 px-4 rounded-full hover:bg-green-600 w-full">{{get_label('submit','Submit')}}</button>
        </form>
        <div class="text-center">
            <a href="{{url('')}}" class="d-flex align-items-center justify-content-center text-gray-600 hover:text-gray-800">
                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                Back to login
            </a>
        </div>
    </div>
    <footer class="mt-8 text-center text-gray-600">
        <div class="flex justify-center items-center space-x-2">
            <img alt="Company logo" class="inline-block" height="20" src="{{asset($general_settings['full_logo'])}}" width="20"/>
            <span>
                © 2023 {{$general_settings['company_title']}}. All rights reserved.
            </span>
        </div>
        <div class="mt-2">
            <a class="text-gray-600 hover:text-gray-800 mx-2" href="#">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a class="text-gray-600 hover:text-gray-800 mx-2" href="#">
                <i class="fab fa-twitter"></i>
            </a>
            <a class="text-gray-600 hover:text-gray-800 mx-2" href="#">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </footer>
</div>
<!-- / Content -->
@endsection