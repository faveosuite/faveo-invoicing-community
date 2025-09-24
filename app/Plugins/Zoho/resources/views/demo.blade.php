@extends('themes.default1.layouts.master')

@section('content')
    <div class="container mt-4">
        <h3>Zoho Integration – Demo Event Triggers</h3>

        <div class="card mt-3">
            <div class="card-body">

                {{-- Product Selector --}}
                <div class="form-group mb-3">
                    <label for="product_id">Select Product (for Purchase Events)</label>

                    <select id="product_id" class="form-control">
                        <option value="">-- Select Product --</option>

                        @if($freeProducts->count())
                            <optgroup label="Free Products">
                                @foreach($freeProducts as $product)
                                    <option value="{{ $product->id }}" data-type="free">
                                        {{ $product->name }} (Free)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($paidProducts->count())
                            <optgroup label="Paid Products">
                                @foreach($paidProducts as $product)
                                    <option value="{{ $product->id }}" data-type="paid">
                                        {{ $product->name }} (Paid)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>


                <button class="btn btn-primary trigger-event" data-event="register">
                    Trigger Register Event
                </button>

                <button class="btn btn-info trigger-event" data-event="newsletter">
                    Trigger Newsletter Subscribe Event
                </button>

                <button class="btn btn-success trigger-event" data-event="purchase">
                    Trigger Product Purchase
                </button>

            </div>
        </div>
    </div>

    <script>
        $('.trigger-event').on('click', function () {
            let eventType   = $(this).data('event');
            let productEl  = $('#product_id option:selected');
            let productId  = productEl.val();
            let productType = productEl.data('type');

            if (eventType === 'purchase' && !productId) {
                alert('Please select a product');
                return;
            }

            $.post("{{ url('zoho/testEvent') }}", {
                _token: "{{ csrf_token() }}",
                event: eventType,
                product_id: productId,
                product_type: productType // 👈 SEND THIS
            }, function (response) {
                alert(response.message);
            }).fail(function () {
                alert('Something went wrong');
            });
        });
    </script>
@endsection
