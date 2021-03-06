/*
Coder:     Chiyuan.Ma
Date:       2016-12-13

Abstract:

*/
//==============================================================================
// C
// Linux
// local
#include "HI3518EV200_HAL.h"
// remote
#include "../HAL.h"
#include "../../Tool/String.h"

//=============================================================================
// DATA TYPE
typedef struct {
#define DC_HAL_ADC_Busy			          0x80
#define DC_HAL_ADC_Blocked                0x10
#define DC_HAL_ADC_BLockMask	          0x0F
#define DC_HAL_ADC_BlockIncUnit	          0x01
	uint32 aStatus;
	uint16 aCh0_Result;
	uint16 aCh1_Result;
	uint16 aCh2_Result;
	uint16 aCh3_Result;
	void (*afCh0_CallBack)(uint16);
	void (*afCh1_CallBack)(uint16);
	void (*afCh2_CallBack)(uint16);
	void (*afCh3_CallBack)(uint16);
} mADCopt_Info;

//=============================================================================
// MACRO
// CONSTANT
// FUNCTION
////////////////////////////////////////////////////////////////////////////////
#define DC_HAL_ADC_DefSensiBit 		10
#define DC_HAL_ADC_DefSensi		1024
#define DC_HAL_ADC_DefVol		3300
#define DC_HAL_ADC_Channel		2

#define DF_HAL_ADC_ZERO_Get_Data	\
	(HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & 0xFF)

#define DF_HAL_ADC_Enable_Channel(_channel)	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) | (0x01<<(_channel+8)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Disable_Channel(_channel)	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x01<<(_channel+8))) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_ModelSel_Single	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x01<<13)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_ModelSel_Multiple	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) | (0x01<<13) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_PowerDown_Support	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) | (0x01<<14) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_PowerDown_NoSupport	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x01<<14)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Reset_In	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) | (0x01<<15) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Reset_Exit	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x01<<15)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Deglitch_Disable	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) | (0x01<<14) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Deglitch_Enable	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x01<<14)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Data_Delta(_deltaData)	\
	HAL_writel((HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0x0F<<20))) \
	           |((_deltaData&0x0F)<<20) \
	           , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Active_Bit(_activeBit)	\
	HAL_writel(((HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG)) & (~(0xFF<<24))) | ((_activeBit&0xFF)<<24))\
	           , HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))

#define DF_HAL_ADC_Get_Result_Data(_channel)	\
	((HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_RESULT_DATA)) & (0xFF << _channel*8))	\
	 >> _channel*8)

#define DF_HAL_ADC_Int_Enable \
	HAL_writel(0x01, HAL_ADC_REGAddr(HAL_OFST_ADC_INT_ENABLE))

#define DF_HAL_ADC_Int_Disable \
	HAL_writel(0x00, HAL_ADC_REGAddr(HAL_OFST_ADC_INT_ENABLE))

#define DF_HAL_ADC_Int_GetStatus(_channel)	\
	(HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_INT_STATUS)) & (0x01 << _channel))

#define DF_HAL_ADC_IntFlag_Clr(_channel)	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_INT_CLR)) | (0x01<<_channel) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_INT_CLR))

#define DF_HAL_ADC_IntFlag_NoClr(_channel)	\
	HAL_writel( HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_INT_CLR)) & (~(0x01<<_channel)) \
	            , HAL_ADC_REGAddr(HAL_OFST_ADC_INT_CLR))

#define DF_HAL_ADC_Start	\
	HAL_writel(0x01, HAL_ADC_REGAddr(HAL_OFST_ADC_START))

#define DF_HAL_ADC_Stop		\
	HAL_writel(0x01, HAL_ADC_REGAddr(HAL_OFST_ADC_STOP))

#define DF_HAL_Cipher_Reset	\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) | 0x01		\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))
#define DF_HAL_Cipher_Set		\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) & (~0x01)	\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))

#define DF_HAL_CipherCken_CLK_Open	\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) | (0x01 << 1)	\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))
#define DF_HAL_CipherCken_CLK_Close	\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) & (~(0x01 << 1))	\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))

#define DF_HAL_ADC_SrstReq_Reset	\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) | (0x01 << 2)	\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))
#define DF_HAL_ADC_SrstReq_Set		\
	HAL_writel(HAL_readl(HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC)) & (~(0x01 << 2))	\
	           , HAL_CRGx_Addr(HAL_OFST_CRG31_RsTADC))

#define DF_HAL_ADC_IsChannelA \
	((HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))&(0x01<<8))?1:0)

#define DF_HAL_ADC_IsChannelB \
	((HAL_readl(HAL_ADC_REGAddr(HAL_OFST_ADC_CONFIG))&(0x01<<9))?1:0)

#define	DF_ADC_8bitTO10bit(_val)\
	(((_val+1) << 2) - 1)

//==============================================================================
//extern
//local
static void sfADC_Reset(void);
static int32 sfADC_Init(void);
static int32 sfADC_Uninit(void);
static mAD_Ifo sfGetInfo(void);
static int32 gfADC_StartConvert(uint32 iCmd);
static int32 sfGetChValue(uint32 iCmd);
static void sfRegCallback(uint32 iCmd, void (*ifCallbackHandle)(uint16));
static irqreturn_t ADC_int_Handler(int irq, void *id);
//global

//==============================================================================
//extern
//local
static mAD_Ifo soAD_Ifo = {
	DC_HAL_ADC_DefVol,
	DC_HAL_ADC_DefSensi,
	DC_HAL_ADC_Channel
};

static mADCopt_Info soADCResoult;
#define DF_HAL_ADC_IncBlock			{ soADCResoult.aStatus+=DC_HAL_ADC_BlockIncUnit;}
#define DF_HAL_ADC_IsBlock			((soADCResoult.aStatus&DC_HAL_ADC_Blocked)?1:0)
#define DF_HAL_ADC_ClrBlock 			{soADCResoult.aStatus&=~(DC_HAL_ADC_Blocked+DC_HAL_ADC_BLockMask);}
//
//global
mClass_AD const gcoClass_AD = {
	.prfInit = sfADC_Init,
	.prfUninit = sfADC_Uninit,
	.prfGetInfo = sfGetInfo,
	.prfSetOpt = gfADC_StartConvert,
	.prfGetValue = sfGetChValue,
	.prfRegCallBack = sfRegCallback,
};

//==============================================================================
//Global

//------------------------------------------------------------------------------
//Local
//---------- ---------- ---------- ----------
/*  static void sfADC_Reset(void)
@introduction:
    复位 ADC 模块

@parameter:
    viod

@return:
    void

*/
static void sfADC_Reset(void)
{
	soADCResoult.aStatus = 0;
	DF_HAL_ADC_SrstReq_Reset;
	DF_HAL_ADC_Reset_In;
	udelay(1);
	DF_HAL_ADC_SrstReq_Set;
	DF_HAL_ADC_Reset_Exit;
	DF_HAL_ADC_Enable_Channel(0);
	DF_HAL_ADC_Active_Bit(0xFF);
	DF_HAL_ADC_ModelSel_Single;
	DF_HAL_ADC_PowerDown_NoSupport;
	DF_HAL_ADC_Start;
	DF_HAL_ADC_Int_Enable;
}

//---------- ---------- ---------- ----------
/*  static int32 sfADC_Init(void)
@introduction:
    初始化 ADC 模块

@parameter:
    void

@return:
    0   SUCCESS

*/
static int32 sfADC_Init(void)
{
	gClassStr.afMemset((void *)&soADCResoult, 0x00, sizeof(mADCopt_Info));
	DF_HAL_CipherCken_CLK_Open;
	sfADC_Reset();

	if (request_irq
	    (IRQ_SAR_ADC, ADC_int_Handler, IRQF_SHARED, "NVC_DRV_HAL_ADC",
	     &soAD_Ifo)) {
#if DEBUG_INIT
		NVCPrint("Register IRQ_ADC IntRequest Fail!");
#endif
	}
#if DEBUG_INIT
	NVCPrint("Register IRQ_ADC IntRequest Success!");
#endif

	return 0;
}

//---------- ---------- ---------- ----------
/*  static int32 sfADC_Uninit(void)
@introduction:
    释放 ADC 初始化时注册的资源

@parameter:
    void

@return:
    0   SUCCESS

*/
static int32 sfADC_Uninit(void)
{
	DF_HAL_ADC_Stop;
	DF_HAL_ADC_SrstReq_Reset;
	free_irq(IRQ_SAR_ADC, &soAD_Ifo);
	return 0;
}

//---------- ---------- ---------- ----------
/*  static mAD_Ifo sfGetInfo(void)
@introduction:
    取得 该平台的AD 基本信息

@parameter:
    void

@return:
    mAD_Ifo

*/
static mAD_Ifo sfGetInfo(void)
{
	return soAD_Ifo;
}

//---------- ---------- ---------- ----------
/*  static int32 gfADC_StartConvert(uint32 iCmd)
@introduction:
    启动 ADC 指定通道的电压采集

@parameter:
    iCmd
        ADC 通道值

@return:
    DC_HAL_ADCRet_Success
        启动成功
    DC_HAL_ADCRet_Busy
        ADC 忙

*/
static int32 gfADC_StartConvert(uint32 iCmd)
{
	uint8 _i;

	_i = 70;
	while ((_i > 0) && (soADCResoult.aStatus & DC_HAL_ADC_Busy)) {
		_i--;
		udelay(3);
	}
	if (_i == 0) {
		DF_HAL_ADC_IncBlock if (DF_HAL_ADC_IsBlock) {
			sfADC_Reset();
		} else {
			return DC_HAL_ADCRet_Busy;
		}
	} else {
		DF_HAL_ADC_ClrBlock;
	}
	if (iCmd & DC_HAL_ADCOpt_StartCov) {
		//DF_HAL_ADC_StartTransform( iCmd&DC_HAL_ADCOpt_ChAreaMask );
		DF_HAL_ADC_Start;
		soADCResoult.aStatus |= DC_HAL_ADC_Busy;
	}
	return DC_HAL_ADCRet_Success;
}

//---------- ---------- ---------- ----------
/*  static int32 sfGetChValue(uint32 iCmd)
@introduction:
    启动获得 ADC 通道数值

@parameter:
    iCmd
        ADC 通道

@return:
    返回通道的 AD 值

*/
static int32 sfGetChValue(uint32 iCmd)
{
	int32 tRet = 0;

	if (soADCResoult.aStatus & DC_HAL_ADC_Busy) {
		tRet |= DC_HAL_ADCRet_Busy;
	}

	switch (iCmd & DC_HAL_ADCOpt_ChAreaMask) {
	case 0:
		tRet |= DF_ADC_8bitTO10bit(soADCResoult.aCh0_Result);
		break;
	case 1:
		tRet |= soADCResoult.aCh1_Result;
	default:
		break;
	}
	return tRet;
}

//---------- ---------- ---------- ----------
/*  static void sfRegCallback( \
					uint32 iCmd\
					,void (*ifCallbackHandle)(uint16) )
@introduction:
    注册获取到 ADC 数值后的回调函数

@parameter:
    iCmd
        ADC 通道
    ifCallbackHandle
        注册的函数

@return:
    void

*/
static void sfRegCallback(uint32 iCmd, void (*ifCallbackHandle)(uint16))
{
	if ((iCmd & DC_HAL_ADCOpt_ChAreaMask) == 0) {
		soADCResoult.afCh0_CallBack = ifCallbackHandle;
	} else if ((iCmd & DC_HAL_ADCOpt_ChAreaMask) == 1) {
		soADCResoult.afCh1_CallBack = ifCallbackHandle;
	}
}

//---------- ---------- ---------- ----------
/*  static irqreturn_t ADC_int_Handler(int irq, void *id)
@introduction:
    注册至 Linux 内核的ADC中断服务函数

@parameter:
    (详参 Linux 内核说明文档)

@return:
    (详参 Linux 内核说明文档)


*/
static irqreturn_t ADC_int_Handler(int irq, void *id)
{
	if (DF_HAL_ADC_IsChannelB) {
		soADCResoult.aCh1_Result = DF_HAL_ADC_Get_Result_Data(1);
		if (soADCResoult.afCh1_CallBack != NULL) {
			soADCResoult.afCh1_CallBack(soADCResoult.aCh1_Result);
		}
	} else if (DF_HAL_ADC_IsChannelA) {
		if (DF_HAL_ADC_Int_GetStatus(0)) {
			soADCResoult.aCh0_Result =
			    DF_HAL_ADC_Get_Result_Data(0);
			if (soADCResoult.afCh0_CallBack != NULL) {
				soADCResoult.afCh0_CallBack(soADCResoult.
				                            aCh0_Result);
			}
		}
	}

	DF_HAL_ADC_IntFlag_Clr(0);
	//DF_HAL_ADC_ClrBusy;
	soADCResoult.aStatus &= ~DC_HAL_ADC_Busy;
	return 0;
}

//==============================================================================
//Others
