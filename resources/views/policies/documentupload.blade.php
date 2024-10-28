    @extends('layout')

    @section('content')
    @php 
    $user = getAuthenticatedUser();
    @endphp
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-xl p-8 bg-white shadow-lg rounded-lg">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Upload Policy Documents</h1>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('upload.policy.documents') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-12 cursor-pointer hover:border-blue-500 transition duration-300">
                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <label for="files" class="text-lg font-medium text-gray-700 mb-2">Drop files here or click to upload</label>
                    <input class="hidden" type="file" name="files[]" id="files" multiple required>
                    <p class="text-gray-500 text-sm">You can upload multiple files (jpg, jpeg, png, pdf)</p>
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300">
                        Upload Documents
                    </button>
                </div>
            </form>
        </div>
    </div>



@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

    @endsection