{{-- business information --}}
<div class="container">
    <div class="row">
        <div class="col-sm">
            @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <img class="center-block mt-5"
                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
            @endif
        </div>
        <div class="col-sm">
            <!-- Header text -->
            @if (!empty($receipt_details->header_text))
                <div class="col-xs-12">
                    {!! $receipt_details->header_text !!}
                </div>
            @endif
            @if ($receipt_details->show_barcode)
                {{-- Barcode --}}
                <img class="center-block"
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
            @endif
        </div>
        <div class="col-sm">
            <!-- Logo -->
            @if (!empty($receipt_details->logo))
                <img style="max-height: 120px; width: auto;" src="{{ $receipt_details->logo }}"
                    class="img img-responsive center-block">
            @endif
        </div>
    </div>
</div>


{{-- stor information --}}
<div class="row">
    <div class="col-6">.col-6</div>
    <div class="col-6">.col-6</div>
</div>

{{-- products didalis --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">First</th>
            <th scope="col">Last</th>
            <th scope="col">Handle</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row">1</th>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
        </tr>
        <tr>
            <th scope="row">2</th>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
        </tr>
    </tbody>
</table>


<div class="row">
    <div class="col-6">.col-6</div>
    <div class="col-6">.col-6</div>
</div>


<div class="row">
    <div class="col-6">.col-6</div>
    <div class="col-6">.col-6</div>
</div>
