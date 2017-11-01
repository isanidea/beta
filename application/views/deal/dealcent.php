<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="CoinComing">
    <title data-i18n="title"></title>
    <link href="http://st.test.com/xnb_home/css/style-trade.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/css/trade-center-detail.css" rel="stylesheet">
    <link href="http://st.test.com/xnb_home/css/kline.css" rel="stylesheet">
    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
</head>

<body>
<!--[if lt IE 10]>
<div class="promote_bar" id="promote_bar">
    <div class="boardalert">
        <div class="bordalert-text pos-relative">
            <span>重要提示：系统检测到您的浏览器版本太低，为了您的账户安全及操作体验，请升级浏览器，建议您使用谷歌浏览器。</span>
            <a href="http://browsehappy.com/" target="_blank" class="color-red">点击升级</a>
        </div>
        3
    </div>
</div>
<![endif]-->
<div class="wrap">
    <!-- 内容-begin -->
    <div class="trade_wrap clearfix">
        <!-- 行情-begin -->
        <div class="trade_lt fr">
            <div class="tradelt_search">
                <h2 class="trade_tit fl" data-i18n="trade_market"></h2>
                <!-- <div class="tra_searchbox fr">
                <a href="javasript:;"> <img src="../../img/trade_search.png" alt="Bit-Z"></a>
                <input type="text" placeholder="搜索币种" id="filterInput">
            </div> -->
            </div>
            <div class="tradelt_outbox">
                <ul class="tradelt_ul clearfix">
                    <li class="active">BTC</li>
                </ul>
                <div class="tradelt_listbox btc_trade_market active">
                    <table class="layui-table up-market" lay-skin="nob" lay-even="" id="market">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th data-i18n="trade_coinName"></th>
                            <th data-i18n="trade_lastPrice"></th>
                            <th data-i18n="trade_volumn" class="js-addmarket"></th>
                            <th data-i18n="trade_zd"></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="trade_price_con">
                <span>BTC</span>
                <span data-i18n="zxj" class="ml5"></span>
                <span id="usdPrice">$<?echo $usd?></span>
                <span class="price_line"></span>
                <span id="rmbPrice">₩<?echo $krw?></span>
            </div>
            <div class="trade_news">
                <div class="tradelt_search">
                    <h2 class="trade_tit fl" data-i18n="trade_notice"></h2>
                </div>
                <ul id="trade_notice"></ul>
            </div>
            <!-- 新闻end -->
        </div>
        <!-- 行情-end -->
        <div class="trade_rt fl">
            <!-- 币种详情-home -->
            <h2 class="trade_tit">
                <div class="addwidth">
                    <span class="curCoinDisplay js-upco-name"></span>
                    <span data-i18n="trade_trade"></span>
                    (<span class="curCoinName">
                        <i class="js-upco-name"></i> /
                        <i class="js-coin-market"></i>
                    </span>)
                </div>
                <div class="clearfix"></div>
            </h2>
            <h3 class="trade_hinfo">
                <span data-i18n="trade_volumn"></span>：
                <span id="ltc_btc_sum_from_24"></span><span class="curCoinName js-upco-name"></span>
            </h3>
            <div class="clearfix"></div>
            <div class="trart_lowprice borderbox clearfix">
                <div class="lowprice lowprimar fl">
                    <p data-i18n="trade_lastPrice"></p>
                    <span id="ltc_btc_new_price" class="curCoin_new_price"></span>
                </div>
                <div class="fl trart_lowprice_line"></div>
                <div class="lowprice fl">
                    <p data-i18n="trade_zd"></p>
                    <span id="ltc_btc_change_price"></span>
                </div>
                <!-- <div class="fl trart_lowprice_line"></div>
            <div class="lowprice fl">
                <p data-i18n="trade_high"></p>
                <span id="ltc_btc_max_price" class="curCoin_max_price">loading</span>
            </div>
            <div class="fl trart_lowprice_line"></div>
            <div class="lowprice fl noborder">
                <p data-i18n="trade_low"></p>
                <span id="ltc_btc_min_price" class="curCoin_min_price">loading</span>
            </div> -->
                <div class="fl trart_lowprice_line"></div>
                <div class="lowprice fl noborder">
                    <p data-i18n="buy_one"></p>
                    <span id="buy_one" class="curCoin_min_price">loading</span>
                </div>
                <div class="fl trart_lowprice_line"></div>
                <div class="lowprice fl noborder">
                    <p data-i18n="sell_one"></p>
                    <span id="sell_one" class="curCoin_min_price">loading</span>
                </div>
            </div>
            <!-- 币种详情-end -->
            <!-- tab分页-home -->
            <div class="uc_topnav mt20 pr">
                <span class="open-fresh" id="open_fresh" data-select="open" data-i18n="trade_beginFresh"></span>
                <ul class="ucnav_ul clearfix js-btc-tab">
                    <li class="active">
                        <a href="javascript:;">
                            <span class="js-upco-name"></span>
                            <span data-i18n="trade_trade"></span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span class="js-upco-name"></span>
                            <span data-i18n="trade_information"></span>
                        </a>
                    </li>
                </ul>
                <div class="tab_con pr">
                    <!-- <div class="gjk" id="select_k">
                    <span href="javascript:;" data-type="one_k" data-i18n="trade_day"></span>
                    <span href="javascript:;" data-type="three_k" data-i18n="trade_threeDay"></span>
                    <span href="javascript:;" data-type="five_k" data-i18n="trade_fiveDay"></span>
                    <span href="javascript:;" data-type="senior_k" data-i18n="trade_senior"></span>
                </div> -->
                    <!-- K线图-home -->
                    <div id="main" class="trade_klinebox">
                        <div id="chart_container" class="dark" style="width: 1670px; height: 361px; visibility: visible;">
                            <!-- Dom Element Cache -->
                            <div id="chart_dom_elem_cache"></div>
                            <!-- ToolBar -->
                            <div id="chart_toolbar" style="left: 0px; top: 0px; width: 1670px; height: 29px;">
                                <div class="chart_toolbar_minisep">CoinComing</div>
                                <!-- Periods -->
                                <div class="chart_dropdown" id="chart_toolbar_periods_vert">
                                    <div class="chart_dropdown_t">
                                        <a class="chart_str_period">周期</a></div>
                                    <div class="chart_dropdown_data" style="margin-left: -58px;">
                                        <table>
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <ul>
                                                        <li id="chart_period_1w_v" name="1w">
                                                            <a class="chart_str_period_1w">周线</a></li>
                                                        <li id="chart_period_3d_v" name="3d">
                                                            <a class="chart_str_period_3d">3日</a></li>
                                                        <li id="chart_period_1d_v" name="1d">
                                                            <a class="chart_str_period_1d">日线</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <ul>
                                                        <li id="chart_period_12h_v" name="12h">
                                                            <a class="chart_str_period_12h">12小时</a></li>
                                                        <li id="chart_period_6h_v" name="6h">
                                                            <a class="chart_str_period_6h">6小时</a></li>
                                                        <li id="chart_period_4h_v" name="4h">
                                                            <a class="chart_str_period_4h">4小时</a></li>
                                                        <li id="chart_period_2h_v" name="2h">
                                                            <a class="chart_str_period_2h">2小时</a></li>
                                                        <li id="chart_period_1h_v" name="1h">
                                                            <a class="chart_str_period_1h">1小时</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <ul>
                                                        <li id="chart_period_30m_v" name="30m">
                                                            <a class="chart_str_period_30m">30分钟</a></li>
                                                        <li id="chart_period_15m_v" name="15m">
                                                            <a class="chart_str_period_15m">15分钟</a></li>
                                                        <li id="chart_period_5m_v" name="5m">
                                                            <a class="chart_str_period_5m">5分钟</a></li>
                                                        <li id="chart_period_3m_v" name="3m">
                                                            <a class="chart_str_period_3m">3分钟</a></li>
                                                        <li id="chart_period_1m_v" name="1m">
                                                            <a class="chart_str_period_1m selected">1分钟</a></li>
                                                        <li id="chart_period_line_v" name="line">
                                                            <a class="chart_str_period_line">分时</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="chart_toolbar_periods_horz">
                                    <ul class="chart_toolbar_tabgroup" style="padding-left:5px; padding-right:11px;">
                                        <li id="chart_period_1w_h" name="1w" style="display: inline-block;">
                                            <a class="chart_str_period_1w">周线</a></li>
                                        <li id="chart_period_3d_h" name="3d" style="display: none;">
                                            <a class="chart_str_period_3d">3日</a></li>
                                        <li id="chart_period_1d_h" name="1d" style="display: inline-block;">
                                            <a class="chart_str_period_1d">日线</a></li>
                                        <li id="chart_period_12h_h" name="12h" style="display: none;">
                                            <a class="chart_str_period_12h">12小时</a></li>
                                        <li id="chart_period_6h_h" name="6h" style="display: none;">
                                            <a class="chart_str_period_6h">6小时</a></li>
                                        <li id="chart_period_4h_h" name="4h" style="display: none;">
                                            <a class="chart_str_period_4h">4小时</a></li>
                                        <li id="chart_period_2h_h" name="2h" style="display: none;">
                                            <a class="chart_str_period_2h">2小时</a></li>
                                        <li id="chart_period_1h_h" name="1h" style="display: inline-block;">
                                            <a class="chart_str_period_1h">1小时</a></li>
                                        <li id="chart_period_30m_h" name="30m" style="display: inline-block;">
                                            <a class="chart_str_period_30m">30分钟</a></li>
                                        <li id="chart_period_15m_h" name="15m" style="display: inline-block;">
                                            <a class="chart_str_period_15m">15分钟</a></li>
                                        <li id="chart_period_5m_h" name="5m" style="display: inline-block;">
                                            <a class="chart_str_period_5m">5分钟</a></li>
                                        <li id="chart_period_3m_h" name="3m" style="display: none;">
                                            <a class="chart_str_period_3m">3分钟</a></li>
                                        <li id="chart_period_1m_h" name="1m" style="display: inline-block;">
                                            <a class="chart_str_period_1m selected">1分钟</a></li>
                                        <li id="chart_period_line_h" name="line" style="display: inline-block;">
                                            <a class="chart_str_period_line">分时</a></li>
                                    </ul>
                                </div>
                                <div id="chart_show_indicator" class="chart_toolbar_button chart_str_indicator_cap selected">技术指标</div>
                                <div id="chart_show_tools" class="chart_toolbar_button chart_str_tools_cap">画线工具</div>
                                <div id="chart_toolbar_theme">
                                    <div class="chart_toolbar_label chart_str_theme_cap">主题选择</div>
                                    <a name="dark" class="chart_icon chart_icon_theme_dark selected"></a>
                                    <a name="light" class="chart_icon chart_icon_theme_light"></a>
                                </div>
                                <div class="chart_dropdown" id="chart_dropdown_settings">
                                    <div class="chart_dropdown_t">
                                        <a class="chart_str_settings">更多</a></div>
                                    <div class="chart_dropdown_data" style="margin-left: -142px;">
                                        <table>
                                            <tbody>
                                            <tr id="chart_select_main_indicator">
                                                <td class="chart_str_main_indicator">主指标</td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a name="MA" class="selected">MA</a></li>
                                                        <li>
                                                            <a name="EMA" class="">EMA</a></li>
                                                        <li>
                                                            <a name="BOLL" class="">BOLL</a></li>
                                                        <li>
                                                            <a name="SAR" class="">SAR</a></li>
                                                        <li>
                                                            <a name="NONE" class="">None</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr id="chart_select_chart_style">
                                                <td class="chart_str_chart_style">主图样式</td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a class="selected">CandleStick</a></li>
                                                        <li>
                                                            <a>CandleStickHLC</a>
                                                        </li>
                                                        <li>
                                                            <a class="">OHLC</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr id="chart_select_theme" style="display: none;">
                                                <td class="chart_str_theme">主题选择</td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a name="dark" class="chart_icon chart_icon_theme_dark selected"></a>
                                                        </li>
                                                        <li>
                                                            <a name="light" class="chart_icon chart_icon_theme_light"></a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr id="chart_enable_tools" style="display: none;">
                                                <td class="chart_str_tools">画线工具</td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a name="on" class="chart_str_on">开启</a></li>
                                                        <li>
                                                            <a name="off" class="chart_str_off selected">关闭</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr id="chart_enable_indicator" style="display: none;">
                                                <td class="chart_str_indicator">技术指标</td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a name="on" class="chart_str_on selected">开启</a></li>
                                                        <li>
                                                            <a name="off" class="chart_str_off">关闭</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <ul>
                                                        <li>
                                                            <a id="chart_btn_parameter_settings" class="chart_str_indicator_parameters">指标参数设置</a></li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="chart_dropdown" id="chart_language_setting_div" style="padding-left: 5px;">
                                    <div class="chart_dropdown_t">
                                        <a class="chart_language_setting">语言(LANG)</a></div>
                                    <div class="chart_dropdown_data" style="padding-top: 15px; margin-left: -12px;">
                                        <ul>
                                            <li style="height: 25px;">
                                                <a name="zh-cn">简体中文(zh-CN)</a></li>
                                            <li style="height: 25px;">
                                                <a name="en-us" class="selected">English(en-US)</a></li>
                                            <li style="height: 25px;">
                                                <a name="zh-tw">繁體中文(zh-HK)</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="chart_updated_time">
                                    <span class="chart_str_updated">更新于</span>
                                    <span id="chart_updated_time_text">4秒</span>
                                    <span class="chart_str_ago">前</span></div>
                            </div>
                            <!-- ToolPanel -->
                            <div id="chart_toolpanel" style="display: none; left: 0px; top: 30px; width: 32px; height: 331px;">
                                <div class="chart_toolpanel_separator"></div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_Cursor" name="Cursor"></div>
                                    <div class="chart_toolpanel_tip chart_str_cursor">光标</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_CrossCursor" name="CrossCursor"></div>
                                    <div class="chart_toolpanel_tip chart_str_cross_cursor">十字光标</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_SegLine" name="SegLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_seg_line">线段</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_StraightLine" name="StraightLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_straight_line">直线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_RayLine" name="RayLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_ray_line">射线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_ArrowLine" name="ArrowLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_arrow_line">箭头</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_HoriSegLine" name="HoriSegLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_horz_seg_line">水平线段</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_HoriStraightLine" name="HoriStraightLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_horz_straight_line">水平直线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_HoriRayLine" name="HoriRayLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_horz_ray_line">水平射线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_VertiStraightLine" name="VertiStraightLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_vert_straight_line">垂直直线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_PriceLine" name="PriceLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_price_line">价格线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_TriParallelLine" name="TriParallelLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_tri_parallel_line">价格通道线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_BiParallelLine" name="BiParallelLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_bi_parallel_line">平行直线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_BiParallelRayLine" name="BiParallelRayLine"></div>
                                    <div class="chart_toolpanel_tip chart_str_bi_parallel_ray">平行射线</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_DrawFibRetrace" name="DrawFibRetrace"></div>
                                    <div class="chart_toolpanel_tip chart_str_fib_retrace">斐波纳契回调</div>
                                </div>
                                <div class="chart_toolpanel_button">
                                    <div class="chart_toolpanel_icon" id="chart_DrawFibFans" name="DrawFibFans"></div>
                                    <div class="chart_toolpanel_tip chart_str_fib_fans">斐波纳契扇形</div>
                                </div>
                                <div style="padding-left: 3px;padding-top: 10px;">
                                    <button style="color: red;" id="clearCanvas" title="Clear All">X</button>
                                </div>
                            </div>
                            <!-- Canvas Group -->
                            <div id="chart_canvasGroup" style="left: 0px; top: 30px; width: 1670px; height: 308px;" class="temp">
                                <canvas class="chart_canvas" id="chart_mainCanvas" width="1670" height="308" style="cursor: default;"></canvas>
                                <canvas class="chart_canvas" id="chart_overlayCanvas" width="1670" height="308" style="cursor: default;"></canvas>
                            </div>
                            <!-- TabBar -->
                            <div id="chart_tabbar" style="display: block; position: relative; left: 0px; top: 338px; width: 1670px; height: 22px;">
                                <ul>
                                    <li>
                                        <a name="MACD" class="selected">MACD</a></li>
                                    <li>
                                        <a name="KDJ" class="">KDJ</a></li>
                                    <li>
                                        <a name="StochRSI" class="">StochRSI</a></li>
                                    <li>
                                        <a name="RSI" class="">RSI</a></li>
                                    <li>
                                        <a name="DMI" class="">DMI</a></li>
                                    <li>
                                        <a name="OBV" class="">OBV</a></li>
                                    <li>
                                        <a name="BOLL" class="">BOLL</a></li>
                                    <li>
                                        <a name="SAR" class="">SAR</a></li>
                                    <li>
                                        <a name="DMA" class="">DMA</a></li>
                                    <li>
                                        <a name="TRIX" class="">TRIX</a></li>
                                    <li>
                                        <a name="BRAR" class="">BRAR</a></li>
                                    <li>
                                        <a name="VR" class="">VR</a></li>
                                    <li>
                                        <a name="EMV" class="">EMV</a></li>
                                    <li>
                                        <a name="WR" class="">WR</a></li>
                                    <li>
                                        <a name="ROC" class="">ROC</a></li>
                                    <li>
                                        <a name="MTM" class="">MTM</a></li>
                                    <li>
                                        <a name="PSY">PSY</a></li>
                                </ul>
                            </div>
                            <!-- Parameter Settings -->
                            <div id="chart_parameter_settings" style="left: 515px; top: -64px;">
                                <h2 class="chart_str_indicator_parameters">指标参数设置</h2>
                                <table>
                                    <tbody>
                                    <tr>
                                        <th>MA</th>
                                        <td>
                                            <input name="MA">
                                            <input name="MA">
                                            <input name="MA">
                                            <input name="MA">
                                            <br>
                                            <input name="MA">
                                            <input name="MA">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>DMA</th>
                                        <td>
                                            <input name="DMA">
                                            <input name="DMA">
                                            <input name="DMA">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>EMA</th>
                                        <td>
                                            <input name="EMA">
                                            <input name="EMA">
                                            <input name="EMA">
                                            <input name="EMA">
                                            <br>
                                            <input name="EMA">
                                            <input name="EMA">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>TRIX</th>
                                        <td>
                                            <input name="TRIX">
                                            <input name="TRIX">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>VOLUME</th>
                                        <td>
                                            <input name="VOLUME">
                                            <input name="VOLUME">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>BRAR</th>
                                        <td>
                                            <input name="BRAR">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>MACD</th>
                                        <td>
                                            <input name="MACD">
                                            <input name="MACD">
                                            <input name="MACD">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>VR</th>
                                        <td>
                                            <input name="VR">
                                            <input name="VR">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>KDJ</th>
                                        <td>
                                            <input name="KDJ">
                                            <input name="KDJ">
                                            <input name="KDJ">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>EMV</th>
                                        <td>
                                            <input name="EMV">
                                            <input name="EMV">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>StochRSI</th>
                                        <td>
                                            <input name="StochRSI">
                                            <input name="StochRSI">
                                            <input name="StochRSI">
                                            <input name="StochRSI">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>WR</th>
                                        <td>
                                            <input name="WR">
                                            <input name="WR">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>RSI</th>
                                        <td>
                                            <input name="RSI">
                                            <input name="RSI">
                                            <input name="RSI">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>ROC</th>
                                        <td>
                                            <input name="ROC">
                                            <input name="ROC">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>DMI</th>
                                        <td>
                                            <input name="DMI">
                                            <input name="DMI">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>MTM</th>
                                        <td>
                                            <input name="MTM">
                                            <input name="MTM">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>OBV</th>
                                        <td>
                                            <input name="OBV">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                        <th>PSY</th>
                                        <td>
                                            <input name="PSY">
                                            <input name="PSY">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>BOLL</th>
                                        <td>
                                            <input name="BOLL">
                                        </td>
                                        <td>
                                            <button class="chart_str_default">默认值</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div id="close_settings">
                                    <a class="chart_str_close">关闭</a></div>
                            </div>
                            <!-- Loading -->
                            <div id="chart_loading" class="chart_str_loading" style="left: 735px; top: 78px;">正在读取数据...</div>
                        </div>
                        <!-- End Of ChartContainer -->
                        <div style="display: none" id="chart_language_switch_tmp">
                                <span name="chart_str_period" zh_tw="週期" zh_cn="周期" en_us="TIME">
        <span name="chart_str_period_line" zh_tw="分時" zh_cn="分时" en_us="Line">
            <span name="chart_str_period_1m" zh_tw="1分钟" zh_cn="1分钟" en_us="1m">
                <span name="chart_str_period_3m" zh_tw="3分钟" zh_cn="3分钟" en_us="3m">
                    <span name="chart_str_period_5m" zh_tw="5分钟" zh_cn="5分钟" en_us="5m">
                        <span name="chart_str_period_15m" zh_tw="15分钟" zh_cn="15分钟" en_us="15m">
                            <span name="chart_str_period_30m" zh_tw="30分钟" zh_cn="30分钟" en_us="30m">
                                <span name="chart_str_period_1h" zh_tw="1小時" zh_cn="1小时" en_us="1h">
                                    <span name="chart_str_period_2h" zh_tw="2小時" zh_cn="2小时" en_us="2h">
                                        <span name="chart_str_period_4h" zh_tw="4小時" zh_cn="4小时" en_us="4h">
                                            <span name="chart_str_period_6h" zh_tw="6小時" zh_cn="6小时" en_us="6h">
                                                <span name="chart_str_period_12h" zh_tw="12小時" zh_cn="12小时" en_us="12h">
                                                    <span name="chart_str_period_1d" zh_tw="日線" zh_cn="日线" en_us="1d">
                                                        <span name="chart_str_period_3d" zh_tw="3日" zh_cn="3日" en_us="3d">
                                                            <span name="chart_str_period_1w" zh_tw="周線" zh_cn="周线" en_us="1w">
                                                                <span name="chart_str_settings" zh_tw="更多" zh_cn="更多" en_us="MORE">
                                                                    <span name="chart_setting_main_indicator" zh_tw="均線設置" zh_cn="均线设置" en_us="Main Indicator">
                                                                        <span name="chart_setting_main_indicator_none" zh_tw="關閉均線" zh_cn="关闭均线" en_us="None">
                                                                            <span name="chart_setting_indicator_parameters" zh_tw="指標參數設置" zh_cn="指标参数设置" en_us="Indicator Parameters">
                                                                                <span name="chart_str_chart_style" zh_tw="主圖樣式" zh_cn="主图样式" en_us="Chart Style">
                                                                                    <span name="chart_str_main_indicator" zh_tw="主指標" zh_cn="主指标" en_us="Main Indicator">
                                                                                        <span name="chart_str_indicator" zh_tw="技術指標" zh_cn="技术指标" en_us="Indicator">
                                                                                            <span name="chart_str_indicator_cap" zh_tw="技術指標" zh_cn="技术指标" en_us="INDICATOR">
                                                                                                <span name="chart_str_tools" zh_tw="畫線工具" zh_cn="画线工具" en_us="Tools">
                                                                                                    <span name="chart_str_tools_cap" zh_tw="畫線工具" zh_cn="画线工具" en_us="TOOLS">
                                                                                                        <span name="chart_str_theme" zh_tw="主題選擇" zh_cn="主题选择" en_us="Theme">
                                                                                                            <span name="chart_str_theme_cap" zh_tw="主題選擇" zh_cn="主题选择" en_us="THEME">
                                                                                                                <span name="chart_language_setting" zh_tw="語言(LANG)" zh_cn="语言(LANG)" en_us="LANGUAGE">
                                                                                                                    <span name="chart_exchanges_setting" zh_tw="更多市場" zh_cn="更多市场" en_us="MORE MARKETS">
                                                                                                                        <span name="chart_othercoin_setting" zh_tw="其它市場" zh_cn="其它市场" en_us="OTHER MARKETS">
                                                                                                                            <span name="chart_str_none" zh_tw="關閉" zh_cn="关闭" en_us="None">
                                                                                                                                <span name="chart_str_theme_dark" zh_tw="深色主題" zh_cn="深色主题" en_us="Dark">
                                                                                                                                    <span name="chart_str_theme_light" zh_tw="淺色主題" zh_cn="浅色主题" en_us="Light">
                                                                                                                                        <span name="chart_str_on" zh_tw="開啟" zh_cn="开启" en_us="On">
                                                                                                                                            <span name="chart_str_off" zh_tw="關閉" zh_cn="关闭" en_us="Off">
                                                                                                                                                <span name="chart_str_close" zh_tw="關閉" zh_cn="关闭" en_us="CLOSE">
                                                                                                                                                    <span name="chart_str_default" zh_tw="默認值" zh_cn="默认值" en_us="default">
                                                                                                                                                        <span name="chart_str_loading" zh_tw="正在讀取數據..." zh_cn="正在读取数据..." en_us="Loading...">
                                                                                                                                                            <span name="chart_str_indicator_parameters" zh_tw="指標參數設置" zh_cn="指标参数设置" en_us="Indicator Parameters">
                                                                                                                                                                <span name="chart_str_cursor" zh_tw="光標" zh_cn="光标" en_us="Cursor">
                                                                                                                                                                    <span name="chart_str_cross_cursor" zh_tw="十字光標" zh_cn="十字光标" en_us="Cross Cursor">
                                                                                                                                                                        <span name="chart_str_seg_line" zh_tw="線段" zh_cn="线段" en_us="Trend Line">
                                                                                                                                                                            <span name="chart_str_straight_line" zh_tw="直線" zh_cn="直线" en_us="Extended">
                                                                                                                                                                                <span name="chart_str_ray_line" zh_tw="射線" zh_cn="射线" en_us="Ray">
                                                                                                                                                                                    <span name="chart_str_arrow_line" zh_tw="箭頭" zh_cn="箭头" en_us="Arrow">
                                                                                                                                                                                        <span name="chart_str_horz_seg_line" zh_tw="水平線段" zh_cn="水平线段" en_us="Horizontal Line">
                                                                                                                                                                                            <span name="chart_str_horz_straight_line" zh_tw="水平直線" zh_cn="水平直线" en_us="Horizontal Extended">
                                                                                                                                                                                                <span name="chart_str_horz_ray_line" zh_tw="水平射線" zh_cn="水平射线" en_us="Horizontal Ray">
                                                                                                                                                                                                    <span name="chart_str_vert_straight_line" zh_tw="垂直直線" zh_cn="垂直直线" en_us="Vertical Extended">
                                                                                                                                                                                                        <span name="chart_str_price_line" zh_tw="價格線" zh_cn="价格线" en_us="Price Line">
                                                                                                                                                                                                            <span name="chart_str_tri_parallel_line" zh_tw="價格通道線" zh_cn="价格通道线" en_us="Parallel Channel">
                                                                                                                                                                                                                <span name="chart_str_bi_parallel_line" zh_tw="平行直線" zh_cn="平行直线" en_us="Parallel Lines">
                                                                                                                                                                                                                    <span name="chart_str_bi_parallel_ray" zh_tw="平行射線" zh_cn="平行射线" en_us="Parallel Rays">
                                                                                                                                                                                                                        <span name="chart_str_fib_retrace" zh_tw="斐波納契回調" zh_cn="斐波纳契回调" en_us="Fibonacci Retracements">
                                                                                                                                                                                                                            <span name="chart_str_fib_fans" zh_tw="斐波納契扇形" zh_cn="斐波纳契扇形" en_us="Fibonacci Fans">
                                                                                                                                                                                                                                <span name="chart_str_updated" zh_tw="更新於" zh_cn="更新于" en_us="Updated">
                                                                                                                                                                                                                                    <span name="chart_str_ago" zh_tw="前" zh_cn="前" en_us="ago"></span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                                </span>
                        </div>
                        <input value="0" id="depArgs" type="hidden">
                    </div>
                    <!-- K线图-end -->
                    <!-- 申请委托-home-->
                    <div class="trade_box clearfix">
                        <div class="trade_buy trade_info fl">
                            <div class="trade_buyfm">
                                <div class="trabuy_title borderbox clearfix">
                                    <div class="trabuy_titlt fl">
                                        <h2>
                                            <i data-i18n="trade_buy"></i>
                                            <span class="curCoinName js-upco-name">LTC</span>
                                        </h2>
                                        <p>
                                            <span data-i18n="trade_useMoney"></span>：
                                            <strong>
                                                <span class="buy_max" id="buy_max">0</span>
                                            </strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="trade_listbox js-entrust">
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_price"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="bprice" value="0">
                                            <span class="buy-unit">BTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_num"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="bnumber">
                                            <span class="curCoinName js-upco-name">LTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_tradeMoney"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="bsumprice" readonly="">
                                            <span class="buy-unit">BTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_pwd"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="password" id="btradepwd" placeholder="">
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_poundage"></label>&nbsp;
                                        <i id="rate_buy">0%</i>
                                    </div>
                                    <div class="trade_list mt15 clearfix">
                                        <button class="buy_btn cp" id="buy_submit" data-i18n="trade_buy"></button>
                                    </div>
                                    <div class="reg_messlist clearfix" style="width:100%;margin-bottom: 15px;">
                                        <p class="error_confirm" id="sErrorTips" style="display:none;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- sell -->
                        <div class="trade_sell fl trade_info tradesell_mar">
                            <div class="trade_buyfm trade_sellfm">
                                <div class="trabuy_title borderbox clearfix">
                                    <div class="trabuy_titlt fl">
                                        <h2>
                                            <i data-i18n="trade_sell"> </i>
                                            <span class="curCoinName js-upco-name"></span>
                                        </h2>
                                        <p>
                                            <span data-i18n="trade_useMoney"></span>：
                                            <strong><span class="sell_max" id="sell_max">0</span></strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="trade_listbox js-entrust">
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_price"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="sprice" value="0">
                                            <span class="buy-unit">BTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_num"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="snumber">
                                            <span class="curCoinName js-upco-name">LTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_tradeMoney"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="text" id="ssumprice" readonly="">
                                            <span class="buy-unit">BTC</span>
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_pwd"></label>
                                        <div class="tradeipt borderbox">
                                            <input type="password" id="stradepwd">
                                        </div>
                                    </div>
                                    <div class="trade_list clearfix">
                                        <label data-i18n="trade_poundage"></label>
                                        <i style="margin-left: 6px;" id="rate">0.3%</i>
                                    </div>
                                    <div class="trade_list clearfix" style="margin-bottom: 15px;">
                                        <button type="button" value="卖出" class="buy_btn" id="sell_submit" data-i18n="trade_sell"></button>
                                    </div>
                                    <div class="reg_messlist clearfix" style="width:100%;margin-bottom: 15px;">
                                        <p class="error_confirm" id="sErrorTips" style="display:none;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- My open Orders -->
                        <div class="trade_openorder fl borderbox">
                            <div class="trabuy_title" style="position: relative;overflow: hidden;">
                                <h2 style="float: left;" data-i18n="my_orders"></h2>
                                <div style="float: right; margin-right: 5px; font-size: 12px; line-height: 30px;"><a href="http://trade.test.com/user/pOrder" data-i18n="view_all"></a></div>
                            </div>
                            <table class="layui-table up-table" lay-skin="nob" lay-even="" id="myentrust">
                                <colgroup>
                                    <col>
                                </colgroup>
                                <thead>
                                <tr>
                                    <th data-i18n="trade_type"></th>
                                    <th><span data-i18n="trade_price"></span>(<span class="js-unit"></span>)</th>
                                    <th>
                                        <span data-i18n="trade_num"></span> (
                                        <span class="js-upco-name"></span>)
                                    </th>
                                    <th data-i18n="trade_action"></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="trade_mask dn" id="trade_mask">
                            <a class="trade_mask_btn trade_mask_btn_cn" href="javascript:;" data-i18n="text1"></a>
                        </div>
                    </div>
                    <!-- 买卖成交单order -->
                    <div class="order_box clearfix">
                        <div class="buy_order order_info fl">
                            <h2>
                                <i data-i18n="buy_orders"></i>
                                <!-- <i class="auto-flash" data-i18n ="auto_fresh">10秒自动更新</i> -->
                                <select class="showOrder fr js-select-pageNum" data-select="sp1">
                                    <option value="10" data-i18n="display1"></option>
                                    <option value="20" data-i18n="display2"></option>
                                    <option value="30" data-i18n="display4"></option>
                                </select>
                            </h2>
                            <div class="buyorder_con">
                                <table class="layui-table bc_update" lay-skin="line" id="buy_entrust">
                                    <colgroup>
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th data-i18n="sequence"></th>
                                        <th><span data-i18n="trade_price"></span>(<span class="js-unit"></span>)</th>
                                        <th><span data-i18n="trade_num"></span>(<span class="js-upco-name"></span>)
                                        </th>
                                        <th><span data-i18n="completed_money"></span>(<span class="js-unit"></span>)
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="sell_order order_info fr">
                            <h2>
                                <i data-i18n="sell_orders"> </i>
                                <!-- <i class="auto-flash" data-i18n ="auto_fresh">10秒自动更新</i> -->
                                <select class="showOrder fr js-select-pageNum" data-select="sp2">
                                    <option value="10" data-i18n="display1"></option>
                                    <option value="20" data-i18n="display2"></option>
                                    <option value="30" data-i18n="display4"></option>
                                </select>
                            </h2>
                            <div class="buyorder_con sellorder_con">
                                <table class="layui-table bc_update " lay-skin="line" id="sell_entrust">
                                    <colgroup>
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th data-i18n="sequence"></th>
                                        <th><span data-i18n="trade_price"></span>(<span class="js-unit"></span>)</th>
                                        <th><span data-i18n="trade_num"></span>(<span class="js-upco-name"></span>)</th>
                                        <th><span data-i18n="completed_money"></span>(<span class="js-unit"></span>)
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- 深度depth -->
                    <!-- trade history -->
                    <div class="trade_history">
                        <h2>
                            <i data-i18n="trade_history"></i>
                            <!-- <i class="auto-flash" data-i18n ="auto_fresh">10秒自动更新</i> -->
                            <select class="showOrder fr js-select-pageNum" data-select="sp3">
                                <option value="10" data-i18n="display1"></option>
                                <option value="20" data-i18n="display2"></option>
                                <option value="30" data-i18n="display4"></option>
                            </select>
                        </h2>
                        <table class="layui-table" lay-skin="line" id="done">
                            <colgroup>
                                <col width="20%">
                                <col width="20%">
                                <col width="20%">
                                <col width="20%">
                                <col width="20%">
                            </colgroup>
                            <thead>
                            <tr>
                                <th data-i18n="time"></th>
                                <th data-i18n="trade_type"></th>
                                <th><span data-i18n="trade_price"></span>(<span class="js-unit"></span>)</th>
                                <th><span data-i18n="trade_num"></span>(<span class="js-upco-name"></span>)</th>
                                <th><span data-i18n="completed_money"></span>(<span class="js-unit"></span>)</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab_con" style="display: none">
                    <!-- div id="newscon"></div>
                <ul id="newspage" class="page-wrap"></ul> -->
                    <div class="content_warp">
                        <div class="datum">
                            <div class="explain clearfix">
                                <div class="explain_ico lf">
                                    <img src="http://st.test.com/xnb_home/img/logo.png" alt="货币图标" id="pa_icon">
                                </div>
                                <div class="cont lf">
                                    <div class="explain_name font_color_big">
                                        <em id="in_coin_name"></em>
                                        <span data-i18n="digital_assets"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="intro">
                                <span class="segmentation"></span>
                                <span class="title" data-i18n="desc"></span>
                                <p id="in_desc"></p>
                            </div>
                            <div class="list_link">
                                <ul class="clearfix" id="select_url">
                                    <li data-select="gfwz">
                                        <a href="javascript:;" target="_blank" data-i18n="office_site"></a>
                                    </li>
                                    <li data-select="lt">
                                        <a href="javascript:;" target="_blank" data-i18n="forum"></a>
                                    </li>
                                    <li data-select="qbxz">
                                        <a href="javascript:;" target="_blank" data-i18n="download_wallet"></a>
                                    </li>
                                    <li data-select="qkllq">
                                        <a href="javascript:;" target="_blank" data-i18n="block_browser"></a>
                                    </li>
                                </ul>
                            </div>
                            <!-- <div class="mid_wrap clearfix">
                            <div class="warehouse lf">
                                <span class="segmentation"></span>
                                <span class="title">持仓及资金变化</span>
                                <div class="item clearfix first">
                                    <div class="col-1 lf">
                                        <p>持仓账户数</p>
                                        <span class="font_color_big">48363  个</span>
                                    </div>
                                    <div class="col-2 lf">
                                        <p>持仓总币数</p>
                                        <span class="font_color_big">10165.04  个</span>
                                    </div>
                                    <div class="col-3 lf">
                                        <p>人均持币</p>
                                        <span class="font_color_big">0.21  个</span>
                                    </div>
                                </div>
                                <div class="item clearfix">
                                    <div class="col-1 lf">
                                        <p>24小时资金流入</p>
                                        <span class="font_color_big">4379335 CNY</span>
                                    </div>
                                    <div class="col-2 lf">
                                        <p>24小时资金流出</p>
                                        <span class="font_color_big">2894196 CNY</span>
                                    </div>
                                    <div class="col-3 lf">
                                        <p>24小时资金变化</p>
                                        <span class="font_color_big">1485138 CNY</span>
                                    </div>
                                </div>
                                <div class="item clearfix">
                                    <div class="col-1 lf">
                                        <p>一周资金流入</p>
                                        <span class="font_color_big">39715518 CNY</span>
                                    </div>
                                    <div class="col-2 lf">
                                        <p>一周资金流出</p>
                                        <span class="font_color_big">36826623 CNY</span>
                                    </div>
                                    <div class="col-3 lf">
                                        <p>一周资金变化</p>
                                        <span class="font_color_big">2888894 CNY</span>
                                    </div>
                                </div>
                                <div class="count_times">
                                    <div class="count_times_top">
                                        <p>统计时间：<span> 08-25 16:07</span></p>
                                    </div>
                                    <div class="count_times_bottom">
                                        <p>免责声明：<span> 本数据来自时代交易平台，采集范围不包括平台以外的交易数据，旨在提升交易透明度，仅供用户参考，不构成投资建议，任何根据本数据进行交易的行为须自担风险</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="rankingList lf clearfix">
                                <span class="segmentation"></span>
                                <span class="title">排行榜单</span>
                                <a id="self_anymous" href="javascript:;" data-isany="1">隐藏我的持仓</a>
                                <ul>
                                    <li><i>NO.01</i><span>***BTC</span></li>
                                    <li><i>NO.02</i><span>***BTC</span></li>
                                    <li><i>NO.03</i><span>323BTC</span></li>
                                    <li><i>NO.04</i><span>145BTC</span></li>
                                    <li><i>NO.05</i><span>***BTC</span></li>
                                    <li><i>NO.06</i><span>***BTC</span></li>
                                    <li><i>NO.07</i><span>71BTC</span></li>
                                    <li><i>NO.08</i><span>63BTC</span></li>
                                    <li><i>NO.09</i><span>57BTC</span></li>
                                    <li><i>NO.10</i><span>***BTC</span></li>
                                </ul>
                                <div class="countTime">
                                    统计时间 <span>08-18 18:10</span>
                                </div>
                            </div>
                        </div> -->
                            <div class="parameter">
                                <span class="segmentation"></span>
                                <span class="title" data-i18n="technical_parameters"></span>
                                <ul class="clearfix">
                                    <li>
                                        <span data-i18n="english_name"></span>
                                        <span class="name" id="pa_coin_ename"></span>
                                    </li>
                                    <li>
                                        <span data-i18n="chinese_name"></span>
                                        <span class="name" id="pa_coin_name"></span>
                                    </li>
                                </ul>
                                <ul class="clearfix">
                                    <li>
                                        <span data-i18n="english_desc"></span>
                                        <span class="name" id="pa_abbreviation"></span>
                                    </li>
                                    <li>
                                        <span data-i18n="developer"></span>
                                        <span class="name" id="pa_creater"></span>
                                    </li>
                                </ul>
                                <ul class="clearfix">
                                    <li>
                                        <span data-i18n="block_time"></span>
                                        <span class="name" id="pa_blockTime"></span>
                                    </li>
                                    <li>
                                        <span data-i18n="release_date"></span>
                                        <span class="name" id="pa_publishTime"></span>
                                    </li>
                                </ul>
                                <ul class="clearfix">
                                    <li>
                                        <span data-i18n="core_algorithm"> </span>
                                        <span class="name" id="pa_algorithm"></span>
                                    </li>
                                    <li class="par_last">
                                        <span data-i18n="coin_amount"></span>
                                        <span class="name" id="pa_total_vol"></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="recommend">
                                <span class="segmentation"></span>
                                <span class="title" data-i18n="other"></span>
                                <ul class="clearfix" id="coin_rem">
                                    <!--                                         <li><a href="#"><img
                                        src="http://bitccpicture-1251755124.cosgz.myqcloud.com/news/2017/03/1490686951283953.png">
                                    <p>时代币</p></a></li>
                                <li><a href="#"><img
                                        src="http://bitccpicture-1251755124.cosgz.myqcloud.com/news/2017/08/c18c28986943ce6e51ae7d203097b147.jpg"><p>奖赏币</p></a></li>
                                <li><a href="#"><img
                                        src="http://bitccpicture-1251755124.cosgz.myqcloud.com/news/2017/03/1490837904927836.png">
                                    <p>微币</p></a></li>
                                <li><a href="#"><img
                                        src="http://bitccpicture-1251755124.cosgz.myqcloud.com/news/2017/03/1490687805618366.png">
                                    <p>比特股</p></a></li>
                                <li><a href="#"><img
                                        src="http://bitccpicture-1251755124.cosgz.myqcloud.com/news/2017/07/e8a65352af59dd629e347aff4c949132.jpg">
                                    <p>活力币</p></a>
                                </li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- trade end -->
    <!-- 风险提示 -->
    <!--     <div style="background: #fffee3;">
<div class="warning">
    <img src="http://st.test.com/xnb_home/img/warning.png">
    <div class="warn-tips" data-i18n="warn_tips"></div>
</div>
</div> -->
</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/trade_center/main"></script>
</body>

</html>