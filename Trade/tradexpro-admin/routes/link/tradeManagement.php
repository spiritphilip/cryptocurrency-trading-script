<?php

use Illuminate\Support\Facades\Route;

// trade
Route::group(['group' => 'coin_pair'], function () {
    Route::get('trade/coin-pairs', 'TradeSettingController@coinPairs')->name('coinPairs');
    Route::get('trade/coin-pairs-chart-update/{id}', 'TradeSettingController@coinPairsChartUpdate')->name('coinPairsChartUpdate');
    Route::post('trade/change-coin-pair-bot-status', 'TradeSettingController@changeCoinPairBotStatus')->name('changeCoinPairBotStatus');
    Route::get('trade/trade-fees-settings', 'TradeSettingController@tradeFeesSettings')->name('tradeFeesSettings');
    Route::post('trade/save-trade-fees-settings', 'TradeSettingController@tradeFeesSettingSave')->name('tradeFeesSettingSave');
    Route::post('trade/remove-trade-limit', 'TradeSettingController@removeTradeLimit')->name('removeTradeLimit');
});
Route::group(['middleware' => 'check_demo'], function () {
    Route::get('trade/coin-pairs-delete/{id}', 'TradeSettingController@coinPairsDelete')->name('coinPairsDelete');
    Route::post('trade/save-coin-pair', 'TradeSettingController@saveCoinPairSettings')->name('saveCoinPairSettings');
    Route::post('trade/change-coin-pair-status', 'TradeSettingController@changeCoinPairStatus')->name('changeCoinPairStatus');
});


// trade reports
Route::group([ 'group' => 'buy_order'], function () {
    Route::get('all-buy-orders-history', 'ReportController@adminAllOrdersHistoryBuy')->name('adminAllOrdersHistoryBuy');
});
Route::group(['group' => 'sell_order'], function () {
    Route::get('all-sell-orders-history', 'ReportController@adminAllOrdersHistorySell')->name('adminAllOrdersHistorySell');
});
Route::group([ 'group' => 'stop_limit'], function () {
    Route::get('all-stop-limit-orders-history', 'ReportController@adminAllOrdersHistoryStopLimit')->name('adminAllOrdersHistoryStopLimit');
});
Route::group([ 'group' => 'transaction'], function () {
    Route::get('all-transaction-history', 'ReportController@adminAllTransactionHistory')->name('adminAllTransactionHistory');
});
