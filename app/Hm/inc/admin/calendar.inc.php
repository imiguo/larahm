<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<br><br>
<b>Estimate users earnings:</b><br><br>
';
  echo '<s';
  echo 'cript>
var colorBlank       = \'#FFFFFF\';
var colorActive      = \'#FFFFFF\';
var colorActiveHover = \'#FFD39D\';
var colorPast        = \'#E2E2E2\';
var colorToday       = \'#FEAE68\';
var colorToDate      = \'#FFEA00\';
var colorPayDate     = \'#FBF7CC\';

var CurDate = new Date();
CurDate = new Date(CurDate.getFullYear(), CurDate.getMonth(), CurDate.getDate());
var ToDate = new Date(CurDate.getFullYear(), C';
  echo 'urDate.getMonth(), CurDate.getDate());
var WantDate = new Date(CurDate.getFullYear(), CurDate.getMonth(), CurDate.getDate());
var Percent = 0;
var Amount = 10;
var Deposit = 10;
var Diff = 0;
var FirstDay;
var LastDay;
var lastrow = \'\';

var percents = new Array;
percents[0] = new Array(10, 100, 2.2);
var paymentperiod = \'d\'; // \'d\' - daily, \'w\' - weekly, \'bw\' - beweekly, \'m\' - monthly, \'y\' - year';
  echo 'ly
var maxdays = 0;
var returnprofit = 0;
var compound = 0;

function CalculatePercent()
{
  Percent = 0;
  var LastPercent = percents[0][2];
  for (var i = 0; i < percents.length; i++)
  {
    if (Amount < percents[i][0])
    {
      Percent = LastPercent;
    }
    else
    {
      LastPercent = percents[i][2];
      if ((Amount >= percents[i][0]) && ((Amount <= percents[i][1]) || (percents[i][1] =';
  echo '= 0)))
      {
        Percent = percents[i][2];
      }
    }
  }
}

function CalculateDiff(row)
{
  var ToDate;
  var Diff;

  if (row)
  {
    var obj = document.getElementById(row);
    tToDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);
    if (maxdays > 0 && Math.ceil((tToDate.getTime() - CurDate.getTime())/(24*60*60*1000)) > maxdays)
    {
 ';
  echo '     return new Array(tToDate, -1);
    }
    ToDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);
  }
  else
  {
    ToDate = WantDate;
  }

  if (paymentperiod == \'w\')
  {
    Diff = Math.ceil((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000));
    Diff = Math.floor(Diff / 7) * 7;
    ToDate = new Date(CurDate.getTime() + Diff * (24*60*60*1000';
  echo ') + 2*60*60*1000);
    ToDate = new Date(ToDate.getFullYear(), ToDate.getMonth(), ToDate.getDate());

    Diff = Math.ceil((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000)) / 7;
  }
  else if (paymentperiod == \'w-d\')
  {
    wd = ToDate.getDay();
    if (wd == 6)
    {
      ToDate = new Date(ToDate.getTime() - (24*60*60*1000) + 2*60*60*1000);
    }
    if (wd == 0)
    {
      ToDate = new ';
  echo 'Date(ToDate.getTime() - 2 * (24*60*60*1000) + 2*60*60*1000);
    }
    Diff = Math.floor((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000));
    Weeks = Math.floor(Diff / 7);
    if (ToDate.getDay() < CurDate.getDay()) Weeks++;
    Diff -= Weeks * 2;

    ToDate = new Date(ToDate.getFullYear(), ToDate.getMonth(), ToDate.getDate());
  }
  else if (paymentperiod == \'b-w\')
  {
    Diff = Math.cei';
  echo 'l((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000));
    Diff = Math.floor(Diff / 14) * 14;
    ToDate = new Date(CurDate.getTime() + Diff * (24*60*60*1000) + 2*60*60*1000);
    ToDate = new Date(ToDate.getFullYear(), ToDate.getMonth(), ToDate.getDate());

    Diff = Math.ceil((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000)) / 14;
  }
  else if (paymentperiod == \'m\')
  {
    tLastDay ';
  echo '= GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    var day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    var month = ToDate.getMonth()
    if (ToDate.getDate() < day)
    {
      month--;
      tLastDay = GetDaysInMonth(month, document.data.years.value); 
      day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    }
    ToDate = new Date(document';
  echo '.data.years.value, month, day);

    Diff = (ToDate.getFullYear() - CurDate.getFullYear()) * 12 + ToDate.getMonth() - CurDate.getMonth();
  }
  else if (paymentperiod == \'2m\')
  {
    ToDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);

    ToDateMonth = ToDate.getMonth();
    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    ';
  echo 'if (ToDate.getDate() < CurDate.getDate() && ToDate.getDate() < tLastDay)
    {
      ToDateMonth -= 1;
    }
    var cmonths = Math.floor(((ToDate.getFullYear() - CurDate.getFullYear()) * 12 + ToDateMonth - CurDate.getMonth()) / 2) * 2;

    var cyears = Math.floor(cmonths / 12);
    cmonths -= cyears * 12;
    var month = CurDate.getMonth() + cmonths;
    if (month > 11)
    {
      month -= 12;
  ';
  echo '    cyears++;
    }

    ToDate = new Date(cyears + CurDate.getFullYear(), month, ToDate.getDate());

    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    var day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    if (ToDate.getDate() < day)
    {
      tLastDay = GetDaysInMonth(month, document.data.years.value); 
      day = (tLastDay < CurDate.getDate()) ? ';
  echo 'tLastDay : CurDate.getDate();
    }
    ToDate = new Date(cyears + CurDate.getFullYear(), month, day);

    Diff = (ToDate.getFullYear() - CurDate.getFullYear()) * 6 + Math.floor((ToDate.getMonth() - CurDate.getMonth())/2);
  }
  else if (paymentperiod == \'3m\')
  {
    ToDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);

    ToDateMonth = ToDate.get';
  echo 'Month();
    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    if (ToDate.getDate() < CurDate.getDate() && ToDate.getDate() < tLastDay)
    {
      ToDateMonth -= 1;
    }
    var cmonths = Math.floor(((ToDate.getFullYear() - CurDate.getFullYear()) * 12 + ToDateMonth - CurDate.getMonth()) / 3) * 3;

    var cyears = Math.floor(cmonths / 12);
    cmonths -= cyears * 12;
    var';
  echo ' month = CurDate.getMonth() + cmonths;
    if (month > 11)
    {
      month -= 12;
      cyears++;
    }

    ToDate = new Date(cyears + CurDate.getFullYear(), month, ToDate.getDate());

    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    var day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    if (ToDate.getDate() < day)
    {
      tLastDay = GetDaysIn';
  echo 'Month(month, document.data.years.value); 
      day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    }
    ToDate = new Date(cyears + CurDate.getFullYear(), month, day);

    Diff = (ToDate.getFullYear() - CurDate.getFullYear()) * 4 + Math.floor((ToDate.getMonth() - CurDate.getMonth())/3);
  }
  else if (paymentperiod == \'6m\')
  {
    ToDate = new Date(document.data.years.value,';
  echo ' document.data.monthes.value-1, obj.childNodes[0].data);

    ToDateMonth = ToDate.getMonth();
    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    if (ToDate.getDate() < CurDate.getDate() && ToDate.getDate() < tLastDay)
    {
      ToDateMonth -= 1;
    }
    var cmonths = Math.floor(((ToDate.getFullYear() - CurDate.getFullYear()) * 12 + ToDateMonth - CurDate.getMonth()) / 6';
  echo ') * 6;

    var cyears = Math.floor(cmonths / 12);
    cmonths -= cyears * 12;
    var month = CurDate.getMonth() + cmonths;
    if (month > 11)
    {
      month -= 12;
      cyears++;
    }

    ToDate = new Date(cyears + CurDate.getFullYear(), month, ToDate.getDate());

    tLastDay = GetDaysInMonth(ToDate.getMonth(), ToDate.getFullYear()); 
    var day = (tLastDay < CurDate.getDate()) ? tLastDa';
  echo 'y : CurDate.getDate();
    if (ToDate.getDate() < day)
    {
      tLastDay = GetDaysInMonth(month, document.data.years.value); 
      day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    }
    ToDate = new Date(cyears + CurDate.getFullYear(), month, day);

    Diff = (ToDate.getFullYear() - CurDate.getFullYear()) * 2 + Math.floor((ToDate.getMonth() - CurDate.getMonth())/6);
  }';
  echo '  else if (paymentperiod == \'y\')
  {
    year = (ToDate.getMonth() < CurDate.getMonth()) ? document.data.years.value - 1 : document.data.years.value;

    ToDate = new Date(year, document.data.monthes.value-1, ToDate.getDate());
    tLastDay = GetDaysInMonth(CurDate.getMonth(), year); 
    day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    year = (CurDate.getMonth() == ToDate.g';
  echo 'etMonth() && ToDate.getDate() < day) ? document.data.years.value - 1 : document.data.years.value;
    month = CurDate.getMonth();
    if (ToDate.getDate() < day)
    {
      tLastDay = GetDaysInMonth(month, year); 
      day = (tLastDay < CurDate.getDate()) ? tLastDay : CurDate.getDate();
    }
    ToDate = new Date(year, month, day);

    Diff = (ToDate.getFullYear() - CurDate.getFullYear());
  }
  ';
  echo 'else if (paymentperiod == \'end\')
  {
    Diff = 1;
    tToDate = new Date(CurDate.getTime() + maxdays * (24*60*60*1000) + 2*60*60*1000);
    if (Math.ceil((ToDate.getTime() - CurDate.getTime())/(24*60*60*1000)) < maxdays)
    {
      Diff = 0;
      ToDate = CurDate;
    }

    ToDate = new Date(ToDate.getFullYear(), ToDate.getMonth(), ToDate.getDate());
  }
  else
  {
    Diff = Math.ceil((ToDate.';
  echo 'getTime() - CurDate.getTime())/(24*60*60*1000));
  }

  if (ToDate)
  {
    return new Array(ToDate, Diff);
  }
}

function UpdateRates()
{
  percents = new Array();
  var j = 0;
  for (i = 0; i < 20; i++)
  {
    if (document.nform.elements["rate_amount_active["+i+"]"])
    {
      if (document.nform.elements["rate_amount_active["+i+"]"].checked)
      {
        percents[j] = new Array(document.nf';
  echo 'orm.elements["rate_min_amount["+i+"]"].value, document.nform.elements["rate_max_amount["+i+"]"].value, document.nform.elements["rate_percent["+i+"]"].value);
        j++;
      }
    }
  }
  paymentperiod = document.nform.hperiod.value;
  if (paymentperiod == \'d\' && document.nform.work_week.checked)
  {
    paymentperiod = \'w-d\';
  }

  maxdays = document.nform.hq_days.value;
  if (document.nform.';
  echo 'hq_days_nolimit.checked)
  {
    maxdays = 0;
  }
  returnprofit = (document.nform.hreturn_profit.checked) ? 1 : 0;
  compound = (document.nform.use_compound.checked) ? 1 : 0;
}

function CalculateProfit(row) 
{
  UpdateRates();
  if (row)
  {
    obj = document.getElementById(row);
    if (!obj || !obj.childNodes[0].data) return;
    WantDate = new Date(document.data.years.value, document.data.mo';
  echo 'nthes.value-1, obj.childNodes[0].data);
  }

  var t = CalculateDiff(row);
  if (t)
  {
    ToDate = t[0];
    Diff = t[1];
  }

  if (Diff < 0)
  {
    return;
  }

  if (row)
  {
    if (lastrow)
    {
      obj = document.getElementById(lastrow);

      if (obj)
      {
        tDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);
        tDiff = Mat';
  echo 'h.ceil((tDate.getTime() - CurDate.getTime())/(24*60*60*1000));
        if (tDiff > 0)
        {
          var t = CalculateDiff(lastrow);
          tToDate = t[0];
          tDiff = t[1];
          if (tToDate.getTime() == tDate.getTime())
          {
            obj.style.backgroundColor = colorPayDate;
          }
          else
          {
            obj.style.backgroundColor = colorActive;
  ';
  echo '        }
        }
      }
    }
    if (ToDate.getTime() != CurDate.getTime() && ToDate.getMonth()+1 == document.data.monthes.value && ToDate.getFullYear() == document.data.years.value)
    {
      point = FirstDay - 1 + ToDate.getDate();
      lastrow = "td"+point;

      obj = document.getElementById("td"+point);
      obj.style.backgroundColor = colorToDate;
      obj.alt = colorToDate;
    }';
  echo '
  }

  document.getElementById(\'to\').childNodes[0].data = WantDate.getMonth()+1 + \'/\' + WantDate.getDate() + \'/\' + WantDate.getFullYear();
  document.getElementById(\'days\').childNodes[0].data = Diff;

  Amount = new Number(document.data.amount.value);
  CalculatePercent();

  if (Percent == 0)
  {
    if (Amount < percents[0][0])
    {
      alert(\'Provided amount is too small. \' + percents[0][0] ';
  echo '+ \' is minimal!\');
      document.data.amount.value = percents[0][0];
      CalculateProfit(row);
    }
    else if (percents[percents.length-1][1] != 0 && Amount > percents[percents.length-1][1])
    {
      alert(\'Provided amount is too large. \' + percents[percents.length-1][1] + \' is miximum!\');
      document.data.amount.value = percents[percents.length-1][1];
      CalculateProfit(row);
    }';
  echo '
    else
    {
      alert(\'Provided amount does not meet any range\');
    }
    return;
  }

  document.getElementById(\'percent\').childNodes[0].data = Percent + \'%\';
  var Profit = 0;

  if (compound)
  {
    var CompoundPercent = new Number(document.data.compounding_percent.value);
    CompoundPercent = (CompoundPercent / 100);
    Percent = Percent / 100;
    if ((CompoundPercent > 1) || (Comp';
  echo 'oundPercent < 0))
    {
      alert(\'Compounding Percent should be from 0 to 100\');
      return;
    }
    Deposit = Math.round(Amount * Math.pow((1 + Percent * CompoundPercent), Diff) * 10000) / 10000;

    for (i = 1; i <= Diff; i++)
    {
      Profit += Amount * Math.pow(1 + Percent * CompoundPercent, i-1);
    }

    Profit = Math.round(Profit * Percent * (1 - CompoundPercent) * 100) / 100;
  ';
  echo '}
  else
  {
    Deposit = Amount;
    Profit = Math.round(Amount * Percent * Diff) / 100;
  }

  if (returnprofit)
  {
    day = ToDate.getDate();
    if (row)
    {
      obj = document.getElementById(row);
      day = obj.childNodes[0].data;
    }

    tDiff = Math.ceil((WantDate.getTime() - CurDate.getTime())/(24*60*60*1000));
    if (tDiff == maxdays)
    {
      Profit += Deposit;
      Depo';
  echo 'sit = 0;
    }
  }
  Profit = Math.round(Profit * 100) / 100;

  document.getElementById(\'deposit\').childNodes[0].data = \'$\' + Deposit;
  document.getElementById(\'profit\').childNodes[0].data = \'$\' + Profit;
}

function GetDaysInMonth(Month, Year)
{
  var PrevDate = new Date(Year, Month+1, 0);
  return PrevDate.getDate();
}

function tdUpdateBg(row, flag) 
{
  obj = document.getElementById(row);

 ';
  echo ' tToDate = new Date(document.data.years.value, document.data.monthes.value-1, obj.childNodes[0].data);
  tDiff = Math.ceil((tToDate.getTime() - CurDate.getTime())/(24*60*60*1000));

  if (maxdays > 0 && Math.ceil((tToDate.getTime() - CurDate.getTime())/(24*60*60*1000)) > maxdays)
  {
    tDiff = -1;
  }

  if (obj.childNodes[0].data && tDiff > 0)
  {
    tDate = new Date(document.data.years.value,';
  echo ' document.data.monthes.value-1, obj.childNodes[0].data);
    var t = CalculateDiff(row);
    tToDate = t[0];
    tDiff = t[1];

    if (flag)
    {
      obj.alt = obj.style.backgroundColor;
      obj.style.backgroundColor = colorActiveHover;
    }
    else
    {
      if (tToDate.getTime() == tDate.getTime() && tDate.getTime() != ToDate.getTime())
      {
        obj.style.backgroundColor = color';
  echo 'PayDate;
      }
      else
      {
        obj.style.backgroundColor = obj.alt;
      }
    }
  }

}

function PrevMonth()
{
  var Month = document.data.monthes.selectedIndex - 1;
  var Year = document.data.years.value;
  if (Month < 0)
  {
    Month = 11;
    Year--;
  }
  if (Year - CurDate.getFullYear() < 0)
  {
    Month = CurDate.getMonth();
    Year = CurDate.getFullYear();
  }

  document.da';
  echo 'ta.monthes.options[Month].selected = true;
  document.data.years.options[Year - CurDate.getFullYear()].selected = true;
  InitCalendar(Month+1, Year);
}
function NextMonth()
{
  var Month = document.data.monthes.selectedIndex + 1;
  var Year = document.data.years.value;
  if (Month > 11)
  {
    Month = 0;
    Year++;
  }
  if (Year - CurDate.getFullYear() > 5)
  {
    Month = CurDate.getMonth();';
  echo '    Year = CurDate.getFullYear();
  }

  document.data.monthes.options[Month].selected = true;
  document.data.years.options[Year - CurDate.getFullYear()].selected = true;
  InitCalendar(Month+1, Year);
}

function InitCalendar(Month, Year)
{
  UpdateRates();
  if (!Month)
  {
    Month = document.data.monthes.value;
    Year = document.data.years.value;
  }
  Month--;
  var TDate = new Date(Year,';
  echo ' Month, 1);

  FirstDay = TDate.getDay();
  FirstDay++;
  if (FirstDay == 8) FirstDay = 1;
  LastDay = GetDaysInMonth(Month, Year);
  var d, w, obj;

  var aMonth = new Array();
  aMonth[0] = new Array(5);
  aMonth[1] = new Array(5);
  aMonth[2] = new Array(5);
  aMonth[3] = new Array(5);
  aMonth[4] = new Array(5);
  aMonth[5] = new Array(5);
  aMonth[6] = new Array(5);

  var VarDate = 1;
  var ';
  echo 'DateNum = 1;
  for (w = 0; w < 6; w++) 
  {
    for (d = 0; d < 7; d++)
    {
      if (VarDate < FirstDay)
      {
        VarDate++;
        continue;
      }
      if (DateNum <= LastDay)
      {
        aMonth[w][d] = DateNum;
        VarDate++;
        DateNum++;
      }
      else
      {
        aMonth[w][d] = \'x\';
      }
    }
  }
  for (w = 0; w < 6; w++)
  {
    for (d = 0; d < 7; d++)
    {';
  echo '      point = (7*w)+d+1;

      if (!isNaN(aMonth[w][d]))
      {
        obj = document.getElementById("td"+point);
        if (obj.childNodes.length == 0)
        {
          var txt = document.createTextNode(aMonth[w][d]);
          obj.appendChild(txt);
        }
        else
        {
          obj.childNodes[0].data = aMonth[w][d];
        }
        obj.style.backgroundColor = colorActive;
 ';
  echo '       obj.style.cursor = \'hand\';
      }
      else
      {
        obj = document.getElementById("td"+point);
        if (obj.childNodes.length == 0)
        {
          var txt = document.createTextNode(\'\');
          obj.appendChild(txt);
        }
        else
        {
          obj.childNodes[0].data = \'\';
        }
        obj.style.backgroundColor = colorBlank;
        obj.style.cursor = ';
  echo '\'\';
      }

      if (!isNaN(aMonth[w][d]))
      {
        tDate = new Date(document.data.years.value, document.data.monthes.value-1, aMonth[w][d]);

        if (tDate.getTime() < CurDate.getTime())
        {
          obj.style.backgroundColor = colorPast;
          obj.style.cursor = \'\';
        }
        else if (tDate.getTime() == CurDate.getTime())
        {
          obj.style.backgroundCol';
  echo 'or = colorToday;
          obj.style.cursor = \'\';
        }
        else if (maxdays > 0 && Math.ceil((tDate.getTime() - CurDate.getTime())/(24*60*60*1000)) > maxdays)
        {
          obj.style.backgroundColor = colorPast;
          obj.style.cursor = \'\';
        }
        else if (tDate.getTime() == ToDate.getTime())
        {
          obj.style.backgroundColor = colorToDate;
          lastr';
  echo 'ow = "td"+point;
        }
        else
        {
          var t = CalculateDiff("td"+point);
          tToDate = t[0];
          tDiff = t[1];
          if (tToDate.getTime() == tDate.getTime())
          {
            obj.style.backgroundColor = colorPayDate;
          }
        }
      }
    }
  }
}
</script>
</head>
<body>
<form name="data" onsubmit="CalculateProfit(); return false;">
<table borde';
  echo 'r=0 cellspacing=0 cellpadding=2>
<tr><td>
<table cellspacing=1 cellpadding=0 border=0 width=200>
<tr>
  <td><a href="javascript:PrevMonth()">&lt;&lt;</a></td>
  <td align=center>
   ';
  echo '<s';
  echo 'elect name="monthes" onchange="InitCalendar(document.data.monthes.value, document.data.years.value)" class=inpts>
    <option value=1>Jan</option>
    <option value=2>Feb</option>
    <option value=3>Mar</option>
    <option value=4>Apr</option>
    <option value=5>May</option>
    <option value=6>Jun</option>
    <option value=7>Jul</option>
    <option value=8>Aug</option>
    <option value=9>Sep</option>
    <opt';
  echo 'ion value=10>Oct</option>
    <option value=11>Nov</option>
    <option value=12>Dec</option>
   </select>
   ';
  echo '<s';
  echo 'elect name="years" onchange="InitCalendar(document.data.monthes.value, document.data.years.value)" class=inpts>
   </select>
  </td>
  <td><a href="javascript:NextMonth()">&gt;&gt;</a></td>
</tr>
</table>

<table cellspacing=1 cellpadding=0 border=0 bgcolor="#ff8d00"><tr><td bgcolor="#FFFFFF">
<table cellspacing=1 cellpadding=2 border=0>
  <tr>
   <td bgcolor="#ff8d00" width=20>Sun</td>
   <td bgcolor="#ff8d00" w';
  echo 'idth=20>Mon</td>
   <td bgcolor="#ff8d00" width=20>Tue</td>
   <td bgcolor="#ff8d00" width=20>Wed</td>
   <td bgcolor="#ff8d00" width=20>Thu</td>
   <td bgcolor="#ff8d00" width=20>Fri</td>
   <td bgcolor="#ff8d00" width=20>Sat</td>
  </tr>                 
  <tr>
   <td align="center" id="td1" onMouseOver="tdUpdateBg(\'td1\', 1)" onMouseOut="tdUpdateBg(\'td1\', 0)" onClick="CalculateProfit(\'td1\')"></td>
   <td align=';
  echo '"center" id="td2" onMouseOver="tdUpdateBg(\'td2\', 1)" onMouseOut="tdUpdateBg(\'td2\', 0)" onClick="CalculateProfit(\'td2\')"></td>
   <td align="center" id="td3" onMouseOver="tdUpdateBg(\'td3\', 1)" onMouseOut="tdUpdateBg(\'td3\', 0)" onClick="CalculateProfit(\'td3\')"></td>
   <td align="center" id="td4" onMouseOver="tdUpdateBg(\'td4\', 1)" onMouseOut="tdUpdateBg(\'td4\', 0)" onClick="CalculateProfit(\'td4\')"></td>
 ';
  echo '  <td align="center" id="td5" onMouseOver="tdUpdateBg(\'td5\', 1)" onMouseOut="tdUpdateBg(\'td5\', 0)" onClick="CalculateProfit(\'td5\')"></td>
   <td align="center" id="td6" onMouseOver="tdUpdateBg(\'td6\', 1)" onMouseOut="tdUpdateBg(\'td6\', 0)" onClick="CalculateProfit(\'td6\')"></td>
   <td align="center" id="td7" onMouseOver="tdUpdateBg(\'td7\', 1)" onMouseOut="tdUpdateBg(\'td7\', 0)" onClick="CalculateProfit(\'td';
  echo '7\')"></td>
  </tr>                                                                                             
  <tr>                                                                                              
   <td align="center" id="td8"  onMouseOver="tdUpdateBg(\'td8\', 1)" onMouseOut="tdUpdateBg(\'td8\', 0)"   onClick="CalculateProfit(\'td8\')"></td>
   <td align="center" id="td9"  onMouseOver="tdUpda';
  echo 'teBg(\'td9\', 1)" onMouseOut="tdUpdateBg(\'td9\', 0)"   onClick="CalculateProfit(\'td9\')"></td>
   <td align="center" id="td10" onMouseOver="tdUpdateBg(\'td10\', 1)" onMouseOut="tdUpdateBg(\'td10\', 0)" onClick="CalculateProfit(\'td10\')"></td>
   <td align="center" id="td11" onMouseOver="tdUpdateBg(\'td11\', 1)" onMouseOut="tdUpdateBg(\'td11\', 0)" onClick="CalculateProfit(\'td11\')"></td>
   <td align="center" id="td1';
  echo '2" onMouseOver="tdUpdateBg(\'td12\', 1)" onMouseOut="tdUpdateBg(\'td12\', 0)" onClick="CalculateProfit(\'td12\')"></td>
   <td align="center" id="td13" onMouseOver="tdUpdateBg(\'td13\', 1)" onMouseOut="tdUpdateBg(\'td13\', 0)" onClick="CalculateProfit(\'td13\')"></td>
   <td align="center" id="td14" onMouseOver="tdUpdateBg(\'td14\', 1)" onMouseOut="tdUpdateBg(\'td14\', 0)" onClick="CalculateProfit(\'td14\')"></td>
  </tr';
  echo '>
  <tr>
   <td align="center" id="td15" onMouseOver="tdUpdateBg(\'td15\', 1)" onMouseOut="tdUpdateBg(\'td15\', 0)" onClick="CalculateProfit(\'td15\')"></td>
   <td align="center" id="td16" onMouseOver="tdUpdateBg(\'td16\', 1)" onMouseOut="tdUpdateBg(\'td16\', 0)" onClick="CalculateProfit(\'td16\')"></td>
   <td align="center" id="td17" onMouseOver="tdUpdateBg(\'td17\', 1)" onMouseOut="tdUpdateBg(\'td17\', 0)" onClick=';
  echo '"CalculateProfit(\'td17\')"></td>
   <td align="center" id="td18" onMouseOver="tdUpdateBg(\'td18\', 1)" onMouseOut="tdUpdateBg(\'td18\', 0)" onClick="CalculateProfit(\'td18\')"></td>
   <td align="center" id="td19" onMouseOver="tdUpdateBg(\'td19\', 1)" onMouseOut="tdUpdateBg(\'td19\', 0)" onClick="CalculateProfit(\'td19\')"></td>
   <td align="center" id="td20" onMouseOver="tdUpdateBg(\'td20\', 1)" onMouseOut="tdUpdate';
  echo 'Bg(\'td20\', 0)" onClick="CalculateProfit(\'td20\')"></td>
   <td align="center" id="td21" onMouseOver="tdUpdateBg(\'td21\', 1)" onMouseOut="tdUpdateBg(\'td21\', 0)" onClick="CalculateProfit(\'td21\')"></td>
  </tr>
  <tr>
   <td align="center" id="td22" onMouseOver="tdUpdateBg(\'td22\', 1)" onMouseOut="tdUpdateBg(\'td22\', 0)" onClick="CalculateProfit(\'td22\')"></td>
   <td align="center" id="td23" onMouseOver="tdUpdat';
  echo 'eBg(\'td23\', 1)" onMouseOut="tdUpdateBg(\'td23\', 0)" onClick="CalculateProfit(\'td23\')"></td>
   <td align="center" id="td24" onMouseOver="tdUpdateBg(\'td24\', 1)" onMouseOut="tdUpdateBg(\'td24\', 0)" onClick="CalculateProfit(\'td24\')"></td>
   <td align="center" id="td25" onMouseOver="tdUpdateBg(\'td25\', 1)" onMouseOut="tdUpdateBg(\'td25\', 0)" onClick="CalculateProfit(\'td25\')"></td>
   <td align="center" id="td2';
  echo '6" onMouseOver="tdUpdateBg(\'td26\', 1)" onMouseOut="tdUpdateBg(\'td26\', 0)" onClick="CalculateProfit(\'td26\')"></td>
   <td align="center" id="td27" onMouseOver="tdUpdateBg(\'td27\', 1)" onMouseOut="tdUpdateBg(\'td27\', 0)" onClick="CalculateProfit(\'td27\')"></td>
   <td align="center" id="td28" onMouseOver="tdUpdateBg(\'td28\', 1)" onMouseOut="tdUpdateBg(\'td28\', 0)" onClick="CalculateProfit(\'td28\')"></td>
  </tr';
  echo '>                                                                                                                              
  <tr>                                                                                                                               
   <td align="center" id="td29" onMouseOver="tdUpdateBg(\'td29\', 1)" onMouseOut="tdUpdateBg(\'td29\', 0)" onClick="CalculateProfit(\'td29\')"></td';
  echo '>
   <td align="center" id="td30" onMouseOver="tdUpdateBg(\'td30\', 1)" onMouseOut="tdUpdateBg(\'td30\', 0)" onClick="CalculateProfit(\'td30\')"></td>
   <td align="center" id="td31" onMouseOver="tdUpdateBg(\'td31\', 1)" onMouseOut="tdUpdateBg(\'td31\', 0)" onClick="CalculateProfit(\'td31\')"></td>
   <td align="center" id="td32" onMouseOver="tdUpdateBg(\'td32\', 1)" onMouseOut="tdUpdateBg(\'td32\', 0)" onClick="Calcu';
  echo 'lateProfit(\'td32\')"></td>
   <td align="center" id="td33" onMouseOver="tdUpdateBg(\'td33\', 1)" onMouseOut="tdUpdateBg(\'td33\', 0)" onClick="CalculateProfit(\'td33\')"></td>
   <td align="center" id="td34" onMouseOver="tdUpdateBg(\'td34\', 1)" onMouseOut="tdUpdateBg(\'td34\', 0)" onClick="CalculateProfit(\'td34\')"></td>
   <td align="center" id="td35" onMouseOver="tdUpdateBg(\'td35\', 1)" onMouseOut="tdUpdateBg(\'td';
  echo '35\', 0)" onClick="CalculateProfit(\'td35\')"></td>
  </tr>                                                                                                                              
  <tr>                                                                                                                               
   <td align="center" id="td36" onMouseOver="tdUpdateBg(\'td36\', 1)" onMouseOut="tdUpdat';
  echo 'eBg(\'td36\', 0)" onClick="CalculateProfit(\'td36\')"></td>
   <td align="center" id="td37" onMouseOver="tdUpdateBg(\'td37\', 1)" onMouseOut="tdUpdateBg(\'td37\', 0)" onClick="CalculateProfit(\'td37\')"></td>
   <td align="center" id="td38" onMouseOver="tdUpdateBg(\'td38\', 1)" onMouseOut="tdUpdateBg(\'td38\', 0)" onClick="CalculateProfit(\'td38\')"></td>
   <td align="center" id="td39" onMouseOver="tdUpdateBg(\'td39\', ';
  echo '1)" onMouseOut="tdUpdateBg(\'td39\', 0)" onClick="CalculateProfit(\'td39\')"></td>
   <td align="center" id="td40" onMouseOver="tdUpdateBg(\'td40\', 1)" onMouseOut="tdUpdateBg(\'td40\', 0)" onClick="CalculateProfit(\'td40\')"></td>
   <td align="center" id="td41" onMouseOver="tdUpdateBg(\'td41\', 1)" onMouseOut="tdUpdateBg(\'td41\', 0)" onClick="CalculateProfit(\'td41\')"></td>
   <td align="center" id="td42" onMouseOv';
  echo 'er="tdUpdateBg(\'td42\', 1)" onMouseOut="tdUpdateBg(\'td42\', 0)" onClick="CalculateProfit(\'td42\')"></td>
  </tr>
</table>
</td></tr></table>';
  echo '<s';
  echo 'cript>
document.data.monthes.options[CurDate.getMonth()].selected = true;
for (var i = CurDate.getFullYear(); i < CurDate.getFullYear() + 6; i++)
{
  document.data.years.options[document.data.years.options.length] = new Option(i,i);
}
InitCalendar(CurDate.getMonth()+1, CurDate.getFullYear());
</script>
</td>
<td valign=top>
<table>
<tr>
  <td>&nbsp;</td>
</tr>
<tr>
  <td>From:</td><td><b>';
  echo '<s';
  echo 'cript>document.write(CurDate.getMonth()+1 + \'/\' + CurDate.getDate() + \'/\' + CurDate.getFullYear())</script></b></td>
</tr>
<tr>
  <td>To:</td><td><b>';
  echo '<s';
  echo 'pan id="to">Select in the calendar</span></b></td>
</tr>
<tr>
  <td>Periods:</td><td><b>';
  echo '<s';
  echo 'pan id="days">N/A</span></b></td>
</tr>
<tr>
  <td>Amount:</td><td nowrap>$ <input type="text" name="amount" value="10" size=5 class=inpts> <input type="button" value="Calculate" onclick="CalculateProfit()" class=sbmt></td>
</tr>
<tr>
  <td>Compounding Percent:</td><td nowrap><input type="text" name="compounding_percent" value="10" size=5 class=inpts> % <input type="button" value="Calculate" onclick="CalculateProfi';
  echo 't()" class=sbmt></td>
</tr>
<tr>
  <td>Percent:</td><td><b>';
  echo '<s';
  echo 'pan id="percent">N/A</span></b></td>
</tr>
<tr>
  <td>Profit:</td><td><b>';
  echo '<s';
  echo 'pan id="profit">N/A</span></b></td>
</tr>
<tr>
  <td>Deposit:</td><td><b>';
  echo '<s';
  echo 'pan id="deposit">N/A</span></b></td>
</tr>
</table>
</td></tr></table>';
  echo '<s';
  echo 'cript>
CalculatePercent();
</script>
</form>';
