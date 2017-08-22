<?php

namespace entimm\LaravelPerfectMoney;

use Illuminate\View\View;

/**
 * Class FormComposer
 *
 * @package \entimm\LaravelPerfectMoney
 */
class FormComposer
{
    /**
     * Bind data to the form view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $viewData = [
            'payee_account' => config('perfectmoney.marchant_id'),
            'payee_name' => config('perfectmoney.marchant_name'),
            'payment_units' => config('perfectmoney.units'),
            'status_url' => config('perfectmoney.status_url'),
            'payment_url' => config('perfectmoney.payment_url'),
            'nopayment_url' => config('perfectmoney.nopayment_url'),
            'payment_url_method' => config('perfectmoney.payment_url_method'),
            'nopayment_url_method' => config('perfectmoney.nopayment_url_method'),

            'global_memo' => config('perfectmoney.payment_memo'),
        ];
        $view->with($viewData);
    }

}