@extends('layout')
@section('title')
<?= get_label('notes', 'Notes') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/home') }}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('notes', 'Notes') ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <span data-bs-toggle="modal" data-bs-target="#create_note_modal">
                <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_note', 'Create note') ?>">
                    <i class='bx bx-plus'></i>
                </a>
            </span>
        </div>
    </div>
    @if ($notes->count() > 0)
    <div class="card">
        <div class="card-body">
            <button type="button" id="delete-selected" class="btn btn-outline-danger mx-4" data-type="notes">
                <i class="bx bx-trash"></i> {{ get_label('delete_selected', 'Delete Selected')}}
            </button>
            <div class="form-check mt-3 mx-4">
                <input type="checkbox" id="select-all" class="form-check-input">
                <label for="select-all" class="form-check-label">{{ get_label('select_all', 'Select All') }}</label>
            </div>
            <div class="row sticky-notes mt-3">
                @foreach ($notes as $note)
                <div class="col-md-4 sticky-note">
                    <div class="sticky-content sticky-note-bg-<?= $note->color ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" class="ms-0 mx-2 selected-items" value="{{ $note->id }}">
                                <span class="note-id">#{{ $note->id }}</span>
                            </div>
                            <div class="text-end">
                                <a href="javascript:void(0);" class="btn btn-primary btn-xs edit-note" data-id="{{ $note->id }}" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="{{ get_label('update', 'Update') }}">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-danger btn-xs mx-1 delete" data-id="{{ $note->id }}" data-type="notes" data-reload="true" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="{{ get_label('delete', 'Delete') }}">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </div>

                        <h4><?= $note->title ?></h4>
                        <p><?= $note->description ?></p>
                        <b><?= get_label('created_at', 'Created at') ?> : </b><span class="text-primary">{{ format_date($note->created_at,true)}}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @else
    <?php
    $type = 'Notes';
    ?>
    <x-empty-state-card :type="$type" />
    @endif
</div>
@endsection