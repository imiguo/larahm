<form action="https://www.asmoney.com/sci.aspx" method="post">
    <input type="hidden" name="USER_NAME" value="{{ $user_name }}"/>
    <input type="hidden" name="STORE_NAME" value="{{ $store_name }}"/>
    <input type="hidden" name="PAYMENT_UNITS" value="{{ $payment_units }}"/>
    <input type="hidden" name="PAYMENT_AMOUNT" value="{{ $amount }}"/>
    <input type="hidden" name="PAYMENT_ID" value="{{ $payment_id }}"/>
    @if ($payment_method)
        <input type="hidden" name="PAYMENT_METHOD" value="{{ $payment_method }}"/>
    @endif
    <input type="hidden" name="PAYMENT_MEMO" value="{{ $memo }}"/>
    <input type="submit" value="PAY"/>
</form>