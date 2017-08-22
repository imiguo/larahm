<form action="https://payeer.com/merchant/" method="POST" id="payeer-form">
    <input type="hidden" name="m_shop" value="{{ $shop_id }}">
    <input type="hidden" name="m_orderid" value="{{ $order_id }}">

    @if (! empty($amount))
        <input type="hidden" name="m_amount" value="{{ $amount }}">
    @else
        <div class="form-group">
            <label for="payment_amount">Payment Amount</label>
            <input id="payment_amount" type="number" name="m_amount" value="" required autofocus>
        </div>
    @endif

    <input type="hidden" name="m_curr" value="{{ $currency }}">
    <input type="hidden" name="m_desc" value="{{ $memo or $global_memo }}">
    <input type="hidden" name="m_sign" value="{{ $shop_sign }}">

    <button type="submit" class="btn btn-primary"> Proceed </button>

</form>