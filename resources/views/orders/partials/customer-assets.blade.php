{{-- ========================================================= --}}
{{-- CUSTOMER BRIEF --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="fas fa-comments"></i>

            Customer Brief

        </h5>

    </div>

    <div class="card-body">

        @if($order->customer_brief)

            <div class="p-3 bg-light rounded">

                {!! nl2br(e($order->customer_brief)) !!}

            </div>

        @else

            <div class="alert alert-secondary mb-0">

                No customer brief provided.

            </div>

        @endif

    </div>

</div>

{{-- ========================================================= --}}
{{-- CUSTOMER ASSETS --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-warning">

        <h5 class="mb-0">

            <i class="fas fa-paperclip"></i>

            Customer Assets

        </h5>

    </div>

    <div class="card-body">

        <h6 class="fw-bold mb-3">

            <i class="fas fa-folder-open text-primary"></i>

            Reference Files

        </h6>

        @php

            $files = $order->references->whereNotNull('file_path');

        @endphp

        @if($files->count())

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead class="table-light">

                <tr>

                    <th>File Name</th>

                    <th width="120">Type</th>

                    <th width="220">Action</th>

                </tr>

            </thead>

            <tbody>

                @foreach($files as $reference)

                    <tr>

                        <td>

                            @php
                                $ext = strtolower($reference->file_extension);
                            @endphp

                            @if(in_array($ext,['jpg','jpeg','png']))
                                <i class="fas fa-image text-success me-2"></i>

                            @elseif($ext=='pdf')
                                <i class="fas fa-file-pdf text-danger me-2"></i>

                            @elseif(in_array($ext,['ai','eps','psd','cdr','svg']))
                                <i class="fas fa-palette text-warning me-2"></i>

                            @else
                                <i class="fas fa-file text-secondary me-2"></i>
                            @endif

                            {{ $reference->file_name }}

                        </td>

                        <td>

                            <span class="badge bg-secondary">

                                {{ strtoupper($ext) }}

                            </span>

                        </td>

                        <td>

                            @if(in_array($ext,['jpg','jpeg','png','pdf']))

                                <a href="{{ route('orders.references.preview',$reference) }}"
                                   target="_blank"
                                   class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                            @endif

                            <a href="{{ route('orders.references.download',$reference) }}"
                               class="btn btn-primary btn-sm">

                                <i class="fas fa-download"></i>

                            </a>

                            <form action="{{ route('orders.references.destroy',$reference) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this reference?')">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

@else

    <div class="alert alert-secondary mb-0">

        No reference files uploaded.

    </div>

@endif

<hr>

<h6 class="fw-bold mb-3">

    <i class="fas fa-link text-success"></i>

    Reference Links

</h6>

@php

    $links = $order->references->whereNotNull('reference_link');

@endphp

@if($links->count())

    <div class="list-group">

        @foreach($links as $reference)

            <a href="{{ $reference->reference_link }}"
               target="_blank"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">

                <span>

                    <i class="fas fa-link me-2 text-success"></i>

                    {{ $reference->reference_link }}

                </span>

                <span>

                    <i class="fas fa-external-link-alt"></i>

                </span>

            </a>

        @endforeach

    </div>

@else

    <div class="alert alert-secondary mb-0">

        No reference links available.

    </div>

@endif

    </div>

</div>