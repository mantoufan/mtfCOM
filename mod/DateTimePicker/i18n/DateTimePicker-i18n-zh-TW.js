/* -----------------------------------------------------------------------------

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.38
  Copyright (c)2017 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

  language: Traditional Chinese
  file: DateTimePicker-i18n-zh-TW
  author: JasonYCHuang (https://github.com/JasonYCHuang)

*/

(function ($) {
   $.DateTimePicker.i18n["zh-TW"] = $.extend($.DateTimePicker.i18n["zh-TW"], {

        language: "zh-TW",
        labels: {
            'year': '年',
            'month': '月',
            'day': '日',
            'hour': '時',
            'minutes': '分',
            'seconds': '秒',
            'meridiem': '午'
        },
        dateTimeFormat: "yyyy-MM-dd HH:mm",
        dateFormat: "yyyy-MM-dd",
        timeFormat: "HH:mm",

        shortDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        fullDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        shortMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
        fullMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],

        titleContentDate: "設置日期",
        titleContentTime: "設置時間",
        titleContentDateTime: "設置日期和時間",

        setButtonContent: "設置",
        clearButtonContent: "清除",
        formatHumanDate: function (oDate, sMode, sFormat) {
            if (sMode === "date")
                return  oDate.dayShort + ", " + oDate.yyyy + "年" +  oDate.month +"月" + oDate.dd + "日";
            else if (sMode === "time")
                return oDate.HH + "時" + oDate.mm + "分" + oDate.ss + "秒";
            else if (sMode === "datetime")
                return oDate.dayShort + ", " + oDate.yyyy + "年" +  oDate.month +"月" + oDate.dd + "日 " + oDate.HH + "時" + oDate.mm + "分";
        }
    });
})(jQuery);
