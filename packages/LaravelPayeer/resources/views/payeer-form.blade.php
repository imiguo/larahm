<form action="https://payeer.com/merchant/" method="POST" id="payeer-form">
    <input type="hidden" name="m_shop" value="{{ $shop_id }}">
    <input type="hidden" name="m_orderid" value="{{ $order_id }}">
    <input type="hidden" name="m_amount" value="{{ $amount }}">
    <input type="hidden" name="m_curr" value="{{ $currency }}">
    <input type="hidden" name="m_desc" value="{{ $memo }}">
    <input type="hidden" name="m_sign" value="{{ $shop_sign }}">
    <button type="submit" class="btn btn-primary"> Proceed </button>
</form>
