@extends('layout')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
<!-- Include DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

<style>
    .file-explorer {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .file-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
        background-color: #f9fafb;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .file-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .file-icon {
        font-size: 50px;
        color: #4a5568;
        margin-bottom: 15px;
    }

    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #38a169; /* Green color for success */
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        z-index: 1000;
        display: none;
    }

    .toast-error {
        background-color: #e53e3e; /* Red color for errors */
    }
</style>
{{-- @dd($notfoundpolicies); --}}
<div class="container">
    <h1>Pending Policy Documents</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h2>Not Found Policies</h2>
    @if($notfoundpolicies->isEmpty())
        <p>No pending policies found.</p>
    @else
    <table id="policiesTable" class="min-w-full border-collapse border border-gray-200">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Customer Name</th>
                <th class="border border-gray-300 px-4 py-2">Vehicle No</th>
                <th class="border border-gray-300 px-4 py-2">Policy No</th>
                <th class="border border-gray-300 px-4 py-2">Status</th>
                {{-- Add more headers as needed --}}
            </tr>
        </thead>
        <tbody>
            @foreach($notfoundpolicies as $policy)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $policy->id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $policy->CustomerName }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $policy->Vehicle_No }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $policy->Policy_No }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $policy->STATUS }}</td>
                    {{-- Add more data cells as needed --}}
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
<hr><hr>
<div class="container mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Pending</h2>
    <div class="file-explorer">
        @if($policydocuments->count() > 0)
            @foreach($policydocuments as $document)
                <div class="file-card" data-id="{{ $document->id }}">
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
                    @php
                        $filenameWithTimestamp = pathinfo($document->file_path, PATHINFO_FILENAME);
                        $filenameParts = explode('_', $filenameWithTimestamp);
                        $baseFilename = implode('_', array_slice($filenameParts, 0, -1));
                    @endphp
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ $baseFilename }}</h3>
                    <div class="flex justify-between items-center">
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors duration-300 text-xs">
                            <i class="fas fa-eye mr-1"></i>
                        </a>
                        <a href="{{ asset('storage/' . $document->file_path) }}" download class="text-green-600 hover:text-green-800 transition-colors duration-300 text-xs">
                            <i class="fas fa-download mr-1"></i>
                        </a>
                        <a href="#" onclick="openEditModal({{ $document->id }})" class="text-yellow-600 hover:text-yellow-800 transition-colors duration-300 text-xs">
                            <i class="fas fa-edit mr-1"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-gray-600">No documents available for this policy.</p>
        @endif
    </div>
</div>

<!-- Add Modal HTML -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg">
        <h3 class="text-lg font-semibold mb-4">Edit Policy Registration Number</h3>
        <input type="hidden" id="documentId" />
        <input type="text" id="policyRegistrationNumber" class="border rounded p-2 w-full mb-4" placeholder="Enter Policy Registration Number" />
        <div class="flex justify-end">
            <button id="submitEdit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
            <button id="closeModal" class="ml-2 bg-gray-300 px-4 py-2 rounded">Cancel</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable with 10 entries per page
        $('#policiesTable').DataTable({
            pageLength: 10
        });
    });

    function openEditModal(documentId) {
        // Show the modal
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('documentId').value = documentId; // Store the document ID
    }

    // Add toast notification function
    function showToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.className = 'toast' + (isError ? ' toast-error' : ''); // Add class for styling
        toast.innerText = message;
        document.body.appendChild(toast);
        toast.style.display = 'block'; // Make the toast visible
        setTimeout(() => {
            toast.style.display = 'none'; // Hide toast after 3 seconds
        }, 3000);
    }

    document.getElementById('submitEdit').onclick = function() {
        const registrationNumber = document.getElementById('policyRegistrationNumber').value;
        const docId = document.getElementById('documentId').value; // Get the document ID

        // Submit the registration number and document ID via AJAX
        fetch(`/pending/documents/store/${docId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
            },
            body: JSON.stringify({ 
                registration_number: registrationNumber,
                document_id: docId // Added document ID to the request body
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) { // Check if the response indicates success
                document.querySelector(`.file-card[data-id="${docId}"]`).style.display = 'none';
                showToast('Registration number updated successfully!');
            } else {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(' ');
                    showToast(errorMessages, true); // Show validation errors as toast
                } else {
                    showToast(data.message || 'An error occurred. Please try again.', true); // Generic error message
                }
            }
            document.getElementById('editModal').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', true);
            document.getElementById('editModal').classList.add('hidden');
        });
    };

    document.getElementById('closeModal').onclick = function() {
        document.getElementById('editModal').classList.add('hidden');
    };
</script>
@endsection
