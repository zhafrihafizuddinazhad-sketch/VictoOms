<div class="card mt-3">

    <div class="card-header">

        <h4>Actions</h4>

    </div>


    <div class="card-body">


        {{-- ================================================= --}}
        {{-- PENDING --}}
        {{-- ================================================= --}}

        @if($order->status == 'Pending')

            <span class="badge bg-warning">

                Waiting for Designer Assignment

            </span>


        {{-- ================================================= --}}
        {{-- ASSIGNED --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Assigned')

            <span class="badge bg-info">

                Waiting for Designer to Start Task

            </span>


        {{-- ================================================= --}}
        {{-- IN PROGRESS --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'In Progress')

            <span class="badge bg-primary">

                Designer is Working

            </span>


        {{-- ================================================= --}}
        {{-- PENDING APPROVAL --}}
        {{-- ================================================= --}}

        {{-- ================================================= --}}
{{-- PENDING APPROVAL --}}
{{-- ================================================= --}}

@elseif($order->status == 'Pending Approval')

    @if(auth()->user()->hasRole('owner'))

        <div class="border border-warning rounded">

            {{-- OWNER REVIEW HEADER --}}
            <div class="bg-warning p-3">

                <h5 class="mb-0">

                    <i class="fas fa-search mr-1"></i>

                    Owner Review

                </h5>

            </div>


            {{-- CONTENT --}}
            <div class="p-3">

                <p class="text-muted mb-4">

                    Review the latest uploaded design before
                    approving it for printing or requesting a revision.

                </p>


                {{-- ============================================= --}}
                {{-- APPROVE DESIGN --}}
                {{-- ============================================= --}}

                <form
                    action="{{ route('orders.approve', $order) }}"
                    method="POST"
                    class="mb-4"
                >

                    @csrf

                    @method('PATCH')


                    <div class="form-group">

                        <label>

                            Review Comment

                            <span class="text-muted">

                                (Optional)

                            </span>

                        </label>


                        <textarea
                            name="owner_review_comment"
                            class="form-control"
                            rows="3"
                            placeholder="Optional comment about the approved design"
                        >{{ old('owner_review_comment') }}</textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Approve this design and send the order to printing?')"
                    >

                        <i class="fas fa-check mr-1"></i>

                        Approve Design

                    </button>

                </form>


                <hr>


                {{-- ============================================= --}}
                {{-- REQUEST REVISION --}}
                {{-- ============================================= --}}

                <form
                    action="{{ route('orders.revision', $order) }}"
                    method="POST"
                >

                    @csrf

                    @method('PATCH')


                    <div class="form-group">

                        <label>

                            Revision Reason

                            <span class="text-danger">

                                *

                            </span>

                        </label>


                        <textarea
                            name="owner_review_comment"
                            class="form-control"
                            rows="4"
                            required
                            placeholder="Explain what the designer needs to change..."
                        >{{ old('owner_review_comment') }}</textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-danger"
                        onclick="return confirm('Request revision for this design?')"
                    >

                        <i class="fas fa-undo mr-1"></i>

                        Request Revision

                    </button>

                </form>


                {{-- ============================================= --}}
                {{-- INFORMATION --}}
                {{-- ============================================= --}}

                <div class="alert alert-info mt-4 mb-0">

                    <i class="fas fa-info-circle mr-1"></i>

                    <strong>Approve:</strong>

                    The order will move to Printing.

                    <br>

                    <strong>Request Revision:</strong>

                    The order will return to In Progress and the
                    designer will receive the revision reason.

                </div>

            </div>

        </div>


    @else

        <div class="alert alert-warning mb-0">

            <i class="fas fa-clock mr-1"></i>

            <strong>

                Waiting for Owner Approval

            </strong>

            <br>

            This order has been submitted for approval and is
            currently waiting for the Owner to review the design.

        </div>

    @endif


        {{-- ================================================= --}}
        {{-- PRINTING --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Printing')


            <form
                action="{{ route('orders.readyHQ', $order) }}"
                method="POST"
            >

                @csrf

                @method('PATCH')


                <button class="btn btn-dark">

                    <i class="fas fa-warehouse"></i>

                    Mark as Ready at HQ

                </button>

            </form>


        {{-- ================================================= --}}
        {{-- READY AT HQ --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Ready at HQ')


            @if(!$order->cameraman_id)

                <div class="card border-danger">

                    <div class="card-header bg-danger text-white">

                        <strong>

                            Assign Cameraman

                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="alert alert-warning">

                            This order is ready at HQ but no cameraman
                            has been assigned yet.

                        </div>


                        <form
                            action="{{ route('orders.assignCameraman', $order) }}"
                            method="POST"
                        >

                            @csrf

                            @method('PATCH')


                            <div class="mb-3">

                                <label>

                                    Select Cameraman

                                </label>


                                <select
                                    name="cameraman_id"
                                    class="form-control"
                                    required
                                >

                                    <option value="">

                                        -- Select Cameraman --

                                    </option>


                                    @foreach($cameramen as $cameraman)

                                        <option value="{{ $cameraman->id }}">

                                            {{ $cameraman->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <button class="btn btn-primary">

                                <i class="fas fa-camera"></i>

                                Assign Cameraman

                            </button>

                        </form>

                    </div>

                </div>


            @else

                <div class="alert alert-success">

                    <i class="fas fa-camera"></i>

                    Cameraman Assigned:

                    <strong>

                        {{ $order->cameraman->name }}

                    </strong>

                </div>

            @endif


        {{-- ================================================= --}}
        {{-- PHOTO SESSION --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Photo Session')


            <div class="alert alert-info mb-0">

                <i class="fas fa-camera-retro"></i>

                <strong>

                    Photo Session in Progress.

                </strong>

                Waiting for cameraman to complete the photo session.

            </div>


        {{-- ================================================= --}}
        {{-- PHOTO COMPLETED --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Photo Completed')


            @if($order->delivery_method == 'Delivery')


                <form
                    action="{{ route('orders.dispatch', $order) }}"
                    method="POST"
                >

                    @csrf

                    @method('PATCH')


                    <button class="btn btn-primary">

                        <i class="fas fa-truck"></i>

                        Dispatch Delivery

                    </button>

                </form>


            @else


                <form
                    action="{{ route('orders.readyPickup', $order) }}"
                    method="POST"
                >

                    @csrf

                    @method('PATCH')


                    <button class="btn btn-success">

                        <i class="fas fa-box-open"></i>

                        Ready for Pickup

                    </button>

                </form>


            @endif


        {{-- ================================================= --}}
        {{-- OUT FOR DELIVERY --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Out for Delivery')


            <form
                action="{{ route('orders.markDelivered', $order) }}"
                method="POST"
            >

                @csrf

                @method('PATCH')


                <button class="btn btn-success">

                    <i class="fas fa-check-circle"></i>

                    Mark as Delivered

                </button>

            </form>


        {{-- ================================================= --}}
        {{-- WAITING FOR PICKUP --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Waiting for Pickup')


            <form
                action="{{ route('orders.confirmPickup', $order) }}"
                method="POST"
            >

                @csrf

                @method('PATCH')


                <button class="btn btn-success">

                    <i class="fas fa-handshake"></i>

                    Confirm Pickup

                </button>

            </form>


        {{-- ================================================= --}}
        {{-- COMPLETED --}}
        {{-- ================================================= --}}

        @elseif($order->status == 'Completed')


            <div class="d-flex align-items-center flex-wrap">

                <span class="badge bg-success mr-2 mb-2">

                    Order Completed

                </span>


                <a
                    href="{{ route('orders.repeat', $order) }}"
                    class="btn btn-primary mb-2"
                >

                    <i class="fas fa-redo"></i>

                    Create Repeat Order

                </a>

            </div>


        @endif


    </div>

</div>