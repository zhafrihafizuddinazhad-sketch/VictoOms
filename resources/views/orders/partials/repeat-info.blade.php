@if($order->is_repeat_order && $order->originalOrder)

    <div class="card border-info mt-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-sync-alt"></i>

                Repeat Order

            </h5>

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <strong>
                        Repeat From
                    </strong>

                    <p class="mb-0">

                        <a
                            href="{{ route('orders.show', $order->originalOrder) }}">

                            {{ $order->originalOrder->order_no }}

                        </a>

                    </p>

                </div>


                <div class="col-md-4">

                    <strong>
                        Repeat Type
                    </strong>

                    <p class="mb-0">

                        {{ ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $order->repeat_type
                            )
                        ) }}

                    </p>

                </div>


                <div class="col-md-4">

                    <strong>
                        Designer
                    </strong>

                    <p class="mb-0">

                        {{ $order->designer->name ?? 'Not Assigned' }}

                    </p>

                </div>

            </div>


            <hr>


            <h6>

                <i class="fas fa-palette"></i>

                Original Design

            </h6>


            @if($order->originalOrder->designFiles->count())

                <div class="list-group">

                    @foreach(
                        $order->originalOrder->designFiles
                        as $designFile
                    )

                        <div
                            class="list-group-item d-flex justify-content-between align-items-center">

                            <div>

                                <strong>

                                    {{ $designFile->file_name }}

                                </strong>

                                <br>

                                <small class="text-muted">

                                    Version
                                    {{ $designFile->version }}

                                    @if($designFile->uploader)

                                        · Uploaded by
                                        {{ $designFile->uploader->name }}

                                    @endif

                                </small>

                            </div>


                            <div>

                                <a
                                    href="{{ route(
                                        'designs.preview',
                                        $designFile
                                    ) }}"
                                    class="btn btn-outline-primary btn-sm">

                                    <i class="fas fa-eye"></i>

                                    View

                                </a>


                                <a
                                    href="{{ route(
                                        'designs.download',
                                        $designFile
                                    ) }}"
                                    class="btn btn-outline-secondary btn-sm">

                                    <i class="fas fa-download"></i>

                                    Download

                                </a>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="alert alert-warning mb-0">

                    No design file was found in the original order.

                </div>

            @endif

        </div>

    </div>

@endif