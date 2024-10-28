@extends('layout')
@section('title')
<?= get_label('client_profile', 'Client profile') ?>
@endsection
@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between mb-4 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{url('home')}}" class="text-blue-600 hover:underline"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{url('clients')}}" class="text-blue-600 hover:underline"><?= get_label('clients', 'Clients') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <?= $client->first_name . ' ' . $client->last_name; ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <img src="{{$client->photo ? asset('storage/' . $client->photo) : asset('/profiles/1.png')}}" alt="client-avatar" class="rounded-full h-24 w-24" id="uploadedAvatar" />
                        <div>
                            <h4 class="text-xl font-bold">{{ $client->first_name }} {{$client->last_name}}</h4>
                            <?= $client->status == 1 ? '<span class="badge bg-green-500 text-white">' . get_label('active', 'Active') . '</span>' : '<span class="badge bg-red-500 text-white">' . get_label('deactive', 'Deactive') . '</span>' ?>
                        </div>
                    </div>
                </div>
                <hr class="my-2" />
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-3">
                            <label class="form-label"><?= get_label('Client ID', 'Client Id') ?></label>
                           <input type="text " class="form-control" value="{{$client->client_id}}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?></label>
                            <input type="tel" name="phone" id="phone" class="form-control" value="{{$client->phone}}" readonly>
                            <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ $client->country_iso_code }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email"><?= get_label('email', 'E-mail') ?></label>
                            <input class="form-control" type="text" id="exampleFormControlReadOnlyInput1" value="{{$client->email}}" readonly="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address"><?= get_label('address', 'Address') ?></label>
                            <input class="form-control" type="text" id="address" value="{{$client->address??'-'}}" readonly="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="city"><?= get_label('city', 'City') ?></label>
                            <input class="form-control" type="text" id="city" value="{{$client->city??'-'}}" readonly="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="state"><?= get_label('state', 'State') ?></label>
                            <input class="form-control" type="text" id="state" value="{{$client->state??'-'}}" readonly="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="country"><?= get_label('country', 'Country') ?></label>
                            <input class="form-control" type="text" id="country" value="{{$client->country??'-'}}" readonly="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="zip"><?= get_label('zip_code', 'Zip code') ?></label>
                            <input class="form-control" type="text" id="zip" value="{{$client->zip??'-'}}" readonly="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition-all duration-300 focus:outline-none" id="policies-tab" data-tabs-target="#policies" type="button" role="tab" aria-controls="policies" aria-selected="true">Policies</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition-all duration-300 focus:outline-none" id="documents-tab" data-tabs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition-all duration-300 focus:outline-none" id="billing-tab" data-tabs-target="#billing" type="button" role="tab" aria-controls="billing" aria-selected="false">Billing</button>
                </li>
            </ul>
        </div>
        <div id="myTabContent" class="bg-white rounded-lg shadow-md">
            <div class="p-6 rounded-lg" id="policies" role="tabpanel" aria-labelledby="policies-tab">
                <div class="overflow-x-auto relative">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 bg-light border-1">
                            <tr>
                                <th scope="col" class="py-3 px-6 font-semibold">Policy Number</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Type</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Start Date</th>
                                <th scope="col" class="py-3 px-6 font-semibold">End Date</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                            <tr class="bg-white border-b hover:bg-gray-50 transition-all duration-200">
                                <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                <a target="_blank" href="{{ route('policies.show', $policy->id) }}">{{ $policy->policy_number }}</a>
                                </th>
                                <td class="py-4 px-6">
                                    {{ $policy->type }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $policy->start_date }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $policy->end_date }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                        @if($policy->status == 'Active') bg-green-100 text-green-800
                                        @elseif($policy->status == 'Inactive') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ $policy->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="hidden p-6 rounded-lg" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="p-4">
                    @if($clientdocuments->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($clientdocuments as $document)
                                <div class="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $document->name }}</h3>
                                        <div class="aspect-w-16 aspect-h-9 mb-4">
                                            @php
                                                $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                                            @endphp
                                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <img src="{{ asset('storage/' . $document->file_path) }}" alt="{{ $document->name }}" class="object-cover rounded" />
                                            @elseif($extension == 'pdf')
                                                <iframe src="{{ asset('storage/' . $document->file_path) }}" class="w-full h-full rounded"></iframe>
                                            @else
                                                <div class="flex items-center justify-center h-full bg-gray-200 rounded">
                                                    <i class="fas fa-file-alt text-4xl text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors duration-300">
                                                <i class="fas fa-eye mr-2"></i>View
                                            </a>
                                            <a href="{{ asset('storage/' . $document->file_path) }}" download class="text-green-600 hover:text-green-800 transition-colors duration-300">
                                                <i class="fas fa-download mr-2"></i>Download
                                            </a>
                                          
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">No documents available for this policy.</p>
                    @endif
                </div>
            </div>
            <div class="hidden p-6 rounded-lg" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                <p class="text-sm text-gray-500">Billing content here</p>
            </div>
        </div>
    </div>

<script>
    // JavaScript to handle tab switching
    document.querySelectorAll('#myTab button').forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            document.querySelectorAll('#myTab button').forEach(btn => btn.classList.remove('active'));
            // Hide all tab content
            document.querySelectorAll('#myTabContent > div').forEach(tab => tab.classList.add('hidden'));
            
            // Add active class to the clicked button
            button.classList.add('active');
            // Show the corresponding tab content
            const target = button.getAttribute('data-tabs-target');
            document.querySelector(target).classList.remove('hidden');
        });
    });
</script>
@endsection
