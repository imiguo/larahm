<?php

namespace entimm\LaravelAsmoney;

abstract class APIerror
{
    const OK = 0;
    const InvalidUser = 1;
    const InvalidAPIData = 2;
    const InvalidIP = 3;
    const InvalidIPSetup = 4;
    const InvalidCurrency = 5;
    const InvalidReceiver = 6;
    const NotEnoughMoney = 7;
    const APILimitReached = 8;
    const Invalid = 9;
}
