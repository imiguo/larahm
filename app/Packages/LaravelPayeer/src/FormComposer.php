<?php

namespace entimm\LaravelPayeer;

use Illuminate\View\View;

/**
 * Class FormComposer
 *
 * @package \entimm\LaravelPayeer
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
            'shop_id' => config('payeer.shop_id'),
            'shop_sign' => config('payeer.shop_sign'),
            'currency' => config('payeer.currency'),

            'global_memo' => config('payeer.description'),
        ];
        $view->with($viewData);
    }

}
