@extends('layout')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>

<style>
    .file-explorer {
        display: flex;
        flex-wrap: wrap;
        gap: 20px; /* Space between items */
    }
    .file-card {
        width: 150px; /* Fixed width for file cards */
        border: 1px solid #e2e8f0; /* Light gray border */
        border-radius: 8px; /* Rounded corners */
        padding: 10px; /* Padding inside the card */
        text-align: center; /* Center text */
        transition: transform 0.2s, box-shadow 0.2s; /* Smooth scaling effect */
        background-color: #fff; /* White background */
    }
    .file-card:hover {
        transform: scale(1.05); /* Scale up on hover */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Shadow effect */
    }
    .file-icon {
        font-size: 48px; /* Icon size */
        color: #a0aec0; /* Gray color for icons */
        margin-bottom: 10px; /* Space below the icon */
    }
</style>

<div class="container mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Policy Documents</h2>
    <div class="file-explorer">
        @if($policydocuments->count() > 0)
            @foreach($policydocuments as $document)
                <div class="file-card">
                    <div class="file-icon">
                        @php
                            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                        @endphp
                        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ asset('storage/' . $document->file_path) }}" alt="{{ $document->name }}" class="object-cover rounded mb-2" />
                        @elseif($extension == 'pdf')
                            <i class="fas fa-file-pdf"></i>
                        @else
                            <i class="fas fa-file-alt"></i>
                        @endif
                    </div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ $document->name }}</h3>
                    <div class="flex justify-between items-center">
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors duration-300 text-xs">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="{{ asset('storage/' . $document->file_path) }}" download class="text-green-600 hover:text-green-800 transition-colors duration-300 text-xs">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-gray-600">No documents available for this policy.</p>
        @endif
    </div>
</div>
@endsection
