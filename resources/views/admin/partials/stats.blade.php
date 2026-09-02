{{-- ================================================= --}}
{{-- ADMIN KPI --}}
{{-- ================================================= --}}

<div class="row">

    {{-- Today's Orders --}}
    <div class="col-md-3">

        <div class="small-box bg-info">

            <div class="inner">

                <h3>
                    {{ $stats['todayOrders'] }}
                </h3>

                <p>
                    Today's Orders
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-shopping-cart"></i>

            </div>

        </div>

    </div>


    {{-- Active Orders --}}
    <div class="col-md-3">

        <div class="small-box bg-primary">

            <div class="inner">

                <h3>
                    {{ $stats['activeOrders'] }}
                </h3>

                <p>
                    Active Orders
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-spinner"></i>

            </div>

        </div>

    </div>


    {{-- Due Soon --}}
    <div class="col-md-3">

        <div class="small-box bg-warning">

            <div class="inner">

                <h3>
                    {{ $stats['dueSoon'] }}
                </h3>

                <p>
                    Due Within 3 Days
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-calendar-alt"></i>

            </div>

        </div>

    </div>


    {{-- Needs Attention --}}
    <div class="col-md-3">

        <div class="small-box bg-danger">

            <div class="inner">

                <h3>
                    {{ $stats['needsAttention'] }}
                </h3>

                <p>
                    Needs Attention
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-exclamation-triangle"></i>

            </div>

        </div>

    </div>

</div>