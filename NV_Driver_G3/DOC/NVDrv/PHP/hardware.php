<?php
/**
 * Created by PhpStorm.
 * User: Mojies
 * Date: 9/17/2015
 * Time: 9:55 AM
 */
include("page_frame.php");
include("pkgMySQL.php");
$buildHtml = new page_frame();
$fdMySQL = new pkgMySQL();

$mySQL_User = $fdMySQL->pf_getMysqlUser();



$pContent='
<p style="margin-top:30px;">最后修改时间：2015 年 9 月 17 日</p>
<p style="margin-top:30px;">修订人：Mojies</p>
<hr/>
<ul style="list-style-type:none">

    <li> <a href="#Itd_h_id">1. 说明</a> </li>
    <li> <a href="#Pro_h_id">2. 机型</a>
        <ul style="list-style-type:none">
            <li><a href="#prosmod_h_id">2.1 各个机型的模块</a></li>
            <li> <a href="#Chip_h_id">2.2 芯片资料</a></li>
            <li> <a href="#She_h_id">2.3 原理图</a></li>

        </ul>
    <li><a href="#Mod_h_id">3. 功能模块</a>
        <ul style="list-style-type:none">
            <li><a href="#but_h_id">3.1 按键</a></li>
            <li><a href="#StaLi_h_id">3.2 状态灯</a></li>

            <li><a href="#DaN_h_id">3.3 白天黑夜检测（DND）</a></li>
            <li><a href="#IfrLi_h_id">3.4 红外灯</a></li>
            <li><a href="#DbL_h_id">3.5 双镜头</a></li>
            <li><a href="#IfrFilt_h_id">3.6 IRCut</a></li>

            <li><a href="#AuPl_h_id">3.7 音频控制</a></li>

            <li><a href="#CoF_h_id">3.8 散热风扇</a></li>
            <li><a href="#NiLi_h_id">3.9 小夜灯</a></li>
            <li><a href="#PTZ_h_id">3.10 云台</a></li>
            <li><a href="#RTC_h_id">3.11 实时时钟</a></li>
            <li><a href="#Temp_h_id">3.12 温度检测</a></li>
            <li><a href="#Humi_h_id">3.13 湿度检测</a></li>
            <li><a href="#PIR_h_id">3.14 PIR</a></li>
            <li><a href="#DoorBell_h_id">3.15 DOLL BELL</a></li>
         </ul>
    </li>
</ul>
<a id="title_end_a"></a>
<hr class="hr_2"/>
<h4><a name="Itd_h_id"></a>1. 说明</h4>
<p style="color:red;">“时间就是金钱，效率就是生命”</p>
<p>
    在驱动的开发过程中，不止一次遇到各种各样的不协调问题，包括同一款机型相同原理的功能，控制方式不同，不同机型，功能相同的却控制方式不同，
    进行硬件调试时，往往因为线序的问题，将程序改来改去，而时间往往花费在了，查找问题，代码交互，版本升级这些本不应该频繁发生的地方。
    虽然，随着经验知识的积累，硬件技术的日益革新，在产品开发的长河中，保持一个标准时很难的。但是，为一个版本，做一个统一的说明，
    一是可以减少因程序员的误解而造成代码的缺陷；而且也能有效的避免因突发的知识短板，要去重新询问硬件开发人员相关的技术问题，
    最后导致设计思路被打断，（ 众所周知，当人的脑袋一旦脱离了一条思维轨道，在回到原来的断点，是很需要时间，而且还不能完整的还原现场）；
    第三个方面，最终在生产，调试中出现问题，也能很快的定位到问题点，先不说责任在谁，但能节省大量时间这是肯定的。
</p>
<p>
    这边文档提供的与硬件相关的资料只和目前正在活动的机型相关，以前因为某些原因 stop 的，或者 delete 的，在这里就不再赘述，
    但是，以后每一个进入开发进程的机型都要在此处做记录。
</p>
<dl>
    <dt>约定:</dt>
    <dd>1. 无论电路多么简单，即使是只有一个io口控制一个LED灯，只要他属于一个外接设备，起到一定的作用，
    我们把与之功能相同的集合统称为一个 <code>模块</code> （例如，状态灯，按键，云台）。</dd>
    <dd>2. 我们通常把 B14 叫做 <code>即视通二</code> ，把 D04 叫做 <code>即视通三</code> ,而把使用当前现在这个程序框架的机器集合叫做 <code>三代摄像机</code> 。</dd>
    <dd>3. 在标示引脚状态的时候，<code>HIGH</code> 代表电平为高，电压接近VCC（相对的），<code>LOW</code> 代表电平为低，电压接近 GND（ditto）</dd>
    <dd>4. 硬件需要提供的资料：
        <ul><li>1. 产品命名列表</li><li>2. 机型的原理图（持续更新）</li><li>3. 机型的硬件接口说明（随产品持续更新）</li>
        <li>4. 某机型中涉及到的，驱动所需参考的芯片datesheet</li></ul>
    </dd>
</dl>

<h4><a name="Pro_h_id"></a>2. 机型</h4>
<p>详细信息，请参考首页驱动支持机型条目，或者移步<a href="../DATA/netview%20硬件项目简介.html">DOC</a>.)</p>

<h5><a name="Chip_h_id"></a>2.1 约定</h5>
<table rules="all" align="center" frame="border" width="61%" >
<tr bgcolor="#4BACC6"><th>硬件模块名称</th><th>简写</th><th>驱动代码中的别名</th></tr>
<tr><td>按键</td><td>Button</td><td>Button</td></tr>
<tr><td>状态灯</td><td>StaLi</td><td>StaLi</td></tr>
<tr><td>白天黑夜检测</td><td>D&N Detect</td><td>LDR</td></tr>
<tr><td>红外灯</td><td>IfrLi</td><td>InfraredLi</td></tr>
<tr><td>红外滤光片</td><td>IRCut</td><td>IfrFilter</td></tr>
<tr><td>双镜头</td><td>DubLens</td><td>DoubLens</td></tr>
<tr><td>音频控制</td><td>AudioPlug</td><td>AudioPlug</td></tr>
<tr><td>散热风扇</td><td>CoolFan</td><td>CoolFan</td></tr>
<tr><td>云台</td><td>PTZ</td><td>PanTilt</td></tr>
<tr><td>小夜灯</td><td>NiLi</td><td>NightLi</td></tr>
<tr><td>温度检测</td><td>TempDetect</td><td>TempMonitor</td></tr>
<tr><td>湿度检测</td><td>HumiDetect</td><td>HumiMonitor</td></tr>
<tr><td>实时时钟</td><td>RTC</td><td>RTC</td></tr>
<tr><td>门铃</td><td>DoorBell</td><td>DoorBell</td></tr>
<tr><td>红外移动侦测</td><td>PIR</td><td>PIR</td></tr>
</table>

<h5><a name="prosmod_h_id"></a>2.1 各个机型的模块</h5>
';


$table_date = $fdMySQL->pf_getDeviceSuppModList();
$Count_i = $fdMySQL->pf_getColNum();
$table = $buildHtml->pf_builtATable('NULL','表2.1.1 各机型模块',2,$Count_i,$table_date);
$pContent .= $table;


$pContent .='<h5><a name="Chip_h_id"></a>2.2 芯片资料</h5>';

$list_date = $fdMySQL->pf_getDocAPath('chip');
$Count_i = $fdMySQL->pf_getColNum();
$list = $buildHtml->pf_builtAList( $Count_i, $list_date, 12 );
$pContent .= $list;

$pContent .='<h5><a name="She_h_id"></a>2.3 原理图</h5>';

$list_date = $fdMySQL->pf_getDocAPath('pro');
$Count_i = $fdMySQL->pf_getColNum();
$list = $buildHtml->pf_builtAList( $Count_i, $list_date, 11 );
$pContent .= $list;

$pContent.='
<h4><a name="Mod_h_id"></a>3 功能模块</h4>
<p>以下，将会对各个硬件功能模块的相应机型的驱动方式做详细的说明，需要注意的是，以后下列数据将不会被更新，如果有新的机型
出现，那么对驱动的设计要求会像 F10 一样，由硬件部门提供一个驱动设计规范文档</p>
<h4><a name="Mod_h_id"></a>3 功能模块</h4>
<p>从以上的机型，主要归为一下几个功能模块：按键、状态灯、白天黑夜检测、红外灯、双镜头、IRCUT、音频控制、散热风扇、小夜灯、云台、
    实时时钟、温湿度检测。</p>

<h5><a name="but_h_id"></a>3.1 按键</h5>
<p>同一代摄像机（第二代，或第三代）中，属于自己公司设计的电路中按键的控制引脚就目前而言是没有差别的，但不保证以后会产生变化。</p>
<table border="1" id="btu_tb0_id">
    <caption>表3.1.1 按键控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>响应方式</th>
    </tr>
</table>

<h5><a name="StaLi_h_id"></a>3.2 状态灯</h5>
<p>在三代机型中，大部分机型都预留了多个状态灯的控制口，但在实际应用中都只用到了一个，因此这里列举出来的只有被用到的那些控制口</p>
<p style="color: cornflowerblue">ON：代表开灯的情况</p>
<p style="color: darkslategray">OFF：代表关灯的情况</p>
<table border="1" id="StaLi_tb0_id">
    <caption>表3.2.1 状态灯控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
        <th>颜色</th>
    </tr>
</table>

<h5><a name="DaN_h_id"></a>3.3 白天黑夜检测</h5>
<p>
    白天黑夜检测（Day & Night Detect）在即视通二中，白天黑夜检测由硬件电路完成，芯片只需要读写引脚电平即可，但要注意，
    即视通二的白天黑夜做了一个防止状态抖动的功能，使用时记得加上。在三代摄像机中是利用 ADC 模块来读写光敏二极管的状态，防抖要在软件中作</p>
<table border="1" id="DaN_tb0_id">
    <caption>表3.3.1 白天黑夜检测控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>响应控制方式</th>
    </tr>
</table>

<h5><a name="IfrLi_h_id"></a>3.4 红外灯</h5>
<p style="color: cornflowerblue">ON：代表开灯的情况</p>
<p style="color: darkslategray">OFF：代表关灯的情况</p>
<table border="1" id="IfrLi_tb0_id">
    <caption>表3.4.1 红外灯控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<h5><a name="DbL_h_id"></a>3.5 双镜头</h5>
<p>目前双镜头这个功能值用在球泡机上，控制方式和控制LED灯差不多，但具体实现还是要看驱动电路。
操作的时候最好先禁用该禁用的，然后在使能该使能的。</p>
<table border="1" id="dbl_tb0_id">
    <caption>表3.5.1 双镜头控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<h5><a name="IfrFilt_h_id"></a>3.6 IRCut</h5>
<p>
    控制方式为给 IRCUT 提供一个正负的电势，实现滤光片的切换，然后禁能模块。但基于电路，芯片的不同，有些许不同。
    想 B14 完全由电路搭建而成的，通过三个引脚控制(两个控制电势的正负，一个控制模块的使能)
    而在三代机普遍用的芯片，所以只需要两个引脚（一个控制电势的正负，一个控制模块的使能）
</p>
<table border="1" id="irc_tb0_id">
    <caption>表3.6.1 IrCut控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<h5><a name="AuPl_h_id"></a>3.7 音频控制</h5>
<p>
    即视通二代采用的是音频处理芯片，所以在驱动里实现的功能相对多一些，包括控制扬声器和麦克风的开关，分辨率的调节，音量的调节，
    而在三代机中只在音频放大模块加上了使能的功能，所以只具有扬声器开关的功能。
</p>
<table border="1" id="aupl_tb0_id">
    <caption>表3.7.1 音频控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<h5><a name="CoF_h_id"></a>3.8 散热风扇</h5>
<p>
    目前只有 D11 具有散热功能，受 PWM_0 调速，具体转速要根据风扇在具体的试验中取得。
</p>


<h5><a name="NiLi_h_id"></a>3.9 小夜灯</h5>
<p>
   同样的，小夜灯也只在 D11 中用到，控制IO为 GPIO5_3，高电平ON（灯亮），低电平OFF（灯灭）,配合 PWM_1 实现灯的亮暗调节。
</p>


<h5><a name="PTZ_h_id"></a>3.10 云台</h5>
<p style="padding-bottom:0; margin-bottom: 2px">
    云台目前只存在于 D11 和 F05 中，D11 机中的云台无限位功能。在下表中:
</p>
    <dl style="padding-top: 0; margin-top: 2px;">
        <dd>A,B,C,D 分别代表步进电机的 A,B,C,D 线序</dd>
        <dd>HH 代表水平方向开始的限位检测接口</dd>
        <dd>HT 代表水平方向结束的限位检测接口</dd>
        <dd>VH 代表垂直方向开始的限位检测接口</dd>
        <dd>VT 代表垂直方向结束的限位检测接口</dd>
    </dl>

<table border="1" id="ptz_tb0_id">
    <caption>表3.10.1 云台控制方式</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<h5><a name="RTC_h_id"></a>3.11 实时时钟</h5>
<p>
    B14 机型内部用的核心芯片为海思 HI3507，此款芯片内部不包含实时时钟，因此此机型外置了一款
    <a href="../DATA/CHIP/PCF8563.pdf"> PCF8563 </a>的实时时钟芯片。而目前而言，其他机型并不涉及实时时钟这一块的操作。
</p>

<h5><a name="Temp_h_id"></a>3.12 温度检测</h5>
<p>
    目前只存在 D11 机型当中，利用 ADC 转换采集 <a href="../DATA/MOD/NTC%20参值对照表.jpg">NTC</a> 模块 Sensor 的值，然后再计算出实际温度。
</p>
<p>
    D11 机型中使用的是 10K 的 NTC 电阻。
</p>

<h5><a name="Humi_h_id"></a>3.13 湿度检测</h5>



<h5><a name="PIR_h_id"></a>3.14 红外移动侦测</h5>

<table border="1" id="PIR_tb0_id">
    <caption>表3.14.1 红外移动侦测</caption>
    <tr>
        <th>机型</th>
        <th>检测引脚</th>
        <th>检测方式</th>
    </tr>
</table>



<h5><a name="DoorBell_h_id"></a>3.15 门铃控制</h5>
<table border="1" id="DoorBell_tb0_id">
    <caption>表3.15.1 门铃控制</caption>
    <tr>
        <th>机型</th>
        <th>控制引脚</th>
        <th>控制方式</th>
    </tr>
</table>

<script type="text/javascript">
    var but_info = [];
    but_info[0] = ["B14","GPIO 6_3","UP：HIGH DOWN：LOW"];
    but_info[1] = ["D01","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[2] = ["D04","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[3] = ["D11","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[4] = ["F05","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[5] = ["F08","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[6] = ["F09","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[7] = ["F10","GPIO 0_1","UP：HIGH DOWN：LOW"];
    but_info[8] = ["F11","GPIO 5_1","UP：HIGH DOWN：LOW"];
    but_info[9] = ["F14","GPIO 0_3",""];
    CreateTableFormArra("btu_tb0_id",but_info);
    var sta_info = [];
    sta_info[0] = ["B14","GPIO 6_0","ON:HIGH OFF:LOW","绿色"];
    sta_info[1] = ["D01","GPIO 0_4","ON:LOW OFF:HIGH","绿色"];
    sta_info[2] = ["D04","GPIO 0_4","ON:LOW OFF:HIGH","绿色"];
    sta_info[3] = ["D11","GPIO 0_4","ON:HIGH OFF:LOW","红灯"];
    sta_info[4] = ["F05","GPIO 0_4","ON:LOW OFF:HIGH","蓝色"];
    sta_info[5] = ["F08","GPIO 0_3","ON:LOW OFF:HIGH","蓝色"];
    sta_info[6] = ["F09","GPIO 0_4","ON:LOW OFF:HIGH","绿色"];
    sta_info[7] = ["F10","GPIO5_2<br/>GPIO5_3","ON:HIGHT OFF:LOW<br>+PWM控制","蓝色<br>白色"];
    sta_info[8] = ["F11","GPIO 1_6","ON:LOW OFF:HIGH","蓝色"];
    sta_info[9] = ["F14","GPIO5_2<br/>GPIO5_3","ON: OFF: <br/>+ PWM 控制","红色<br/>蓝色"];
    CreateTableFormArra("StaLi_tb0_id",sta_info);
    var dan_info = [];
    dan_info[0] = ["B14","GPIO 7_3 & GPIO 7_7","GPIO7_3 用于检测状态的转换<br/>GPIO 7_7 用于控制状态抖动"];
    dan_info[1] = ["D01<br/>D04<br/>D11<br/>F05<br/>F08<br/>F09<br/>F11<br/>F10","ADC_0 通道","采样电平数据"];
    CreateTableFormArra("DaN_tb0_id",dan_info);
    var ifrli_info = [];
    ifrli_info[0] = ["B14","GPIO 7_2","ON:HIGH OFF:LOW"];
    ifrli_info[1] = ["D01<br/>D04<br/>D11<br/>F05<br/>F08<br/>F09<br/>109","GPIO 0_0","ON:HIGH OFF:LOW"];
    ifrli_info[2] = ["F11","GPIO 9_3","ON:HIGH OFF:LOW"];
    CreateTableFormArra("IfrLi_tb0_id",ifrli_info);
    var dublen_info = [];
    dublen_info[0] = [ "D11", "GPIO3_1 & GPIO3_0","GPIO3_1 控制白天摄像头<br/>GPIO3_0 控制夜用摄像头<br>输出为高的时候使能镜头" ];
    CreateTableFormArra("dbl_tb0_id",dublen_info);
    var irc_info= [];
    irc_info[0] = ["B14","GPIO7_0<br/>GPIO7_1<br/>GPIO7_4","GPIO7_0:GPIO7_1<br/>1:0 阻止红外光通过<br/>0:1 允许红外光通过" +
    "<br/>GPIO7_4 低电平使能"];
    irc_info[1] = ["D01<br/>D04<br/>F05<br/>F08<br/>F09","GPIO5_3","GPIO5_3<br/>0 阻止红外光通过<br/>1 允许红外光通过"];
    irc_info[2] = ["F11","GPIO9_0","GPIO9_0<br/>0 阻止红外光通过<br/>1 允许红外光通过"];
    irc_info[3] = ["F10","En:GPIO7_7<br/>Switch:GPIO7_6","GPIO7_7 低电平使能<br/>GPIO7_6<br/>0 阻止红外光通过<br/>1 允许红外光通过"];
    irc_info[4] = ["F14","GPIO0_1<br/>GPIO0_2","切换至白天<br/>切换至晚上<br/>IO 口的常态为低电平<br/>相应的引脚产生脉冲之后切换至相应状态"];
    CreateTableFormArra("irc_tb0_id",irc_info);
    var aupl_info = [];
    aupl_info[0] = ["B14","GPIO3_4(IIC SCL)<br/>GPIO3_3(IIC_SDA)<br>",
    "采用模拟IIC更 TLV320AIC32B 通信<br/>具体操作请看<a href=/DATA/CHIP/TLV320AIC23B.pdf >TLV320AIC32B</a>芯片资料"];
    aupl_info[1] = ["D01<br/>D04<br/>D11<br/>F05<br/>F08<br/>F09<br/>F10","GPIO0_6","ON:LOW OFF:HIGH"];
    CreateTableFormArra("aupl_tb0_id",aupl_info);
    var ptz_info = [];
    ptz_info[0] = ["D11","水平方向<br/>A:GPIO4_4<br/>B:GPIO4_5<br/>C:GPIO4_6<br/>D:GPIO4_7" +
    "<br/>垂直方向<br/>A:GPIO4_0<br/>B:GPIO4_1<br/>C:GPIO4_2<br/>D:GPIO4_3","IO 口的输出经驱动芯片会反相，" +
    "<br/>所以控制方式上要适当注意一下，<br/>特别要注意结尾状态" +
    "<br/><a href=/DATA/MOD/人众步进电要图纸.pdf>步进电机资料"];
    ptz_info[1] = ["F05","水平方向<br/>A:GPIO9_0<br/>B:GPIO9_1<br/>C:GPIO9_2<br/>D:GPIO9_3" +
    "<br/>垂直方向<br/>A:GPIO9_4<br/>B:GPIO9_5<br/>C:GPIO9_6<br/>D:GPIO9_7" +
    "<br/>限位<br/>HH:GPIO5_4<br/>HT:GPIO5_5<br/>VH:GPIO5_7<br/>VT:GPIO5_6", "IO 口的输出与步进电机收到的信号同相" +
    "<br/>有限位"+"<br/><a href=/DATA/MOD/人众步进电要图纸.pdf>步进电机资料"];
    CreateTableFormArra("ptz_tb0_id",ptz_info);
    var PIR_info = [];
    PIR_info[0] = ["F10","GPIO1_0","正常情况下，该引脚维持高电平<br/>当检测到移动物体之后会产生一系列脉冲<br/>脉冲的宽度" +
    "频率跟被检测物体有关"];
    CreateTableFormArra("PIR_tb0_id",PIR_info);
    var doorbell_info = [];
    doorbell_info[0]  =["F10","GPIO1_0","常态保持高电平<br/>发送一个500ms的高电平启动门铃响应"];
    CreateTableFormArra("DoorBell_tb0_id",doorbell_info);
</script>
';





$html=$buildHtml->pf_getPage($pContent);
echo $html;
