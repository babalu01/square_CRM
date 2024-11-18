@extends('layout')

@section('content')
@php
$user = getAuthenticatedUser();
@endphp
{{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> --}}
{{-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> --}}
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Grid Upload Log</h1>
        @if($user->can('create_grid'))
        <button onclick="openUploadModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload New
        </button>
        @endif
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden table-responsive">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- @dd($gridUploadLog); --}}
                @foreach($gridUploadLog as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $log->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $log->upload_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $log->policiesCompany->company_name ?? "" }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="badge {{ $log->activation_status == 1 ? 'bg-success' : 'bg-danger' }} cursor-pointer" data-toggle="modal" data-target="#exampleModal{{ $log->id }}">
                            {{ $log->activation_status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_month }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('commission.rates', $log->upload_id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                    @if($user->can('delete_grid'))
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="{{ route('delete.commission.log', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="upload_id" value="{{$log->upload_id}}">
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
                @endif
                </tr>


                {{-- Modal for confirmation --}}
                <div class="modal fade" id="exampleModal{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Confirm Status Change</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to change the status of this log?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <form action="{{ route('update.grid.status') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="log_id" value="{{ $log->id }}">
                                    <input type="hidden" name="company_id" value="{{ $log->comany_name }}">
                                    <button type="submit" class="btn btn-primary">Confirm</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- End of Modal for confirmation --}}
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Upload New File
                </h3>
                <div class="mt-2">
                    <form action="{{ route('import.commissionRates') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                    
                        <div class="mb-4">
                            <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Select Company</label>
                            <select id="company" name="company" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                <option value="">Select a company</option>
                                <option value="Future Generali India Insurance Company">Future Generali India Insurance Company</option>
                                <option value="TATA">Tata AIG General Insurance Co. Ltd.</option>
                                <option value="CHOLA">Cholamandalam MS General Insurance Co. Ltd.</option>
                                <option value="LIBERTY">Liberty General Insurance Ltd.</option>
                                <option value="BAJAJ">Bajaj Allianz General Insurance Co. Ltd.</option>
                                <option value="DIGIT">Go Digit General Insurance Ltd.</option>
                                <option value="SHRIRAM">Shriram General Insurance Co. Ltd.</option>
                                <option value="ROYAL">Royal Sundaram General Insurance Co. Ltd.</option>
                                <option value="Universal Sompo General Insurance Co. Ltd.">Universal Sompo General Insurance Co. Ltd.</option>
                                <option value="MAGMA">Magma HDI General Insurance Co. Ltd.</option>
                                <option value="ICICI Lombard General Insurance Co. Ltd.">ICICI Lombard General Insurance Co. Ltd.</option>
                                <option value="SBI">SBI General Insurance Co. Ltd.</option>
                                <option value="United India Insurance Co. Ltd.">United India Insurance Co. Ltd.</option>
                                <option value="Iffco Tokio General Insurance Co. Ltd.">Iffco Tokio General Insurance Co. Ltd.</option>
                                <option value="HDFC Ergo General Insurance Co. Ltd.">HDFC Ergo General Insurance Co. Ltd.</option>
                                <option value="National Insurance Co. Ltd.">National Insurance Co. Ltd.</option>
                                <option value="RELIANCE">Reliance General Insurance Co. Ltd.</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Choose Excel File</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="file" name="file" type="file" class="sr-only" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Excel files only
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6">
                            <button type="submit" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeUploadModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }
</script>
@endsection
