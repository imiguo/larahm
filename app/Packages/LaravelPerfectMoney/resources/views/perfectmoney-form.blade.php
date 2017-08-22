<form action="https://perfectmoney.is/api/step1.asp" method="POST" id="perfectmoney-form">
    <input type="hidden" name="PAYEE_ACCOUNT" value="{{ $payee_account }}">
    <input type="hidden" name="PAYEE_NAME" value="{{ $payee_name }}">
    <input type="hidden" name="PAYMENT_AMOUNT" value="{{ $payment_amount }}">
    <input type="hidden" name="PAYMENT_UNITS" value="{{ $payment_units }}">
    <input type="hidden" name="PAYMENT_URL" value="{{ $payment_url }}">
    <input type="hidden" name="NOPAYMENT_URL" value="{{ $nopayment_url }}">
    @if (! empty($payment_id))
        <input type="hidden" name="PAYMENT_ID" value="{{ $payment_id }}">
    @endif
    @if ($status_url)
        <input type="hidden" name="STATUS_URL" value="{{ $status_url }}">
    @endif
    @if ($payment_url_method)
        <input type="hidden" name="PAYMENT_URL_METHOD" value="{{ $payment_url_method }}">
    @endif
    @if ( $nopayment_url_method )
        <input type="hidden" name="NOPAYMENT_URL_METHOD" value="{{ $nopayment_url_method }}">
    @endif

    <input type="hidden" name="SUGGESTED_MEMO" value="{{ $memo or $global_memo }}">

    <button type="submit" class="btn btn-primary"> Proceed </button>

</form>