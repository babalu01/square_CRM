@extends('layout')

@section('content')   
<div class="bg-white min-h-screen flex items-center justify-center">
    <div class="bg-white text-gray-800 rounded-lg p-6 w-96 shadow-lg relative">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Import Policies</h2>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{!! session('success') !!}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('Policies.import') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div id="uploadSection" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer mb-4" 
                 onclick="document.getElementById('file').click()" 
                 ondrop="handleFileSelect(event)" 
                 ondragover="handleDragOver(event)">
                <input type="file" id="file" name="file" class="hidden" onchange="handleFileSelect(event)" required />
                <i class="fas fa-file-excel text-4xl text-green-500 mb-4"></i>
                <p class="text-gray-600">Drag & drop your Excel file here or click to select</p>
            </div>
            
            <div id="uploadSectionPDF" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer mb-4" 
                 onclick="document.getElementById('files').click()" 
                 ondrop="handleFileSelectPDF(event)" 
                 ondragover="handleDragOver(event)">
                <input type="file" id="files" name="files[]" class="hidden" accept=".pdf" multiple onchange="handleFileSelectPDF(event)" required />
                <i class="fas fa-file-pdf text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">Drag & drop your PDF files here or click to select (multiple PDFs)</p>
            </div>
            
            <div id="progressSection" class="hidden bg-gray-100 rounded-lg p-6 mb-6">
                <div class="relative h-32 flex items-center justify-center">
                    <div class="absolute inset-0 bg-blue-100 rounded-lg opacity-50"></div>
                    <div class="relative z-10 text-center">
                        <div id="progressText" class="text-blue-500 text-lg font-semibold mb-2">0%</div>
                        <div id="uploadingText" class="text-gray-700 text-lg">Uploading...</div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between items-center mb-4">
                <!-- <div class="flex items-center space-x-2 text-blue-500">
                    <i class="fas fa-shield-alt"></i>
                    <a href="#" class="hover:underline">Verification</a>
                    <span class="text-gray-400">·</span>
                    <a href="#" class="hover:underline">Help Center</a>
                </div> -->
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="cancelUpload()" class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<script>
    function handleFileSelect(event) {
        event.preventDefault();
        document.getElementById('uploadSection').classList.add('hidden');
        document.getElementById('progressSection').classList.remove('hidden');
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            document.getElementById('progressText').innerText = `${progress}%`;
            if (progress >= 100) {
                clearInterval(interval);
                document.getElementById('progressText').innerText = '100%';
                document.getElementById('uploadingText').innerText = 'Upload Complete';
            }
        }, 500);
    }

    function handleDragOver(event) {
        event.preventDefault();
    }

    function cancelUpload() {
        document.getElementById('uploadSection').classList.remove('hidden');
        document.getElementById('progressSection').classList.add('hidden');
        document.getElementById('file').value = '';
    }

    function handleFileSelectPDF(event) {
        // Handle PDF file selection
        event.preventDefault();
        // Additional logic for handling PDF uploads can be added here
    }
</script>
@endsection
