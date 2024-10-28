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

<div class="bg-gray-100 min-h-screen py-8">
        <div class="p-2 lg:p-6">
            <div class="mx-auto p-2 lg:p-4">
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded">
                    <p class="text-lg font-semibold">
                        Remaining Time: 
                        <span class="font-bold">{{ $remainingTime }}</span>
                    </p>
                </div>
                <div class="bg-label-gray grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 border border-gray-300 p-4 rounded-lg">
                    <div>
                        <p class="text-gray-600">ID:</p>
                        <p class="text-gray-600">{{ $policy->id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Number:</p>
                        <p class="text-gray-600">{{ $policy->policy_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Type:</p>
                        <p class="text-gray-600">{{ $policy->type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Provider:</p>
                        <p class="text-gray-600">{{ $policy->provider }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Premium Amount:</p>
                        <p class="text-gray-600">{{ number_format($policy->premium_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Start Date:</p>
                        <p class="text-gray-600">{{ $policy->start_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">End Date:</p>
                        <p class="text-gray-600">{{ $policy->end_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status:</p>
                        <p class="text-gray-600">{{ $policy->status }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Created At:</p>
                        <p class="text-gray-600">{{ $policy->created_at }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Updated At:</p>
                        <p class="text-gray-600">{{ $policy->updated_at }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Company:</p>
                        <p class="text-gray-600">{{ $policy->company }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Product:</p>
                        <p class="text-gray-600">{{ $policy->product }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Manufacturing Year:</p>
                        <p class="text-gray-600">{{ $policy->mfg_year }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Fuel Type:</p>
                        <p class="text-gray-600">{{ $policy->fuel_type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">GVW/CC:</p>
                        <p class="text-gray-600">{{ $policy->gvw_cc }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Holder Name:</p>
                        <p class="text-gray-600">{{ $policy->policy_holder_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">OD:</p>
                        <p class="text-gray-600">{{ number_format($policy->od, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Without GST:</p>
                        <p class="text-gray-600">{{ number_format($policy->without_gst, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total:</p>
                        <p class="text-gray-600">{{ number_format($policy->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Registration Number:</p>
                        <p class="text-gray-600">{{ $policy->registration_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Type:</p>
                        <p class="text-gray-600">{{ $policy->policy_type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Agent Name:</p>
                        <p class="text-gray-600">{{ $policy->agent_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Broker/Direct Code:</p>
                        <p class="text-gray-600">{{ $policy->broker_direct_code }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Mode of Payment:</p>
                        <p class="text-gray-600">{{ $policy->mode_of_payment }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Percentage:</p>
                        <p class="text-gray-600">{{ number_format($policy->percentage, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Commission:</p>
                        <p class="text-gray-600">{{ number_format($policy->commission, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">TDS:</p>
                        <p class="text-gray-600">{{ number_format($policy->tds, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Final Commission:</p>
                        <p class="text-gray-600">{{ number_format($policy->final_commission, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Discount Percentage:</p>
                        <p class="text-gray-600">{{ number_format($policy->discount_percentage, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Discount:</p>
                        <p class="text-gray-600">{{ number_format($policy->discount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Payment:</p>
                        <p class="text-gray-600">{{ number_format($policy->payment, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Cheque No:</p>
                        <p class="text-gray-600">{{ $policy->cheque_no }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Payment Received:</p>
                        <p class="text-gray-600">{{ number_format($policy->payment_received, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Profit:</p>
                        <p class="text-gray-600">{{ number_format($policy->profit, 2) }}</p>
                    </div>
                    <div>
                    <a href="{{ route('policies.edit', $policy->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Update Policy
            </a>
                    </div>
                    <div>
                    <a href="{{ route('policies.index') }}" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Back to Policies
            </a>
                    </div>
                 
                </div>
                <div class="bg-white shadow-md rounded-lg mt-6 overflow-hidden">
                    <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Policy Documents</h2>
                        <button id="uploadDocumentBtn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Upload Document
                        </button>
                    </div>
                    <div class="container mx-auto mt-6 mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Policy Documents</h2>
                        <div class="file-explorer">
                            @if($policy->documents->count() > 0)
                                @foreach($policy->documents as $document)
                                    <div class="file-card">
                                        <div class="file-icon">
                                            @php
                                                $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                                            @endphp
                                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <img src="{{ asset('storage/' . $document->file_path) }}" alt="{{ $document->name }}" class="object-cover rounded mb-2" />
                                            @elseif($extension == 'pdf')
                                                <i class="fas fa-file-pdf"></i>
                                            @elseif(in_array($extension, ['doc', 'docx']))
                                                <i class="fas fa-file-word"></i>
                                            @elseif(in_array($extension, ['xls', 'xlsx']))
                                                <i class="fas fa-file-excel"></i>
                                            @elseif($extension == 'csv')
                                                <i class="fas fa-file-csv"></i>
                                            @elseif($extension == 'zip')
                                                <i class="fas fa-file-archive"></i>
                                            @elseif($extension == 'folder')
                                                <i class="fas fa-folder"></i>
                                            @else
                                                <i class="fas fa-file-alt"></i>
                                            @endif
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ $document->name }}</h3>
                                        <div class="flex justify-between items-center">
                                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors duration-300 text-xs">
                                                <i class="fas fa-eye mr-1"></i>
                                            </a>
                                            <a href="{{ asset('storage/' . $document->file_path) }}" download class="text-green-600 hover:text-green-800 transition-colors duration-300 text-xs">
                                                <i class="fas fa-download mr-1"></i>
                                            </a>
                                            <button onclick="openDeleteModal({{ $document->id }})" class="text-red-600 hover:text-red-800 transition-colors duration-300">
                                                <i class="fas fa-trash-alt mr-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-600">No documents available for this policy.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Upload Document Modal -->
<div id="uploadDocumentModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Upload Policy Documents
                </h3>
                <div class="mt-2">
                    <form action="{{ route('upload.policy.documents') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="policy_id" value="{{ $policy->registration_number }}">
                        <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-12 cursor-pointer hover:border-blue-500 transition duration-300">
                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <label for="files" class="text-lg font-medium text-gray-700 mb-2">Drop files here or click to upload</label>
                            <input class="hidden" type="file" name="files[]" id="files" multiple required>
                            <p class="text-gray-500 text-sm">You can upload multiple files (jpg, jpeg, png, pdf)</p>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2" onclick="closeModal()">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                Upload Documents
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Document Confirmation Modal -->
<div id="deleteDocumentModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Confirm Delete Document
                </h3>
                <div class="mt-2">
                    <p class="text-gray-600">Are you sure you want to delete this document? This action cannot be undone.</p>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2" onclick="closeDeleteModal()">
                        Cancel
                    </button>
                    <button id="confirmDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                        Delete Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let documentIdToDelete;

    function openDeleteModal(documentId) {
        documentIdToDelete = documentId;
        document.getElementById('deleteDocumentModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteDocumentModal').classList.add('hidden');
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // Make an AJAX request to delete the document
        fetch(`/documents/${documentIdToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                // Remove the document from the UI or refresh the page
                location.reload(); // Reload the page to see the changes
            } else {
                alert('Failed to delete the document.');
            }
        })
        .catch(error => console.error('Error:', error));
        
        closeDeleteModal();
    });

    function openModal() {
        document.getElementById('uploadDocumentModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('uploadDocumentModal').classList.add('hidden');
    }

    document.getElementById('uploadDocumentBtn').addEventListener('click', openModal);
</script>
@endsection
