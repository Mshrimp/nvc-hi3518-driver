<?php
/**
 * Created by PhpStorm.
 * User: Mojies
 * Date: 9/17/2015
 * Time: 4:58 PM
 */
include("page_frame.php");
include("pkgMySQL.php");
$buildHtml = new page_frame();
$fdMySQL = new pkgMySQL();

$mySQL_User = $fdMySQL->pf_getMysqlUser();

$pContent ='
<p style="margin-top:30px;">最后修改时间：2015 年 8 月 24 日</p>
<p style="margin-top:30px;">修订人：Mojies</p>
<hr/>
<h3 style="text-align: center">目录</h3>
<ul style="list-style-type: none">
    <li><a href="#outline_id_h">1. 概述</a></li>
    <li><a href="#comu_insmod_id_h">2. 通讯装载方式</a></li>
    <li><a href="#msg_fomat_id_h">3. 消息指令格式</a></li>
    <li><a href="#msgtype_id_h">4. 各个消息类型的定义</a></li>
    <li>
        <ul style="list-style-type:none;">
            <li><a href="#mmsgtype_id_h">4.1 消息主类型</a></li>
            <li><a href="#smsgtype_id_h">4.2 消息子类型</a></li>
            <li>
                <ul style="list-style-type: none;">
                    <li><a href="#device_id_h">4.2.1 SubMessageType:DEVICE</a></li>
                    <li><a href="#but_h_id">4.2.2 SubMessageType:BUTTON</a></li>
                    <li><a href="#ldr_h_id">4.2.3 SubMessageType:LDR</a></li>
                    <li><a href="#irc_id_h">4.2.4 SubMessageType:IRC</a></li>
                    <li><a href="#nili_id_h">4.2.5 SubMessageType:Infrared Light</a></li>
                    <li><a href="#stali_id_h">4.2.6 SubMessageType:State Light</a></li>
                    <li><a href="#ptz_id_h">4.2.7 SubMessageType:PTZ</a></li>
                    <li><a href="#nili_id_h">4.2.8 SubMessageType:NightLight</a></li>
                    <li><a href="#aupl_id_h">4.2.9 SubMessageType:Audio Plug</a></li>
                    <li><a href="#tm_id_h">4.2.A SubMessageType:Temp Monitor</a></li>
                    <li><a href="#hm_id_h">4.2.B SubMessageType:Humidity Monitor</a></li>
                    <li><a href="#dublen_id_h">4.2.C SubMessageType:DoubleLens</a></li>
                    <li><a href="#resetio_id_h">4.2.D SubMessageType:Reset IO</a></li>
                    <li><a href="#rtc_id_h">4.2.E SubMessageType:RTC</a></li>
                    <li><a href="#pir_id_h">4.2.F SubMessageType:PIR</a></li>
                    <li><a href="#doorbell_id_h">4.2.10 SubMessageType:DoorBell</a></li>
                </ul>
            </li>
        </ul>
    </li>
    <li><a href="#appendix1_id_h">5. 附件1——兼容老协议 MsgType</a></li>
    <li><a href="#appendix2_id_h">6. 附件2——内文注解</a></li>
    <li><a href="#appendix3_id_h">7. 附件3——修改记录</a></li>
</ul>
<hr class="hr_1"/>

<h3>驱动和应用程序接口文档</h3>
<h4><a name="outline_id_h"></a>1. 概述</h4>
<p>
    驱动程序向应用程序屏蔽不同机型的外设区别和复杂底层处理，通过统一的接口，支持应用程序通过消息指令方式查询及控制所有外部设备。
    目前，可把驱动和应用程序所有交互消息分成四种类型:
</p>
<ol>
    <li>设备发送查询命令，驱动返回查询结果。</li>
    <li>设备发送控制命令，驱动返回控制结果。</li>
    <li>设备发送控制命令，不需要驱动返回控制结果。(暂无实际消息类型，未来如果有频繁控制请求再添加)</li>
    <li> 外部设备状态发生变化或触发事件时，驱动主动反馈消息给应用程序，不需要应用程序再发响应消息给驱动程序。</li>
</ol>
<p>
    应用程序通过open驱动映射的设备节点，通过write和read标准I/O函数和驱动进行通讯。驱动支持应用程序订阅事件或外设状态变化消息，
    默认不订阅，当应用程序通过订阅事件指令请求接收事件或外设状态变化消息时，驱动应该把所有事件或外设状态变化消息反馈给应用程序。
</p>
<p>
    驱动应支持和多个应用程序之间的通讯。驱动和应用程序之间的通讯采用异步方式，但应该保持相同消息类型之间的处理同步。
    举例来说，应用程序先后发送了云台控制和查询ircut状态指令，可以先返回ircut状态响应，后返回云台控制指令响应；
    应用程序先后发送了两个云台控制指令1和指令2，则应该先返回指令1的控制指令响应，再返回指令2的控制指令响应。
    事件或外设状态变化消息使用异步方式上报，无需判断当前是否在处理查询或控制请求。
    驱动应及时返回响应消息给应用程序，一般的查询控制命令理论上应在3s内返回，云台控制命令应在15s内返回。
</p>
<p>
    部分应用程序不需要参与的外设，加载驱动时，驱动应完成响应的设备初始化操作，只是后续无相应控制操作。
</p>
<h4><a name="comu_insmod_id_h"></a>2. 通讯装载方式</h4>
<p>
    通过以下命令格式装载/卸载驱动:（在 表2.1 中会列举出各机型的附加命令）
</p>
<dl><dt>装载设备驱动:</dt>
    <dd>insmod /mnt/mtd/modules/Nv_Driver/Nv_Driver.ko Chip=3518C Pro=D01</dd>
    <dd>insmod /mnt/mtd/modules/Nv_Driver/Nv_Driver.ko Chip=3518C Pro=D11 Pro_Cmd=NiLi_ON（NiLi_OFF）</dd>
</dl>
<dl><dt>卸载设备驱动:</dt>
    <dd>rmmod Nv_Driver</dd>
</dl>
<p>
    其中，Chip 为设备使用的芯片方案名称，Pro 为设备产品类型，某些机型有特殊的命令需求，因此根据机型的不同装在驱动的时候，
    可能还要跟一些别的参数，比如 D11 机型就有一个 NiLi 的参数，该参数是为了控制驱动加载时初始化小夜灯的状态而设置的，
    ON 代表灯亮，OFF 代表灯灭,当然这个参数不选用对加载驱动也不会产生什么影响，驱动对这一参数设定的功能将会沿用默认设定。<br/>
    Chip 和 Pro 取值具体定义如下：
</p>
<pre>
    // Chip 取值集合
    typedef enum __NvcChipType
    {
        NVC_CHIP_3507R = 0x3507,
        NVC_CHIP_3518C = 0x3518c,
        NVC_CHIP_3518E = 0x3518e,
        NVC_CHIP_BUTT
        //REMAIN
    }NvcChipType_E;

    // Pro 取值集合
    typedef enum __NvcDeviceType
    {
        NVC_DEVICE_B14 = 0xB14, // -->(硬件)B14    （即视通II）
        NVC_DEVICE_D01 = 0xd01, // -->(硬件)D01    矩形netview 机型
        NVC_DEVICE_D03 = 0xd03, // -->(硬件)D03    DOREL MO136 机型
        NVC_DEVICE_D04 = 0xd04, // -->(硬件)D04    圆形netview 机型
        NVC_DEVICE_D11 = 0xd11, // -->(硬件)D11    Awox 球泡机型
        NVC_DEVICE_F05 = 0xf05, // -->(硬件)F05    中本云台机
        NVC_DEVICE_F08 = 0xf08, // -->(硬件)F08    水滴
        NVC_DEVICE_F09 = 0xf09, // -->(硬件)F09
        NVC_DEVICE_F10 = 0xf10, // -->(硬件)F10
        NVC_DEVICE_F11 = 0x301, // -->(硬件)F11
        NVC_DEVICE_F14 = 0x302, // -->(硬件)F14
        NVC_DEVICEBUTT
        // REMAIN
    }NvcDeviceType_E;
</pre>

<table border="1" align="center">
    <caption >表2.1 驱动加载时的附加命令</caption>
    <tr>
        <th width="100px">机型</th>
        <th width="500px">子命令( Pro_Cmd )</th>
    </tr>
    <tr><td>D04</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>D11</td>
        <td style="text-align: left">
            NiLI_ON: 打开小夜灯，亮度达到 100<br/>NiLi_OFF: 关闭小夜灯
        </td>
    </tr>
    <tr><td>F05</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>F08</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>F09</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>F10</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>F11</td>
        <td style="text-align: left">无</td>
    </tr>
    <tr><td>F14</td>
        <td style="text-align: left">无</td>
    </tr>
</table>
<h4><a name="msg_fomat_id_h"></a>3. 消息指令格式</h4>
<p>
    驱动和应用程序之间的 消息头+消息体 的方式，其中，消息体为各消息类型的具体结构体定义。如果指令头中的消息体长度为 0，则消息体不存在。
</p>
<div >
    <!--<span style="display: block;font-weight: bold">消息头格式:</span>-->
    <table border="1" align="center">
        <caption >表3.1 消息头格式</caption>
        <tr>
            <th>2 Bytes</th>
            <th>1 Bytes</th>
            <th>1 Bytes</th>
            <th>2 Bytes</th>
            <th>1 Bytes</th>
            <th>1 Bytes</th>
            <th>4 Bytes</th>
        </tr>
        <tr>
            <td>指令头魔术字(N)</td>
            <td>消息子类型(N)</td>
            <td>消息主类型(N)</td>
            <td>消息体长度(N)</td>
            <td>设备号(N)</td>
            <td>错误码(N)</td>
            <td>预留</td>
        </tr>
    </table>
</div>
<dl>
    <dt>约定：</dt>
    <dd>指令头魔术字 —— 默认为51843(0xCA83)，如不一致，驱动可不返回响应消息</dd>
    <dd>消息子类型 —— 同一类型的主消息中，拥有三种子类型<br/>
        &nbsp;&nbsp;1.应用层向驱动层的请求消息（Request）；<br/>
        &nbsp;&nbsp;2.驱动层向应用层返回得响应消息（Response）；<br/>
        &nbsp;&nbsp;3.驱动层接受了应用层的配置后，主动发送的上报消息（Report）。
    </dd>
    <dd>
        消息主类型 —— 这个字节主要标示各个模块，将消息细分到各个模块，之所以将主消息类型排到子消息类型的后面，
        主要是考虑到机型中用的芯片大多都是采用小端存储。
    </dd>
    <dd>消息体长度 —— 消息携带具体消息结构体长度，如果为 0，则消息体不存在</dd>
    <dd>设备号 —— 从0开始，比如设备有2个button，则0为第一个，1为第二个</dd>
    <dd>错误码 —— 请求消息填0，响应消息填0代表操作成功，否则操作失败，详见错误码定义</dd>
</dl>
<pre>
    // 消息头 C 语言结构体
    typedef struct __NvcDriverMsgHdr
    {
        uint16 u16Magic;
        union{
            uint16 u16MsgType;
            struct{
                uint8 u8SubMsgType;
                uint8 u8MainMsgType;
            };
        };
        uint16 u16MsgLen;
        uint8  u8DevNo;
        uint8  u8ErrCode;
        uint8  u8Res[4];
    }Nvc_Driver_Msg_Hdr_S;

    /* 错误码定义
        消息在驱动层方面的实现，主要分为两部分，一个是对应用层来的消息做初步的规则分析，
        或者或者驱动在运行的时候出现异常上报的错误。（如 MagicWord 是否相同，消息类型是否相同，是否携带错误，以及应用层是否提供非法空间...)
        因为这两种情况无法判断错误的接受应该属于哪一个模块，所以会自动上报。还有一部分是在执行应用层的命令，
        因为事先已经确定了执行的命令，因此，此时的错误信息将会直接体现在响应消息中。
    */
    #define     NVC_DRIVER_SUCCESS              0   // 无错误
    #define     NVC_DRIVER_ERR_MGCWOD           1   // 消息透验证码错误(magic word)
    #define     NVC_DRIVER_ERR_MSGTYPE_M        2   // 消息主类型错误(Massage type main)
    #define     NVC_DRIVER_ERR_MSGTYPE_S        3   // 消息子类型错误(Massage type sub)
    #define     NVC_DRIVER_ERR_PFAPP            4   // 应用层读写时，传递到驱动的指针为非法指针，不可操作(point from application layer)
    #define     NVC_DRIVER_ERR_SLFAPP           5   // 读取数据时，应用层提供的空间不够(space length from application layer)
    #define     NVC_DRIVER_ERR_BFORMAT          6   // 应用层提供的消息类型，与目标消息格式不符(package format)
    #define     NVC_DRIVER_ERR_BUSY             7   // 驱动正忙(Driver busy)
    #define     NVC_DRIVER_ERR_INIT             8   // 设备初始化错误
    #define     NVC_DRIVER_ERR_PLBREAK          9   // 硬件设备检测不到（physical device lost connection）
    #define     NVC_DRIVER_ERR_UNFINISHED       10  // 操作未完成
    #define     NVC_DRIVER_ERR_MQUEUE           11  // 内存消息管理出错（不能写，读，或者直接内存溢出）
    #define     NVC_DRIVER_ERR_NO_SUPP          12  // 主消息和子消息都是对的，但可能由于机型的原因导致某些操作不支持

</pre>

<h4><a name="msgtype_id_h"></a>4. 各个消息类型的定义</h4>
<h5><a name="mmsgtype_id_h"></a>4.1 消息主类型</h5>
<p>
    消息的主类型主要定义一驱动中包含的功能模块的代号 ID，（比如：获取设备信息的代号为0）。目前的协议中仅支持 0-255 种可能的功能。
    之所以把消息类型中标识功能模块的 ID 放在功能模块对应详细操作 ID 的后面是基于 linux 中存储信息的方式为小端存储，
    换句话说，如果将八位的消息主类型和八位的消息子类型转换成16位的消息类型，表示产品功能的 ID 将在高八位，而表示产品功能的详细操作将在低八位中。
</p>
<p>
    下表定义了消息主类型的宏名，值，以及类型介绍
</p>
<table align="center" border="1" id="mMsgType_tb0_id">
    <caption>表4.1.1 主消息类型</caption>
    <tr>
        <th style="width: 250px;">宏名</th>
        <th style="width: 100px">值</th>
        <th style="width: 200px">类型功能介绍</th>
    </tr>
</table>
<pre>
    typedef enum _NvcMsgType{
        NVC_MsgType_DEVICE        = 0x00，
        NVC_MsgType_BUTTON        = 0x01，
        NVC_MsgType_LDR           = 0x02，
        NVC_MsgType_IRC           = 0x03，
        NVC_MsgType_IFRRED_LIGHT  = 0x04，
        NVC_MsgType_STATE_LIGHT   = 0x05，
        NVC_MsgType_PTZ           = 0x06，
        NVC_MsgType_NIGHT_LIGHT   = 0x07，
        NVC_MsgType_AUDIO_PLUG    = 0x08，
        NVC_MsgType_TEMP_MONITOR  = 0x09，
        NVC_MsgType_HUMI_MONITOR  = 0x0A，
        NVC_MsgType_DOUB_LENS     = 0x0B，
        NVC_MsgType_RESET_IO      = 0x0C，
        NVC_MsgType_RTC           = 0x0D，
        NVC_MsgType_PIR           = 0x0E，
        NVC_MsgType_DOOR_BELL     = 0x0F，
    }NvcMsgType_E
</pre>





<h5><a name="smsgtype_id_h"></a>4.2 消息子类型</h5>
<h6><a name="device_id_h"></a>4.2.1 SubMessageType:DEVICE</h6>
<div>
    <p>设备的第一个功能模块，这个模块是每个产品中必须加载的，它支持查询当前驱动的状态，编译版本，加载的产品类型，
        支持的功能，以及订阅自动上报消息。</p>
    <p>要特别注意的是 DEVICE_REPORT_DRIVER_ERR 这条消息，这条消息会上报因消息格式，硬件连接方面出现的突发错误，
        应用层监视这条消息，能很方便快速的定位错误发生的位置</p>
    <p>详细的协议见下表：</p>
    <table align="center" border="1" id="sMsgDvc_tb0_id">
        <caption>表4.2.1.1 SubMsg：DEVICE</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // DDEVICE MODE 中包含的 消息子类型枚举
        typedef enum _NVC_DeviceCmd{
            DEVICE_GET_INFO_REQ             = 1,
            DEVICE_GET_INFO_RESP            = 2,
            DEVICE_GET_CAPACITY_REQ         = 3,
            DEVICE_GET_CAPACITY_RESP        = 4,
            DEVICE_SUB_REPORT_MSG_REQ       = 5,
            DEVICE_SUB_REPORT_MSG_RESP      = 6,
            DEVICE_REPORT_DRIVER_ERR        = 7,
        }NVC_DeviceCmd_E;

        /* DEVICE_GET_INFO_RESP 应答消息结构体
            u32ChipType     芯片类型，已经在第二节介绍了
            u32DeviceType   产品类型，也在第二节介绍了
            szVerInfo       驱动版本类型，由字符串组成 第一个数字标示大版本号，第二个数字，标示累计版本号。
                            测试版会在后面就加上 Beta 字样,如果是稳定版，则不会加后缀
                            如：V:03:001:Beta V:03.002.
            szBuildData    编译信息，格式为 作者+编译时间
                            如：maj:15-8-1 13:00
        */
        typedef struct __Nvc_Driver_Ver_Info
        {
            uint32  u32ChipType; // NvcChipType_E
            uint32  u32DeviceType; // NvcDeviceType_E
            char    szVerInfo[16];
            char    szBuildData[32];
        }Nvc_Driver_Ver_Info_S;

        /* DEVICE_GET_CAPACITY_RESP 应答消息结构体
            u32CapMask      能力集：【注1： 附件2 —— 表1】
            u8ButtonCnt     按键数量
            u8LedCnt        状态灯个数
            u8Res           保留
        */
        typedef struct __Nvc_Driver_Cap_Info
        {
            uint32 u32CapMask;
            uint8  u8ButtonCnt;
            uint8  u8LedCnt;
            uint8  u8Res[2];
        }Nvc_Driver_Cap_Info_S;

        /* DEVICE_SUB_REPORT_MSG_REQ 请求消息结构体
            u8Attached      0：注销 上报事件/状态信息
                            1：注册上报事件/状态信息
            u8Res           保留
        */
        typedef struct __Nvc_Attached_Driver_Msg
        {
            uint8   u8Attached;
            uint8   u8Res[3];
        }Nvc_Attached_Driver_Msg_S;
    </pre>
</div>





<h6><a name="but_h_id"></a>4.2.2 SubMessageType:BUTTON</h6>
<div>
    <div class="Block_2">
        <p>不同的产品中可能拥有不同的 button 数量，如果应用层向要获取当前加载的驱动中的 button 数量可以发送，DEVICE_GET_CAPACITY_REQ
        在返回的消息体中会包含案件的总数。另外在获取 button 的状态时，要在消息头的 Unit 字段标示需要获取的是哪个 button （数值 0 表示
            第一个 button）；同样想要知道 button 上报消息是属于哪个 button 的时候，查看 Unit 字段即可。</p>
    </div>
    <table align="center" border="1" id="sMsgBut_tb0_id">
        <caption>表4.2.2.1 SubMsg：BUTTON</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // BUTTON MODE 中包含的 消息子类型枚举
        typedef enum _Nvc_ButStateCmd{
            BUTTON_GET_STATUS_REQ       = 1，
            BUTTON_GET_STATUS_RESP      = 2，
            BUTTON_REPORT_STATUS_MSG    = 3，
        }Nvc_ButStateCmd_E;

        /* BUTTON_GET_STATUS_RESP 以及 BUTTON_REPORT_STATUS_MSG 消息结构体
            u8Status        0: 上报-弹起事件发生  查询-按键处于正常状态
                            1: 上报-按下事件发生  查询-按键已经按下
        */
        typedef struct __Nvc_Button_Status_S
        {
            uint8   u8Status; // 0 未被按下， 1 被按下
            uint8   u8Res[3];
        }Nvc_Button_Status_S;
    </pre>
</div>





<h6><a name="ldr_h_id"></a>4.2.3 SubMessageType:LDR</h6>
<div>
    <div class="Block_2">
        <p>白天黑夜检测模块（LDR or Day&Night Monitor ），负责检测环境中的照度，有状态变化时，驱动层会主动通知应用层。
        应用层可以修改驱动中该模块的检测灵敏度，其中调节灵敏度主要从从两个方面设置，一个是临界点，还有一个消抖区间大小，
        详细配置见配置灵敏度结构体。</p>
    </div>

    <table align="center" border="1" id="sMsgLDR_tb0_id">
        <caption>表4.2.3.1 SubMsg：LDR</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // LDR MODE 中包含的 消息子类型枚举
        typedef enum _Nvc_LdrCmd{
            LDR_GET_STATUS_REQ          = 1,
            LDR_GET_STATUS_RESP         = 2,
            LDR_REPORT_STATUS_MSG       = 3,
            LDR_SET_SENSITIVITY_REQ     = 5,
            LDR_SET_SENSITIVITY_RESP    = 6,
            LDR_GET_SENSITIVITY_REQ     = 7,
            LDR_GET_SENSITIVITY_RESP    = 8,
        }Nvc_LdrCmd_E;

        /* LDR_GET_STATUS_RESP 和 LDR_REPORT_STATUS_MSG 的消息结构体
            u8Status    0: 当前环境状态为黑夜
                        1: 当前环境状态为白天
        */
        typedef struct __Nvc_Ldr_Status
        {
            uint8   u8Status;
            uint8   u8Res[3];
        }Nvc_Ldr_Status_S;

        /* LDR_SET_SENSITIVITY_REQ & LDR_GET_SENSITIVITY_RESP 的消息结构体
            u8SPoint    调节检测白天黑夜的临界点，调节范围（0-100）
                        设置0会没有有黑夜状态，也不会上报状态
                        设置100会没有白天状态，也不会上报状态

            u8Domain    调节白天黑夜消除抖动的缓冲大小，调节范围（0-100）
                        设置0，严格的根据灵界值做判断
                        设置成其他值则驱动会适当的改变临界值的判断边界

            以上两个参数超过设置范围会触发驱动上报异常（消息类型 DEVICE_REPORT_DRIVER_ERR ）
        */
        typedef struct __Nvc_Ldr_Senitivity
        {
            uint8   u8SPoint;
            uint8   u8Domain;
            uint8   u8Res[2];
        }Nvc_Ldr_Senitivity_S;

    </pre>
</div>




<h6><a name="irc_id_h"></a>4.2.4 SubMessageType:IRC</h6>
<div>
    <table align="center" border="1" id="sMsgIrc_tb0_id">
        <caption>表4.2.4.1 SubMsg：IRC</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // IRC MODE 中包含的 消息子类型枚举
        typedef enum _NvcIRCCmd{
            IRC_GET_TYPE_REQ    = 1,
            IRC_GET_TYPE_RESP   = 2,
            IRC_SET_SWITCH_REQ  = 3,
            IRC_SET_SWITCH_RESP = 4,
            IRC_GET_STATUS_REQ  = 5,
            IRC_GET_STATUS_RESP = 6,
        }NvcIRCCmd_E;

        /* IRC_GET_TYPE_RESP 的消息结构体
            上报 IRC 的类型，目前有两种，
            1. TYPEA 8002 芯片搭建的驱动电路
            2. TYPEB 分立元件搭建的电路

            作用： 暂时只是标示不同机型的不同 IRC 模块，并没有其他昨天用
        */
        typedef struct __Nvc_Ircut_Info
        {
            uint32 u32IrcType;
        }Nvc_Ircut_Info_S;

        /* IRC_SET_SWITCH_REQ & IRC_GET_STATUS_RESP 的消息结构体
            u8Status
                0   代表控制 IRC 模块切换至阻挡红外光的状态
                1   代表控制 IRC 模块切换至允许红外光通过的状态

        */
        typedef struct __Nvc_Ircut_Status
        {
            uint8   u8Status;
            uint8   u8Res[3];
        }Nvc_Ircut_Status_S;
    </pre>
</div>



<h6><a name="nili_id_h"></a>4.2.5 SubMessageType:Infrared Light</h6>
<div>
    <table align="center" border="1" id="sMsgIfrLi_tb0_id">
        <caption>表4.2.5.1 SubMsg：Infrared Light</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // IfrLi MODE 中包含的 消息子类型枚举
        typedef enum _NvcIfrLight{
            IfrLIGHT_SET_SWITCH_REQ     = 1,
            IfrLIGHT_SET_SWITCH_RESP    = 2,
            IfrLIGHT_GET_STATUS_REQ     = 3,
            IfrLIGHT_GET_STATUS_RESP    = 4,
        }NvcIfrLight_E;

        /*  IfrLIGHT_SET_SWITCH_REQ & IfrLIGHT_GET_STATUS_RESP 的消息结构体
            u8Status
                0   关闭红外灯
                1   打开红外灯
        */
        typedef struct __Nvc_Lamp_Status
        {
            uint8   u8Status;
            uint8   u8Res[3];
        }Nvc_Lamp_Status_S;

    </pre>
</div>


<h6><a name="stali_id_h"></a>4.2.6 SubMessageType:State Light</h6>
<div>
    <table align="center" border="1" id="sMsgStaLi_tb0_id">
        <caption>表4.2.6.1 SubMsg：State Light</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // StaLi MODE 中包含的 消息子类型枚举
        typedef enum _NvcStaLight{
            StaLIGHT_SET_STATUS_REQ     = 1,
            StaLIGHT_SET_STATUS_RESP    = 2,
        }NvcStaLight_E;

        /* StaLIGHT_SET_STATUS_REQ 的消息结构体
            注意：这里只有控制灯的状态
            eColor:
                如果一个产品（设备）里面拥有多个个灯的话，指示操作哪一个需要在消息头的 Unit 字段里面指明，默认从 0 开始，
                0 代表第一个灯。如果某些灯一个中含有多种颜色，那么，我们要在此字段明确（如红灯：即 NV_LED_COLOR_RED）
            u32OnMesl:
            u32OffMesl:
                这两个字段分别标示，灯亮的时间，灯灭的时间，单位为 1ms，如此便可控制灯的亮度，也可控制灯的闪烁，如果要常
            量，将 u32OffMesl 设置成 0，u32OnMesl 设置非零，即可，不过可能因为硬件特性的原因，建议非零的那个值越大越好。
            u32BrthFrq：
                当 eMode 为 NV_LED_Mode_BREATH 模式的时候，这个参数会用来调节呼吸灯的频率快慢，范围从 0-10 ，当选择 0 的
            的时候会选择默认值，选择 1-10 频率会逐步上升，超过这些限定值的时候操作会视为无效操作。
        */
        typedef enum __Nvc_State_Led_Mode
        {
            NV_LED_Mode_DEFAULT    = 0,
            NV_LED_Mode_RED        = 1,
            NV_LED_Mode_GREEN      = 2,
            NV_LED_Mode_BLUE       = 3,
            NV_LED_Mode_BREATH     = 4,
        }Nvc_State_Led_Mode_E;
        //
        typedef struct __Nvc_State_Led_Control
        {
            Nvc_State_Led_Mode_E eMode;
            union{
                uint32  u32OnMesl; // 亮灯时间，单位ms
                uint32  u32BrthFrq; // 呼吸灯的频率
            }
            uint32  u32OffMesl;// 灭灯时间，单位ms
        }Nvc_State_Led_Control_S;

    </pre>
</div>



<h6><a name="ptz_id_h"></a>4.2.7 SubMessageType:PTZ</h6>
<div>

    <p>2015-9-23 日记录：1.就目前拥有的机型，支持云台操作的机型都支持垂直和水平两个方向的自由度， D11 球泡机硬件上没有限位检测，
    因此，不带有零位获取，自动扫描的功能。F05 机型以及历史的 D03 BabyMonitor 中限位开关在硬件上是固定的，因此，也不支持限位的设置。
    2.还有一个要注意的点是，如果云台在硬件上具备水平限位开关和垂直限位开关，那么他是一定支持叠加运动的，也可获取得到当前云台所在的坐标，
    在驱动上也要做到这一点，而且也会支持预置位设置的功能。获取云台当前位置可以发送 <code>PTZ_GET_INFO_REQ</code> ，
    在返回得消息结构体会存在云台的坐标。
    3.目前的所有机型中，还没有具备自动变焦的机器。而且，变焦的功能也应该加在云台消息类型中，因此这次在
    <code>Nvc_Ptz_Cap_E</code> 枚举类型中删除
    <code>NVC_PTZ_SUPP_ZOOM   = 0x00000200, // 是否支持变倍</code> 和 <code>   NVC_PTZ_SUPP_FOCUS  = 0x00000400, // 是否支持手动聚焦</code>
    两条定义。和在 <code>Nvc_Ptz_Cmd_E</code> 中删除了<code>
    a.【NV_PTZ_ZOOM_IN          = 9,  // 变倍+】
    b.【 NV_PTZ_ZOOM_OUT         = 10, // 变倍-】
    c.【 NV_PTZ_FOCUS_NEAR       = 11, // 聚焦近】
    d.【  NV_PTZ_FOCUS_FAR        = 12, // 聚焦远】
    </code>四条定义。
    4.启用云台预置位，巡航等功能时，先要保证机型硬件中有限位功能。首先掉电后，驱动中的配置将不再存在，因此，机器在启动之时，
    应该把用户保存的预置位配置信息通过 <code>PTZ_ENPORT_PRESET_POINT_REQ</code> 消息类型对驱动的预置位进行配置，如果应用层未存在配置信息，
    那么不需要发送。在控制云台进行预置位巡航时要先用 <code>PTZ_SET_CRUISE_PATH_REQ</code> 配置云台的运动路径，
    然后在发送 <code>PTZ_SET_COMMON_REQ</code> 中的 <code>NV_PTZ_STAR_CRUISE</code>  来启动巡航，
    如果只要移动到一个预置位的位置，用 <code>PTZ_SET_COMMON_REQ</code> 中的 <code>NV_PTZ_MVT_PRESET</code> 命令即可。
    云台在巡航过程中可以被中断。预置位的编号从1开始。
    </p>

    <p>2015-9-28 日记录：1.设置云台预置位的时候，所有预置位的编号都是从 1 开始，到最大允许设置的预置位结束。如果设置 0 ，
    那么此次设置当做无效设置。比如：Nvc_Ptz_Control_S 字段中的 u8No， 以及 Nvc_Ptz_PrePoint_S 字段中的 u8PrePointNum，
    这些字段中的数值应当设置在 1 - NV_CRUISE_PRESET_NUM 范围类，而且，当数值设置在此个范围的时候，也要保证这些结构体的其
    他字段应该是在协议预先商定的数值范围，不然会发生意想不到的错误。
    </p>

    <table align="center" border="1" id="sMsgPTZ_tb0_id">
        <caption>表4.2.7.1 SubMsg：PTZ</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>


    <pre>
        // PTZ MODE 中包含的 消息子类型枚举
        typedef enum _NvcPTZCmd{
            PTZ_GET_INFO_REQ             = 1,
            PTZ_GET_INFO_RESP            = 2,
            PTZ_SET_COMMON_REQ           = 3,
            PTZ_SET_COMMON_RESP          = 4,
            PTZ_SET_PRESET_POINT_REQ     = 5,
            PTZ_SET_PRESET_POINT_RESP    = 6,
            PTZ_CLR_PRESET_POINT_REQ     = 7,
            PTZ_CLR_PRESET_POINT_RESP    = 8,
            PTZ_REPORT_INFO_MSG          = 9,
            PTZ_ENPORT_PRESET_POINT_REQ  = 11,
            PTZ_ENPORT_PRESET_POINT_RESP = 12,
            PTZ_SET_CRUISE_PATH_REQ      = 13,
            PTZ_SET_CRUISE_PATH_RESP     = 14,
        }NvcPTZCmd_E;

        // 云台坐标
        typedef struct __Nvc_PTZ_Coordinate{
            uint16 XPos;
            uint16 YPos;
        }Nvc_PTZ_Coordinate_S;

        // 云台支持的功能 MASK
        typedef enum __NvcPtzCap
        {
            NVC_PTZ_SUPP_HMOVE  = 0x00000001, // 是否支持水平运动
            NVC_PTZ_SUPP_VMOVE  = 0x00000002, // 是否支持垂直运动
            NVC_PTZ_SUPP_HVMOVE = 0x00000004, // 是否支持水平垂直叠加运动( 是否支持左上,左下, 右上, 右下命令 )
            NVC_PTZ_SUPP_HSCAN  = 0x00000008, // 是否支持水平自动扫描
            NVC_PTZ_SUPP_VSCAN  = 0x00000010, // 是否支持垂直自动扫描
            NVC_PTZ_SUPP_HLIMIT = 0x00000020, // 是否支持水平限位设置
            NVC_PTZ_SUPP_VLIMIT = 0x00000040, // 是否支持垂直限位设置
            NVC_PTZ_SUPP_ZERO   = 0x00000080, // 是否支持零位检测/设置
            NVC_PTZ_SUPP_CURPOS = 0x00000100, // 是否支持获取当前云台位置
            NVC_PTZ_SUPP_PRESET = 0x00000800, // 是否支持预置位
            NVC_PTZ_SUPP_CRUISE = 0x00001000, // 是否支持预置位巡航
        }Nvc_Ptz_Cap_E;

        // 标示当前云台的状态
       typedef enum __NvcPtzStatus{
            NVC_PTZ_STATUS_INITIDONE = 0x00000001
            NVC_PTZ_STATUS_Busy      = 0x00000002
            NVC_PTZ_STATUS_CmdFull   = 0x00000004
        }Nvc_Ptz_Status_E;

        /* PTZ_GET_INFO_RESP & PTZ_REPORT_INFO_MSG 的消息结构体
            云台在启动的时候，某些机型会进行初始状态检测，所以可能会导致一个情况，云台还没有初始化完，
        应用层就来获取云台的信息，这样就会导致信息不全面。
            所以在云台自检完成后，驱动会主动上报一条云台的消息，结构类型如下。
            u32PtzCapMask:
                由一个个标志位构成，标示云台所具有的功能，包括是否支持垂直水平，有无限位之类的
            u32Status：
                标示当前云台状态，是否初始化完，是否处于忙碌状态....
            ....
        */
        typedef struct __Nvc_Ptz_Info
        {
            uint32  u32PtzCapMask;          // Nvc_Ptz_Cap_E, 指示设备支持哪些云台命令
            /***************************** 以下定义，最高位均为是否初始化标识 ****************************************/
            /****** 如uint32类型bit [0~31]，其中 bit31: 0x1 表示未初始化完成，0x0 表示已初始化完成,获取到实际参数值 ******/
            //
            uint32  u32Status;
            uint32  u32HorizontalTotSteps;  // 水平方向总步数, (u32Cap & NVC_PTZ_SUPP_HMOVE) 有效
                                            // 如果可一直向左/右运动或无法获取总步数, 则为0x7FFFFFFF,
            uint32  u32HPerStepDegrees;     // 水平方向转动1步对应云台转动度数, 单位0.000001度, (u32Cap & NVC_PTZ_SUPP_HMOVE) 有效
            uint32  u32HorizontalMinSteps;  // 水平方向转动最小步数, (u32Cap & NVC_PTZ_SUPP_HMOVE) 有效
            //
            uint32  u32VerticalTotSteps;    // 垂直方向总步数, (u32Cap & NVC_PTZ_SUPP_VMOVE) 有效
                                            // 如果可一直向上/下运动或无法获取总步数， 则为0x7FFFFFFF,
            uint32  u32VPerStepDegrees;     // 垂直方向转动1步对应云台转动度数, 单位0.000001度, (u32Cap & NVC_PTZ_SUPP_VMOVE) 有效
            uint32  u32VerticalMinSteps;    // 垂直方向转动最小步数，(u32Cap & NVC_PTZ_SUPP_VMOVE) 有效
            //
            uint32  u32ZeroHStepPos;        // 云台零位相对云台最下端步数 (u32Cap & NVC_PTZ_SUPP_ZERO & NVC_PTZ_SUPP_HMOVE) 时有效
            uint32  u32ZeroVStepPos;        // 云台零位相对云台最左端步数(u32Cap & NVC_PTZ_SUPP_ZERO & NVC_PTZ_SUPP_VMOVE) 时有效
            uint32  u32CurHStepPos;         // 云台当前位置, 相对云台最下端步数 (u32Cap & NVC_PTZ_SUPP_CURPOS & NVC_PTZ_SUPP_HMOVE) 时有效
            uint32  u32CurVStepPos;         // 云台当前位置, 相对云台最左端步数 (u32Cap & NVC_PTZ_SUPP_CURPOS & NVC_PTZ_SUPP_VMOVE) 时有效
        }Nvc_Ptz_Info_S;

        // 云台的控制命令
        typedef enum __Nvc_Ptz_Cmd
        {
            NV_PTZ_STOP             = 0, // 云台停止
            NV_PTZ_UP               = 1, // 上
            NV_PTZ_DOWN             = 2, // 下
            NV_PTZ_LEFT             = 3, // 左
            NV_PTZ_RIGHT            = 4, // 右
            NV_PTZ_LEFT_UP          = 5, // 左上
            NV_PTZ_LEFT_DOWN        = 6, // 左下
            NV_PTZ_RIGHT_UP         = 7, // 右上
            NV_PTZ_RIGHT_DOWN       = 8, // 右下
            NV_PTZ_MVT_PRESET       = 9, // 移动至预置位，至于哪一个预置位这要根据 Nvc_Ptz_Control_S 的第二字段给出
            NV_PTZ_STAR_CRUISE      = 10, // 启动巡航
        //    NV_PTZ_AUTO_SCAN        = 11, // 自动扫描
        //    NV_PTZ_UP_LIMIT         = 12, // 上限位设置
        //    NV_PTZ_DOWN_LIMIT       = 13, // 下限位设置
        //    NV_PTZ_LEFT_LIMIT       = 14, // 左限位设置
        //    NV_PTZ_RIGHT_LIMIT      = 15, // 右限位设置
        //    NV_PTZ_GOTO_ZERO        = 16, // 零位检测，云台初始位置
        }Nvc_Ptz_Cmd_E;

        /* PTZ_SET_COMMON_REQ 的消息结构体
            云台分为三种操作模式，步数，角度，坐标控制，在没有限位的机型中，坐标控制是不准确的。
            u8PtzCmd
                控制命令，拥有的控制方式见 Nvc_Ptz_Cmd_E
            u8ParaType
                云台控制模式，步数，角度，坐标
            u8No
                如果控制命令是针对预置位巡航的，则这个字段标示预置位的下标
            u8Speed
                云台的运转速度，值 0-100，0 的时候为默认速度，大约为 36， 超出范围将会产生警告
            u32HSteps
            u32VSteps
                水平和垂直方向控制分量
        */
        typedef struct __Nvc_Ptz_Control_S
        {
            uint8   u8PtzCmd;   // Nvc_Ptz_Cmd_E 云台命令
            union {
                uint8   u8ParaType; // 0: 步数, 1 角度,目前仅支持步数控制
                uint8   u8No;       // 预置位号或巡航号
            };
            uint8   u8Speed;    // 云台速度 (1 ~ 100, 默认50)
            uint8   u8Res;      // 预留
            uint32  u32HSteps;  // 水平步数
            uint32  u32VSteps;  // 垂直步数
        }Nvc_Ptz_Control_S;

        /*
            设置预置位的一个节点
            要特别注意的是，速度指从别的点移动到这一点的速度，停留时间为在进行自动预置位巡航的时候，停留在该点的时间，
        如果只是将云台移至该点，那么云台在下次操作前都会一直停留在该点。
        */
        总共支持 16 个预置位
        #define NV_CRUISE_PRESET_NUM 16
        typedef struct __Nvc_Cruise_PRESET{
            uint8 u8PrePointNum;
            uint8 u8Speed;
            uint16 u16StaySeconds; // 单位: 秒
        }Nvc_Ptz_PrePoint_S;

        /*
            设置运动路径，如果运动路径中涉及到的预置位未预先配置好，那么此次设置将会报错，在响应消息的头中返回错误信息。
        */
        typedef struct __Nvc_PrePoint_CONF{
            uint8 u8MovePath[NV_CRUISE_PRESET_NUM];
        }Nvc_PrePoint_CONF_s;

        /*
            导入预置位的配置信息
        */
        typedef struct __Nvc_Preset_CONF{
            Nvc_Ptz_PrePoint_S      aBasicMsg[NV_CRUISE_PRESET_NUM];
            Nvc_PTZ_COORDINATE_S    aCoordinate[NV_CRUISE_PRESET_NUM];
        }Nvc_Preset_CONF_s;

    </pre>
</div>



<h6><a name="nili_id_h"></a>4.2.8 SubMessageType:NightLight</h6>
<div>
    <table align="center" border="1" id="sMsgNiLi_tb0_id">
        <caption>表4.2.8.1 SubMsg：NightLight</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // NiLi MODE 中包含的 消息子类型枚举
        typedef enum _NvcNiLightCmd{
            NiLIGHT_SET_STATUS_REQ      = 1,
            NiLIGHT_SET_STATUS_RESP     = 2,
            NiLIGHT_GET_STATUS_REQ      = 3,
            NiLIGHT_GET_STATUS_RESP     = 4,
        }NvcNiLightCmd;

        /* NiLIGHT_SET_STATUS_REQ & NiLIGHT_GET_STATUS_RESP 的消息结构体
            u8Status
                0   关闭小夜灯
                1   开启小夜灯
            u8LumLevel
                1-100   超限将会导致警告的的产生
        */
        typedef struct __Nvc_Night_Light_Status
        {
            uint8   u8Status; // 0 关闭小夜灯，1 打开小夜灯
            uint8   u8LumLevel; // 亮度，等级 1 - 100
            uint8  u8Res[2];
        }Nvc_Night_Light_Status_S;
    </pre>
</div>



<h6><a name="aupl_id_h"></a>4.2.9 SubMessageType:Audio Plug</h6>
<div>
    <table align="center" border="1" id="sMsgAuPl_tb0_id">
        <caption>表4.2.9.1 SubMsg：Audio Plug</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // AuPl MODE 中包含的 消息子类型枚举
        typedef enum _NvcAudioPlugCmd{
            Speaker_SET_STATUS_REQ   = 1
            Speaker_SET_STATUS_RESP  = 2
            Speaker_GET_STATUS_REQ   = 3
            Speaker_GET_STATUS_RESP  = 4
            Microph_Set_STATUS_REQ   = 5
            Microph_Set_STATUS_RESP  = 6
            Microph_GET_STATUS_REQ   = 7
            Microph_GET_STATUS_RESP  = 8
        }NvcAudioPlugCmd;

        /* Speaker_SET_STATUS_REQ & Speaker_GET_STATUS_RESP & Microph_Set_STATUS_REQ & Microph_GET_STATUS_RESP 的消息结构体
            u8Status
                0   关闭
                1   打开
        */
        typedef struct __Nvc_Audio_Plug_Status
        {
            uint8   u8Status; // 0 关闭，1 打开, 2 不支持此状态
            uint8   u8Res[3];
        }Nvc_Audio_Plug_Status_S;


    </pre>
</div>



<h6><a name="tm_id_h"></a>4.2.A SubMessageType:Temp Monitor</h6>
<div>
    <table align="center" border="1" id="sMsgTemp_tb0_id">
        <caption>表4.2.A.1 SubMsg：Temp Monitor</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // Temp MODE 中包含的 消息子类型枚举
        typedef enum _NvcTempMonitorCmd{
            TmpMONITOR_SET_REPORT_REQ       = 1,
            TmpMONITOR_SET_REPORT_RESP      = 2,
            TmpMONITOR_GET_VALUE_REQ        = 3,
            TmpMONITOR_GET_VALUE_RESP       = 4,
            TmpMONITOR_REPORT_VALUE_MSG     = 5,
        }NvcTempMonitorCmd;

        /* TmpMONITOR_SET_REPORT_REQ 的消息结构体
            设置多少时间上报一次温度数据，单位为 1s， 如果设置为 0，如果之前上报则会取消上报机制，
        如果没有，则不会产生任何影响。
        */
        typedef struct __Nvc_Temperature_Timer
        {
            uint32  u8DistTime;
        }Nvc_Temperature_Timer_S;

        /*  TmpMONITOR_GET_VALUE_RESP & TmpMONITOR_REPORT_VALUE_MSG 的消息结构体
            获取或上报温度值
            精度为 0.01 度，
        */
        typedef struct __Nvc_Temperature_Value
        {
            int32 s32Temperature;
        }Nvc_Temperature_Value_S;

    </pre>
</div>



<h6><a name="hm_id_h"></a>4.2.B SubMessageType:Humidity Monitor</h6>
<div>
    <table align="center" border="1" id="sMsgHumi_tb0_id">
        <caption>表4.2.B.1 SubMsg：Humidity Monitor</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // Humi MODE 中包含的 消息子类型枚举
        typedef enum _NvcHimiMonitorCmd{
           HumMONITOR_SET_REPORT_REQ        = 1,
            HumMONITOR_SET_REPORT_RESP      = 2,
            HumMONITOR_GET_VALUE_REQ        = 3,
            HumMONITOR_GET_VALUE_RESP       = 4,
            HumMONITOR_REPORT_VALUE_MSG     = 5,
        }NvcHimiMonitorCmd;

        /* HumMONITOR_SET_REPORT_REQ 的消息结构体
            作用同设置温度上报设置
        */
        typedef struct __Nvc_Humidity_Timer
        {
            uint32  u8DistTime; // 间隔多少秒采集1次湿度，0为不采集 (默认)
        }Nvc_Humidity_Timer_S;

        /* HumMONITOR_GET_VALUE_RESP & HumMONITOR_REPORT_VALUE_MSG 的消息结构体
            获取或者上报湿度数据
            精度为 0.01%
        */
        typedef struct __Nvc_Humidity_Value_S
        {
            uint32 u32Humidity; // 百分比,单位 0.01%，比如当前湿度为60%， 则返回6000
        }Nvc_Humidity_Value_S;

    </pre>
</div>



<h6><a name="dublen_id_h"></a>4.2.C SubMessageType:DoubleLens</h6>
<div>
    <table align="center" border="1" id="sMsgDubL_tb0_id">
        <caption>表4.2.C.1 SubMsg：DoubleLens</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // DubLen MODE 中包含的 消息子类型枚举
        typedef enum _NvcDoubLensCmd{
            DobLENS_SET_STATUS_REQ      = 1,
            DobLENS_SET_STATUS_RESP     = 2,
            DobLENS_GET_STATUS_REQ      = 3,
            DobLENS_GET_STATUS_RESP     = 4,

        }NvcDoubLensCmd_E;

        /* DobLENS_SET_STATUS_REQ & DobLENS_GET_STATUS_RESP 的消息结构类型
            u8CurLens
                0   切换至夜用镜头
                1   切换至日用镜头
        */
        typedef struct __Nvc_Lens_Status
        {
            uint8   u8CurLens; // 0 使用日用镜头，1 使用夜用镜头
            uint8   u8Res[3];
        }Nvc_Lens_Status_S;


    </pre>
</div>


<h6><a name="resetio_id_h"></a>4.2.D SubMessageType:Reset IO</h6>
<div>
    <table align="center" border="1" id="sMsgRio_tb0_id">
        <caption>表4.2.D.1 SubMsg：Reset IO</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // ResetIO MODE 中包含的 消息子类型枚举
        typedef enum _NvcResetIOCmd{
            ResetIO_SET_STATUS_REQ      = 1,
            ResetIO_SET_STATUS_RESP     = 2,
        }NvcResetIOCmd_E;
    </pre>
</div>


<h6><a name="rtc_id_h"></a>4.2.E SubMessageType:RTC</h6>
<div>
    <table align="center" border="1" id="sMsgRTC_tb0_id">
        <caption>表4.2.E.1 SubMsg：RTC</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // RTC MODE 中包含的 消息子类型枚举
        typedef enum _NvcRTCCmd{
            RTC_SET_TIME_REQ     = 1
            RTC_SET_TIME_RESP    = 2,
            RTC_GET_TIME_REQ    = 3,
            RTC_GET_TIME_RESP   = 4,
        }NvcRTCCmd_E;

        /* RTC_SET_TIME_REQ & RTC_GET_TIME_RESP 消息结构类型
            data-time 类型
        */
        typedef struct __Nvc_Time_Struct_S{
        uint8   u8Second;
        uint8   u8minutes;
        uint8   u8hour;
        uint8   u8day;
        uint8   u8weekday;
        uint8   u8month
        uint8   u8year;
    }Nvc_Time_Struct_S;
    </pre>
</div>



<h6><a name="pir_id_h"></a>4.2.F SubMessageType:PIR</h6>
<div>
    <p>
        这里有几个注意的地方，首先如果应用层利用 NVC_MsgType_DEVICE 的 DEVICE_SUB_REPORT_MSG_REQ 订阅了上报的消息， PIR
        一经检测到有物体的活动，首先就会上报给应用层，但应用层去主动获取的当时可能并没有检测到有 Activity，也不清楚过去
        的那段时间检测到了几次 Activity，因此此时返回上来的时是驱动挂载起到当下所检测到的次数总和。
    </p>
    <p>
        PIR 的检测基于红外热电堆的电荷堆积，因此检测的时候是不可预测的，可能单个现实生活中的 Activity 会产生 N 次 PIR
        触发，因此在第一次检测到之后建议事先设置一段死区（只需设置一次），这样可以滤掉不必要的干扰。设置死区时间用
        NVC_MsgType_PIR 的 PIR_SET_DEAD_TIME_REQ 。默认 3s。
    </p>
    <table align="center" border="1" id="sMsgPIR_tb0_id">
        <caption>表4.2.F.1 SubMsg：PIR</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // PIR MODE 中包含的 消息子类型枚举
        typedef enum _NvcPIRCmd{
            PIR_GET_STATUS_REQ      = 1
            PIR_GET_STATUS_RESP     = 2,
            PIR_REPORT_STATUS       = 3,
            PIR_SET_DEAD_TIME_REQ   = 5,
            PIR_SET_DEAD_TIME_RESP  = 6,
        }NvcPIRCmd_E;

        /* PIR_GET_STATUS_RESP & PIR_REPORT_STATUS 消息结构类型
            u8Satus
                0   没有检测到异常
                1   检测到异常
            u8Remain[3]
                保留
            u32SumActivity
                当应用层主动去获取是否有事件发生时，他会得到此数据，这个字段记录的是侦测到异常的总次数。
            u32Distence
                被检测物体距当前检测物体的距离
                单位：厘米
        */
        typedef struct __Nvc_PIR_Struct_S{
            union{
                struct{
                    uint8 u8Status;
                    uint8 u8Remain[3];
                };
                uint32 u32SumActivity;
            };
            uint32 u32Distence;
        }Nvc_PIRStatus_Struct_S；
        /* NVC_PIR_SET_DEAD_TIME_REQ 的消息结构类型
            aTime
                设置死区时间，单位为 0.1s
        */
        typedef struct __Nvc_PIRConf_Struct_S{
            uint32 aTime;
        }Nvc_PIRConf_Struct_S;


    </pre>
</div>



<h6><a name="doorbell_id_h"></a>4.2.10 SubMessageType:DoorBell</h6>
<div>
    <table align="center" border="1" id="sMsgDoorBell_tb0_id">
        <caption>表4.2.10.1 SubMsg：Door Bell</caption>
        <tr>
            <th>命令类型</th>
            <th>值</th>
            <th>说明</th>
            <th>消息类型</th>
        </tr>
    </table>
    <pre>

        // Door Bell MODE 中包含的 消息子类型枚举
        typedef enum _NvcDoorBellCmd{
            DOORBELL_SET_STATUS_REQ      = 1
            DOORBELL_SET_STATUS_RESP     = 2,
        }NvcDoorBellCmd_E;

        /* DOORBELL_SET_STATUS_REQ 消息结构类型
            u8Status
                1       触发门铃响应
                others  没反应
            u8Remain
                保留
        */
        typedef struct __Nvc_DoorBellConf_Status_S{
            uint8   u8Status;
            uint8   u8Remain[3];
        }Nvc_DoorBellConf_Status_S;

    </pre>
</div>


<h4><a name="appendix1_id_h"></a>5. 附件1——兼容老协议 MsgType</h4>
<p>
    最初的协议为{魔术字(2Bety)+消息类型(2Bety)+消息长度(2Bety)+Unit(1Bety)+Error(1Bety)+REMAIN(4)(4Bety)},
    与现在的协议区别主要在消息类型部分，以前的协议消息类型间的区分不是很清楚
</p>
<table border="1" id="MsgOld_tb0_id">
    <caption>表5.1 Old Protocol</caption>
    <tr>
        <th>消息类型</th>
        <th>枚举定义</th>
        <th>消息定义描述</th>
        <th>消息结构体</th>
    </tr>
</table>
<pre>

</pre>

<h4><a name="appendix2_id_h"></a>6. 附件2——内文注解</h4>
<table border="1" id="CapMuster_tb0_id">
    <caption>表7.1 能力集</caption>
    <tr>
        <th style="width: 300px;">能力集</th>
        <th style="width: 130px;">掩码</th>
        <th>备注</th>
    </tr>
</table>
<pre>
    typedef enum __NvcDriverCap
    {
        NVC_SUPP_ButtonMonitor   = 0x00000001,    // 是否支持按键检测
        NVC_SUPP_LdrMonitor      = 0x00000002,    // 是否支持日夜模式检测(light dependent resistors detection)
        NVC_SUPP_Ircut           = 0x00000004,    // 是否支持滤光片切换
        NVC_SUPP_IfrLamp         = 0x00000008,    // 是否支持红外灯
        NVC_SUPP_DoubleLens      = 0x00000010,    // 是否支持双镜头
        NVC_SUPP_StateLed        = 0x00000020,    // 是否支持状态灯显示
        NVC_SUPP_PTZ             = 0x00000040,    // 是否支持云台功能
        NVC_SUPP_NightLight      = 0x00000080,    // 是否支持小夜灯功能
        NVC_SUPP_CoolFan         = 0x00000100,    // 是否支持散热风扇功能
        NVC_SUPP_AudioPlug       = 0x00000200,    // 是否支持音频开关功能
        NVC_SUPP_TempMonitor     = 0x00000400,    // 是否支持温度采集功能
        NVC_SUPP_HumiMonitor     = 0x00000800,    // 是否支持湿度采集功能
        NVC_SUPP_GpioReset       = 0x00001000,    // 通过GPIO 复位设备(重启设备)
        NVC_SUPP_RTC             = 0x00002000,    // 是否支持 RTC 实时时钟
        NVC_SUPP_PIR             = 0x00004000,    // 是否支持 PIR
        NVC_SUPP_DoorBell        = 0x00008000,    // 是否支持 Door Bell
    }Nvc_Driver_Cap_E;
</pre>

<h4><a name="appendix3_id_h"></a>7. 附件3——修改记录</h4>
<table border="1" id="history_tb0_id">
    <caption>表5.1 修改记录</caption>
    <tr>
        <th style="width: 130px;">日期</th>
        <th style="width: 130px;">修改人</th>
        <th>记录</th>
    </tr>
</table>








<script type="text/javascript">
    var mMsgtype_info = [];
    mMsgtype_info[0] = ["NVC_MsgType_DEVICE","0x00","设备信息"];
    mMsgtype_info[1] = ["NVC_MsgType_BUTTON","0x01","按键"];
    mMsgtype_info[2] = ["NVC_MsgType_LDR","0x02","光敏电阻"];
    mMsgtype_info[3] = ["NVC_MsgType_IRC","0x03","IRC"];
    mMsgtype_info[4] = ["NVC_MsgType_IFRRED_LIGHT","0x04","红外灯"];
    mMsgtype_info[5] = ["NVC_MsgType_STATE_LIGHT","0x05","状态灯"];
    mMsgtype_info[6] = ["NVC_MsgType_PTZ","0x06","云台"];
    mMsgtype_info[7] = ["NVC_MsgType_NIGHT_LIGHT","0x07","小夜灯"];
    mMsgtype_info[8] = ["NVC_MsgType_AUDIO_PLUG","0x08","音频开关"];
    mMsgtype_info[9] = ["NVC_MsgType_TEMP_MONITOR","0x09","温度检测"];
    mMsgtype_info[10] = ["NVC_MsgType_HUMI_MONITOR","0x0A","湿度检测"];
    mMsgtype_info[11] = ["NVC_MsgType_DOUB_LENS","0x0B","双镜头"];
    mMsgtype_info[12] = ["NVC_MsgType_RESET_IO","0x0C","复位"];
    mMsgtype_info[13] = ["NVC_MsgType_RTC","0x0D","实时时钟"];
    mMsgtype_info[14] = ["NVC_MsgType_PIR","0x0E","红外移动侦测"];
    mMsgtype_info[15] = ["NVC_MsgType_DOOR_BELL","0x0F","门铃"];
    CreateTableFormArra("mMsgType_tb0_id",mMsgtype_info);

    var sMsgDvc_info = [];
    sMsgDvc_info[0] = ["DEVICE_GET_INFO_REQ","1","获取驱动信息","无"];
    sMsgDvc_info[1] = ["DEVICE_GET_INFO_RESP","2","获取驱动信息响应信息","Nvc_Driver_Ver_Info_S"];
    sMsgDvc_info[2] = ["DEVICE_GET_CAPACITY_REQ","3","获取设备外设能力集","无"];
    sMsgDvc_info[3] = ["DEVICE_GET_CAPACITY_RESP","4","获取设备外设能力集响应信息","Nvc_Driver_Cap_Info_s"];
    sMsgDvc_info[4] = ["DEVICE_SUB_REPORT_MSG_REQ","5","设置是否接收驱动事件及状态变化请求","Nvc_Attached_Driver_Msg_s"];
    sMsgDvc_info[5] = ["DEVICE_SUB_REPORT_MSG_RESP","6","设置是否接收驱动事件及状态变化信息","无"];
    sMsgDvc_info[6] = ["DEVICE_REPORT_DRIVER_ERR","7","主动上报驱动警告，错误","无"];
    CreateTableFormArra("sMsgDvc_tb0_id",sMsgDvc_info);

    var sMsgBut_info = [];
    sMsgBut_info[0] = ["BUTTON_GET_STATUS_REQ","1","获取button当前状态","无"];
    sMsgBut_info[1] = ["BUTTON_GET_STATUS_RESP","2","获取button当前状态响应信息","Nvc_Button_Status_S"];
    sMsgBut_info[2] = ["BUTTON_REPORT_STATUS_MSG","3","上报button当前状态","Nvc_Button_Status_S"];
    CreateTableFormArra("sMsgBut_tb0_id",sMsgBut_info);

    var sMsgLDR_info = [];
    sMsgLDR_info[0] = ["LDR_GET_STATUS_REQ","1","获取光敏电阻当前状态","无"];
    sMsgLDR_info[1] = ["LDR_GET_STATUS_RESP","2","获取光敏电阻当前状态响应信息","Nvc_Ldr_Status_S"];
    sMsgLDR_info[2] = ["LDR_REPORT_STATUS_MSG","3","上报光敏电阻当前状态","Nvc_Ldr_Status_S"];
    sMsgLDR_info[3] = ["LDR_SET_SENSITIVITY_REQ","5","设置光敏电阻检测灵敏度","Nvc_Ldr_Senitivity_S"];
    sMsgLDR_info[4] = ["LDR_SET_SENSITIVITY_RESP","6","设置光敏电阻检测灵敏度响应信息","无"];
    sMsgLDR_info[5] = ["LDR_GET_SENSITIVITY_REQ","7","获取光敏电阻检测灵敏度","无"];
    sMsgLDR_info[6] = ["LDR_GET_SENSITIVITY_RESP","8","获取光敏电阻检测灵敏度响应信息","Nvc_Ldr_Senitivity_S"];
    CreateTableFormArra("sMsgLDR_tb0_id",sMsgLDR_info);

    var sMsgIRC_info = [];
    sMsgIRC_info[0] = ["IRC_GET_TYPE_REQ","1","获取设备ircut类型","无"];
    sMsgIRC_info[1] = ["IRC_GET_TYPE_RESP","2","获取设备ircut类型响应信息","Nvc_Ircut_Info_S"];
    sMsgIRC_info[2] = ["IRC_SET_SWITCH_REQ","3","设置ircut切换状态","Nvc_Ircut_Status_S"];
    sMsgIRC_info[3] = ["IRC_SET_SWITCH_RESP","4","设置ircut切换状态响应信息","无"];
    sMsgIRC_info[4] = ["IRC_GET_STATUS_REQ","5","查询ircut当前状态","无"];
    sMsgIRC_info[5] = ["IRC_GET_STATUS_RESP","6","查询ircut当前状态响应信息","Nvc_Ircut_Status_S"];
    CreateTableFormArra("sMsgIrc_tb0_id",sMsgIRC_info);


    var sMsgIfrLi_info = [];
    sMsgIfrLi_info[0] = ["IfrLIGHT_SET_SWITCH_REQ","1","设置红外灯打开/关闭","Nvc_Lamp_Control_S"];
    sMsgIfrLi_info[1] = ["IfrLIGHT_SET_SWITCH_RESP","2","设置红外灯打开/关闭响应信息","无"];
    sMsgIfrLi_info[2] = ["IfrLIGHT_GET_STATUS_REQ","3","查询红外灯打开/关闭状态","无"];
    sMsgIfrLi_info[3] = ["IfrLIGHT_GET_STATUS_RESP","4","查询红外灯打开/关闭状态响应信息","Nvc_Lamp_Status_S"];
    CreateTableFormArra("sMsgIfrLi_tb0_id",sMsgIfrLi_info);


    var sMsgStaLi_info = [];
    sMsgStaLi_info[0] = ["StaLIGHT_SET_STATUS_REQ","1","设置LED灯显示方式","Nvc_State_Led_Control_S"];
    sMsgStaLi_info[1] = ["StaLIGHT_SET_STATUS_RESP","2","设置LED灯显示方式响应信息","无"];
    CreateTableFormArra("sMsgStaLi_tb0_id",sMsgStaLi_info);

    var sMsgPTZ_info = [];
    sMsgPTZ_info[0] = ["PTZ_GET_INFO_REQ","1","云台信息查询","无"];
    sMsgPTZ_info[1] = ["PTZ_GET_INFO_RESP","2","云台信息查询响应信息","Nvc_Ptz_Info_S"];
    sMsgPTZ_info[2] = ["PTZ_SET_COMMON_REQ","3","通用云台控制指令","Nvc_Ptz_Control_S"];
    sMsgPTZ_info[3] = ["PTZ_SET_COMMON_RESP","4","通用云台控制指令响应信息","无"];
    sMsgPTZ_info[4] = ["PTZ_SET_PRESET_POINT_REQ","5","设置云台预置位","Nvc_Ptz_PrePoint_S"];
    sMsgPTZ_info[5] = ["PTZ_SET_PRESET_POINT_RESP","6","设置云台预置位响应信息","Nvc_PTZ_Coordinate_S"];
    sMsgPTZ_info[6] = ["PTZ_CLR_PRESET_POINT_REQ","7","清除云台预置位","Nvc_Ptz_PrePoint_S"];
    sMsgPTZ_info[7] = ["PTZ_CLR_PRESET_POINT_RESP","8","清除云台预置位响应信息","无"];
    sMsgPTZ_info[8] = ["PTZ_REPORT_INFO_MSG","9","上报云台信息","Nvc_Ptz_Info_S"];
    sMsgPTZ_info[9] = ["PTZ_ENPORT_PRESET_POINT_REQ","11","导入预置位配置","Nvc_PrePoint_CONF_s"];
    sMsgPTZ_info[10] = ["PTZ_ENPORT_PRESET_POINT_RESP","12","导入预置位配置响应信息","无"];
    sMsgPTZ_info[11] = ["PTZ_SET_CRUISE_PATH_REQ","13","设置巡航路径","Nvc_Preset_CONF_s"];
    sMsgPTZ_info[12] = ["PTZ_SET_CRUISE_PATH_RESP","14","设置巡航路径响应信息","无"];


    CreateTableFormArra("sMsgPTZ_tb0_id",sMsgPTZ_info);

    var sMsgNiLi_info = [];
    sMsgNiLi_info[0] = ["NiLIGHT_SET_STATUS_REQ","1","设置小夜灯打开/关闭","Nvc_Night_Light_Control_s"];
    sMsgNiLi_info[1] = ["NiLIGHT_SET_STATUS_RESP","2","设置小夜灯打开/关闭响应信息","无"];
    sMsgNiLi_info[2] = ["NiLIGHT_GET_STATUS_REQ","3","查询小夜灯打开/关闭状态","无"];
    sMsgNiLi_info[3] = ["NiLIGHT_GET_STATUS_RESP","4","查询小夜灯打开/关闭状态响应信息","Nvc_Night_Light_status_s"];
    CreateTableFormArra("sMsgNiLi_tb0_id",sMsgNiLi_info);

    var sMsgAuPl_info = [];
    sMsgAuPl_info[0] = ["Speaker_SET_STATUS_REQ","1","设置音频扬声器打开/关闭","Nvc_Audio_Plug_Control_s"];
    sMsgAuPl_info[1] = ["Speaker_SET_STATUS_RESP","2","设置音频扬声器打开/关闭响应信息","无"];
    sMsgAuPl_info[2] = ["Speaker_GET_STATUS_REQ","3","查询音频扬声器打开/关闭状态","无"];
    sMsgAuPl_info[3] = ["Speaker_GET_STATUS_RESP","4","查询音频扬声器打开/关闭状态响应信息","Nvc_Audio_Plug_status_s"];
    sMsgAuPl_info[4] = ["Microph_Set_STATUS_REQ","5","设置音频麦克风打开/关闭","Nvc_Audio_Plug_status_s"];
    sMsgAuPl_info[5] = ["Microph_Set_STATUS_RESP","6","设置音频麦克风打开/关闭状态响应信息","无"];
    sMsgAuPl_info[6] = ["Microph_GET_STATUS_REQ","7","查询音频麦克风开关","无"];
    sMsgAuPl_info[7] = ["Microph_GET_STATUS_RESP","8","查询音频麦克风开关状态响应信息","Nvc_Audio_Plug_status_s"];
    CreateTableFormArra("sMsgAuPl_tb0_id",sMsgAuPl_info);

    var sMsgTemp_info = [];
    sMsgTemp_info[0] = ["TmpMONITOR_SET_REPORT_REQ","1","设置温度定时采集上报请求","Nvc_Temperature_Timer_S"];
    sMsgTemp_info[1] = ["TmpMONITOR_SET_REPORT_RESP","2","设置温度定时采集上报响应信息","无"];
    sMsgTemp_info[2] = ["TmpMONITOR_GET_VALUE_REQ","3","查询当前温度值","无"];
    sMsgTemp_info[3] = ["TmpMONITOR_GET_VALUE_RESP","4","查询当前温度值响应信息","Nvc_Temperature_Timer_S"];
    sMsgTemp_info[4] = ["TmpMONITOR_REPORT_VALUE_MSG","5","上报当前温度值消息","Nvc_Temperature_Timer_S"];
    CreateTableFormArra("sMsgTemp_tb0_id",sMsgTemp_info);

    var sMsgHumi_info = [];
    sMsgHumi_info[0] = ["HumMONITOR_SET_REPORT_REQ","1","设置湿度度定时采集上报请求","Nvc_Humidity_Timer_S"];
    sMsgHumi_info[1] = ["HumMONITOR_SET_REPORT_RESP","2","设置湿度定时采集上报响应信息","无"];
    sMsgHumi_info[2] = ["HumMONITOR_GET_VALUE_REQ","3","查询当前湿度值","无"];
    sMsgHumi_info[3] = ["HumMONITOR_GET_VALUE_RESP","4","查询当前湿度值响应信息","Nvc_Humidity_Timer_S"];
    sMsgHumi_info[4] = ["HumMONITOR_REPORT_VALUE_MSG","5","上报当前湿度值消息","Nvc_Humidity_Timer_S"];
    CreateTableFormArra("sMsgHumi_tb0_id",sMsgHumi_info);

    var sMsgDbL_info = [];
    sMsgDbL_info[0] = ["DobLENS_SET_STATUS_REQ","1","设置双镜头切换(日用镜头/夜用镜头)","Nvc_Lens_Control_S"];
    sMsgDbL_info[1] = ["DobLENS_SET_STATUS_RESP","2","设置双镜头切换响应信息","无"];
    sMsgDbL_info[2] = ["DobLENS_GET_STATUS_REQ","3","查询双镜头使用状态","无"];
    sMsgDbL_info[3] = ["DobLENS_GET_STATUS_RESP","4","查询双镜头使用状态响应信息","Nvc_Lens_Status_S"];
    CreateTableFormArra("sMsgDubL_tb0_id",sMsgDbL_info);

    var sMsgRio_info = [];
    sMsgRio_info[0] = ["ResetIO_SET_STATUS_REQ","1","设置双镜头切换(日用镜头/夜用镜头)","无"];
    sMsgRio_info[1] = ["ResetIO_SET_STATUS_RESP","2","设置双镜头切换响应信息","无"];
    CreateTableFormArra("sMsgRio_tb0_id",sMsgRio_info);

    var sMsgRTC_info = [];
    sMsgRTC_info[0] = ["RTC_SET_TIME_REQ","1","设置（同步）实时时钟时间","Nvc_Time_Struct_S"];
    sMsgRTC_info[1] = ["RTC_SET_TIME_RESP","2","设置（同步）实时时钟时间响应信息","无"];
    sMsgRTC_info[2] = ["RTC_GET_TIME_REQ","3","获取实时时钟当前时间","无"];
    sMsgRTC_info[3] = ["RTC_GET_TIME_RESP","4","获取实时时钟当前时间响应信息","Nvc_Time_Struct_S"];
    CreateTableFormArra("sMsgRTC_tb0_id",sMsgRTC_info);

    var sMsgPIR_info = [];
    sMsgPIR_info[0] = ["PIR_GET_STATUS_REQ","1","获取当前热设红外检测模块状态","无"];
    sMsgPIR_info[1] = ["PIR_GET_STATUS_RESP","2","获取当前热设红外检测模块状态响应信息","Nvc_PIRStatus_Struct_S"];
    sMsgPIR_info[2] = ["PIR_REPORT_STATUS","3","上报当前热设红外检测模块状态","Nvc_PIRStatus_Struct_S"];
    sMsgPIR_info[3] = ["PIR_SET_DEAD_TIME_REQ","5","设置死区时间","Nvc_PIRConf_Struct_S"];
    sMsgPIR_info[4] = ["PIR_SET_DEAD_TIME_RESP","6","设置死去时间响应消息","无"];
    CreateTableFormArra("sMsgPIR_tb0_id",sMsgPIR_info);

    var sMsgDoorBell_info = [];
    sMsgDoorBell_info[0] = ["DOORBELL_SET_STATUS_REQ","1","发送DoorBell配置信息","Nvc_DoorBellConf_Status_S"];
    sMsgDoorBell_info[1] = ["DOORBELL_SET_STATUS_RESP","2","发送DoorBell配置响应信息","无"];
    CreateTableFormArra("sMsgDoorBell_tb0_id",sMsgDoorBell_info);


    var oldMsg_info = [];
    var tb_i = 0;
    oldMsg_info[ tb_i++ ] = [ "0X0001","NVC_QUERY_DRIVER_INFO_REQ","获取驱动信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0002","NVC_QUERY_DRIVER_INFO_RESP","获取驱动信息响应信息","Nvc_Driver_Ver_Info_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0003","NVC_QUERY_DRIVER_CAPACITY_REQ","获取设备外设能力集","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0004","NVC_QUERY_DRIVER_CAPACITY_RESP","获取设备外设能力集响应信息","Nvc_Driver_Cap_Info_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0005","NVC_SET_ATTACHED_DRIVER_MSG_REQ","设置是否接收驱动事件及状态变化请求","Nvc_Attached_Driver_Msg_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0006","NVC_SET_ATTACHED_DRIVER_MSG_RESP","设置是否接收驱动事件及状态变化信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0101","NVC_QUERY_BUTTON_STATUS_REQ","获取button当前状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0102","NVC_QUERY_BUTTON_STATUS_RESP","获取button当前状态响应信息","Nvc_Button_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0103","NVC_REPORT_BUTTON_STATUS_MSG","上报button当前状态","Nvc_Button_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0201","NVC_QUERY_LDR_STATUS_REQ","获取光敏电阻当前状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0202","NVC_QUERY_LDR_STATUS_RESP","获取光敏电阻当前状态响应信息","Nvc_Ldr_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0203","NVC_REPORT_LDR_STATUS_MSG","上报光敏电阻当前状态","Nvc_Ldr_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0205","NVC_SET_LDR_SENSITIVITY_REQ","设置光敏电阻检测灵敏度","Nvc_Ldr_Senitivity_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0206","NVC_SET_LDR_SENSITIVITY_RESP","设置光敏电阻检测灵敏度响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0207","NVC_QUERY_LDR_SENSITIVITY_REQ","获取光敏电阻检测灵敏度","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0208","NVC_QUERY_LDR_SENSITIVITY_RESP","获取光敏电阻检测灵敏度响应信息","Nvc_Ldr_Senitivity_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0301","NVC_QUERY_IRC_TYPE_REQ","获取设备ircut类型","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0302","NVC_QUERY_IRC_TYPE_RESP","获取设备ircut类型响应信息","Nvc_Ircut_Info_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0303","NVC_CONTROL_IRC_SWITCH_REQ","设置ircut切换状态","Nvc_Ircut_Control_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0304","NVC_CONTROL_IRC_SWITCH_RESP","设置ircut切换状态响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0305","NVC_QUERY_IRC_STATUS_REQ","查询ircut当前状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0306","NVC_QUERY_IRC_STATUS_RESP","查询ircut当前状态响应信息","Nvc_Ircut_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0401","NVC_CONTROL_LAMP_SWITCH_REQ","设置红外灯打开/关闭","Nvc_Lamp_Control_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0402","NVC_CONTROL_LAMP_SWITCH_RESP","设置红外灯打开/关闭响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0403","NVC_QUERY_LAMP_STATUS_REQ","查询红外灯打开/关闭状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0404","NVC_QUERY_LAMP_STATUS_RESP","查询红外灯打开/关闭状态响应信息","Nvc_Lamp_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0501","NVC_CONTROL_STATE_LED_REQ","设置LED灯显示方式","Nvc_State_Led_Control_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0502","NVC_CONTROL_STATE_LED_RESP","设置LED灯显示方式响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0601","NVC_QUERY_PTZ_INFO_REQ","云台信息查询","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0602","NVC_QUERY_PTZ_INFO_RESP","云台信息查询响应信息","Nvc_Ptz_Info_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0603","NVC_CONTROL_PTZ_COMMON_REQ","通用云台控制指令","Nvc_Ptz_Control_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0604","NVC_CONTROL_PTZ_COMMON_RESP","通用云台控制指令响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0605","NVC_SET_PTZ_PRESET_POINT_REQ","设置云台预置位巡航","Nvc_Ptz_Cruise_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0606","NVC_SET_PTZ_PRESET_POINT_RESP","设置云台预置位巡航响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0607","NVC_CLR_PTZ_PRESET_POINT_REQ","清除预置位","Nvc_Ptz_PrePoint_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0608","NVC_CLR_PTZ_PRESET_POINT_RESP","清除预置位响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0609","NVC_REPORT_PTZ_INFO_MSG","上报云台信息","Nvc_Ptz_Info_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X060B","NVC_ENPORT_PTZ_PRESET_POINT_REQ","导入云台预置位配置信息","Nvc_PrePoint_CONF_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X060C","NVC_ENPORT_PTZ_PRESET_POINT_RESP","导入云台预置位配置信息响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X060D","NVC_SET_PTZ_CRUISE_PATH_REQ","设置云台预置位巡航路径","Nvc_PRESET_CONF_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X060E","NVC_SET_PTZ_CRUISE_PATH_RESP","设置云台预置位巡航路径","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0701","NVC_CONTROL_NIGHT_LIGHT_SWITCH_REQ","设置小夜灯打开/关闭","Nvc_Night_Light_Control_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0702","NVC_CONTROL_NIGHT_LIGHT_SWITCH_RESP","设置小夜灯打开/关闭响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0703","NVC_QUERY_NIGHT_LIGHT_STATUS_REQ","查询小夜灯打开/关闭状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0704","NVC_QUERY_NIGHT_LIGHT_STATUS_RESP","查询小夜灯打开/关闭状态响应信息","Nvc_Night_Light_status_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0801","NVC_CONTROL_Speaker_SWITCH_REQ","设置音频扬声器打开/关闭","Nvc_Audio_Plug_Control_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0802","NVC_CONTROL_Speaker_SWITCH_RESP","设置音频扬声器打开/关闭响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0803","NVC_QUERY_Speaker_STATUS_REQ","查询音频扬声器打开/关闭状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0804","NVC_QUERY_Speaker_STATUS_RESP","查询音频扬声器打开/关闭状态响应信息","Nvc_Audio_Plug_status_s" ];
    oldMsg_info[ tb_i++ ] = [ "0x0805","NVC_CONTROL_Microph_SWITCH_REQ","设置音频麦克风打开/关闭","Nvc_Audio_Plug_Control_s" ];
    oldMsg_info[ tb_i++ ] = [ "0x0806","NVC_CONTROL_Microph_SWITCH_RESP","设置音频麦克风打开/关闭响应信息	        ","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0807","NVC_QUERY_Microph_STATUS_REQ","查询音频麦克风状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0808","NVC_QUERY_Microph_STATUS_RESP","查询音频麦克风状态响应信息","Nvc_Audio_Plug_status_s" ];
    oldMsg_info[ tb_i++ ] = [ "0X0901","NVC_SET_TEMPERATURE_TIMER_REQ","设置温度定时采集上报请求","Nvc_Temperature_Timer_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0902","NVC_SET_TEMPERATURE_TIMER_RESP","设置温度定时采集上报响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0903","NVC_QUERY_TEMPERATURE_VALUE_REQ","查询当前温度值","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0904","NVC_QUERY_TEMPERATURE_VALUE_RESP","查询当前温度值响应信息","Nvc_Temperature_Value_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0905","NVC_REPORT_TEMPERATURE_VALUE_MSG","上报当前温度值消息","Nvc_Temperature_Value_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0A01","NVC_SET_HUMIDITY_TIMER_REQ","设置湿度度定时采集上报请求","Nvc_Humidity_Timer_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0A02","NVC_SET_HUMIDIT_TIMER_RESP","设置湿度定时采集上报响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0A03","NVC_QUERY_HUMIDIT_VALUE_REQ","查询当前湿度值","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0A04","NVC_QUERY_HUMIDIT_VALUE_RESP","查询当前湿度值响应信息","Nvc_Humidity_Value_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0A05","NVC_REPORT_HUMIDIT_VALUE_MSG","上报当前湿度值消息","Nvc_Humidity_Value_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0B01","NVC_CONTROL_LENS_SWITCH_REQ","设置双镜头切换(日用镜头/夜用镜头)","Nvc_Lens_Control_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0B02","NVC_CONTROL_LENS_SWITCH_RESP","设置双镜头切换响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0B03","NVC_QUERY_LENS_STATUS_REQ","查询双镜头使用状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0B04","NVC_QUERY_LENS_STATUS_RESP","查询双镜头使用状态响应信息","Nvc_Lens_Status_S" ];
    oldMsg_info[ tb_i++ ] = [ "0X0C01","NVC_GPIO_RESET_REQ","通过GPIO复位系统","无" ];
    oldMsg_info[ tb_i++ ] = [ "0X0C02","NVC_GPIO_RESET_RESP","通过GPIO复位系统响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0D01","NVC_RTC_SET_TIME_REQ","设置（同步）实时时钟时间","Nvc_Time_Struct_S" ];
    oldMsg_info[ tb_i++ ] = [ "0x0D02","NVC_RTC_SET_TIME_RESP","设置（同步）实时时钟时间响应信息","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0D03","NVC_RTC_GET_TIME_REQ","获取实时时钟当前时间","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0D04","NVC_RTC_GET_TIME_RESP","获取实时时钟当前时间响应信息","Nvc_Time_Struct_S" ];

    oldMsg_info[ tb_i++ ] = [ "0x0E01","NVC_PIR_GET_STATUS_REQ","获取当前热设红外检测模块状态","无" ];
    oldMsg_info[ tb_i++ ] = [ "0x0E02","NVC_PIR_GET_STATUS_RESP","获取当前热设红外检测模块状态响应信息","Nvc_PIRStatus_Struct_S" ];
    oldMsg_info[ tb_i++ ] = [ "0x0E03","NVC_PIR_REPORT_STATUS","上报当前热设红外检测模块状态","Nvc_PIRStatus_Struct_S" ];
    oldMsg_info[ tb_i++ ] = [ "0x0E05","NVC_PIR_SET_DEAD_TIME_REQ","设置死区时间","Nvc_PIRConf_Struct_S" ];
    oldMsg_info[ tb_i++ ] = [ "0x0E06","NVC_PIR_SET_DEAD_TIME_RESP","设置死去时间响应消息","无" ];

    oldMsg_info[ tb_i++ ] = [ "0x0F01","NVC_DOORBELL_SET_STATUS_REQ","发送DoorBell配置信息","Nvc_DoorBellConf_Status_S" ];
    oldMsg_info[ tb_i   ] = [ "0x0F02","NVC_DOORBELL_SET_STATUS_RESP","发送DoorBell配置响应信息","无" ];
    CreateTableFormArra("MsgOld_tb0_id",oldMsg_info);


    var vCaptMuster_tb_info = [];
    vCaptMuster_tb_info[0] = ["NVC_SUPP_ButtonMonitor","0x00000001","是否支持按键检测"];
    vCaptMuster_tb_info[1] = ["NVC_SUPP_LdrMonitor","0x00000002","是否支持日夜模式检测(light dependent resistors detection"];
    vCaptMuster_tb_info[2] = ["NVC_SUPP_Ircut","0x00000004","是否支持滤光片切换"];
    vCaptMuster_tb_info[3] = ["NVC_SUPP_IfrLamp","0x00000008","是否支持红外灯"];
    vCaptMuster_tb_info[4] = ["NVC_SUPP_DoubleLens","0x00000010","是否支持双镜头"];
    vCaptMuster_tb_info[5] = ["NVC_SUPP_StateLed","0x00000020","是否支持状态灯显示"];
    vCaptMuster_tb_info[6] = ["NVC_SUPP_PTZ","0x00000040","是否支持云台功能"];
    vCaptMuster_tb_info[7] = ["NVC_SUPP_NightLight","0x00000080","是否支持小夜灯功能"];
    vCaptMuster_tb_info[8] = ["NVC_SUPP_CoolFan","0x00000100","是否支持散热风扇功能"];
    vCaptMuster_tb_info[9] = ["NVC_SUPP_AudioPlug","0x00000200","是否支持音频开关功能"];
    vCaptMuster_tb_info[10] = ["NVC_SUPP_TempMonitor","0x00000400","是否支持温度采集功能"];
    vCaptMuster_tb_info[11] = ["NVC_SUPP_HumiMonitor","0x00000800","是否支持湿度采集功能"];
    vCaptMuster_tb_info[12] = ["NVC_SUPP_GpioReset","0x00001000","通过GPIO 复位设备(重启设备)"];
    vCaptMuster_tb_info[13] = ["NVC_SUPP_RTC","0x00002000","是否支持 RTC 实时时钟"];
    vCaptMuster_tb_info[14] = ["NVC_SUPP_PIR","0x00004000","是否支持 PIR"];
    vCaptMuster_tb_info[15] = ["NVC_SUPP_DoorBell","0x00008000","是否支持 Door Bell"];
    CreateTableFormArra("CapMuster_tb0_id",vCaptMuster_tb_info);


    var vhistory_tb_info = [];
    tb_i = 0;
    vhistory_tb_info[ tb_i++ ] = ["2015-8-10","孟奥杰","忘了"];
    vhistory_tb_info[ tb_i++ ] = ["2015-8-24","孟奥杰","<ol style=text-align:left >针对 F10 机型添加了 PIR（红外硬件移动侦测） DoorBell（门铃控制）两种消息类型，"+
    "修改 StateLight 类型，添加呼吸灯控制<li>PIR 消息类型的修改内容请详见 [4.1 消息主类型]"+
    "[4.2.F SubMessageType:PIR][5. 附件1——兼容老协议 MsgType]</li><li>DoorBell "+
    "消息类型的修改内容针详见 [4.1 消息主类型][4.2.10 SubMessageType:DoorBell]"+
    "[5. 附件1——兼容老协议 MsgType]</li><li>呼吸灯功能，修改的内容详见 "+
    "[4.2.6 SubMessageType:State Light] 章节。</li></ol>"];
    vhistory_tb_info[ tb_i++ ] = ["2015-9-23","孟奥杰","<ol style=text-align:left ><li>主要是针对云台做了一系列的修改，" +
     "详见 [4.2.7 SubMessageType:PTZ] </li></ol>"];
     vhistory_tb_info[ tb_i++ ] = ["2015-9-28","孟奥杰","<ol style=text-align:left ><li>对云台的预置位，巡航部分做了更详" +
      "细的而解释说明 详见 [4.2.7 SubMessageType:PTZ] </li></ol>"];

    CreateTableFormArra("history_tb0_id",vhistory_tb_info);

    CreateBackToTop();
    jf_ShowCurTime();
</script>
';


$html=$buildHtml->pf_getPage($pContent);
echo $html;