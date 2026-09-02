<div class="card">

    <div class="card-header">

        <h4>Products</h4>

    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>

                </tr>

            </thead>

            <tbody>

            @php
                $grandTotal = 0;
            @endphp

            @foreach($order->items as $item)

                @php
                    $grandTotal += $item->subtotal;
                @endphp

                <tr>

                    <td>{{ $item->product_name }}</td>

                    <td>{{ $item->quantity }}</td>

                    <td>RM {{ number_format($item->unit_price,2) }}</td>

                    <td>RM {{ number_format($item->subtotal,2) }}</td>

                </tr>

            @endforeach

            </tbody>

            <tfoot>

                <tr>

                    <th colspan="3" class="text-end">

                        Grand Total

                    </th>

                    <th>

                        RM {{ number_format($grandTotal,2) }}

                    </th>

                </tr>

            </tfoot>

        </table>

    </div>

</div>