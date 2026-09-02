<div class="card mt-3">

    <div class="card-header">

        <h4>Product Photos</h4>

    </div>


    <div class="card-body">

        @if($order->productPhotos->isEmpty())

            <div class="alert alert-secondary mb-0">

                No product photos uploaded yet.

            </div>

        @else

            <div class="row">

                @foreach($order->productPhotos as $photo)

                    <div class="col-md-3 mb-3">

                        <div class="card">

                            <img
                                src="{{ asset('storage/'.$photo->photo_path) }}"
                                class="card-img-top preview-image"
                                style="height:220px;object-fit:cover;cursor:pointer;"
                                data-image="{{ asset('storage/'.$photo->photo_path) }}"
                            >

                            <div class="card-body">

                                <small class="fw-bold d-block">

                                    {{ $photo->photo_name }}

                                </small>


                                @if($photo->remarks)

                                    <small class="text-muted">

                                        {{ $photo->remarks }}

                                    </small>

                                @endif

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>