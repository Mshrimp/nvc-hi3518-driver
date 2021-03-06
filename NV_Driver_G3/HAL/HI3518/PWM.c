/*
Coder:      aojie.meng
Date:       2015-9-14

Abstract:

*/
//==============================================================================
// C
// Linux
// local
#include "HI3518_HAL.h"
// remote
#include "../HAL.h"

//=============================================================================
// MACRO
// CONSTANT
// FUNCTION
#define DC_HAL_PWM_AllChannel	2
#define DC_HAL_PWM_MainFrq		3000000
// #define DC_HAL_PWM_MaxDuty           67108863

//PWM0
#define DC_PWM0_PIN_G			5
#define DC_PWM0_PIN_B			2
#define DC_PWM0_MUX				47
#define DC_PWM0_MUXVal			0x01
//PWM1
#define DC_PWM1_PIN_G			5
#define DC_PWM1_PIN_B			3
#define DC_PWM1_MUX				48
#define DC_PWM1_MUXVal			0x01
//PWM2 Disable

#define DC_PWM_CLK_SOURCE 		HAL_CRGx_Addr(HAL_OFST_CRG14_RsTPWM)
#define DC_PWM_CLK_Enable		0x02
#define DC_PWM_MOD_Reset		0x01

#define DF_PWM_CLK_ENABLE		HAL_writel(HAL_readl(DC_PWM_CLK_SOURCE)|DC_PWM_CLK_Enable,DC_PWM_CLK_SOURCE)
#define DF_PWM_CLK_DISABLE		HAL_writel(HAL_readl(DC_PWM_CLK_SOURCE)&(~DC_PWM_CLK_Enable),DC_PWM_CLK_SOURCE)

#define DF_PWM_MOD_RESET		HAL_writel(HAL_readl(DC_PWM_CLK_SOURCE)|DC_PWM_MOD_Reset,DC_PWM_CLK_SOURCE)
#define DF_PWM_MOD_SET			HAL_writel(HAL_readl(DC_PWM_CLK_SOURCE)&(~DC_PWM_MOD_Reset),DC_PWM_CLK_SOURCE)

#define DF_PWMx_SET_Period(_g,_v)		HAL_writel(_v,HAL_OFST_PWMx_CFG0(_g))
#define DF_PWMx_SET_Width(_g,_v)		HAL_writel(_v,HAL_OFST_PWMx_CFG1(_g))
#define DF_PWMx_SET_Number(_g,_v)		HAL_writel(_v,HAL_OFST_PWMx_CFG2(_g))
#define DF_PWMx_SET_Config(_g,_v)		HAL_writel(_v,HAL_OFST_PWMx_CTRL(_g))
#define DF_PWMx_GET_IDLE(_g)			((HAL_readl(HAL_OFST_PWMx_STATE2(_g))&0x400)?0:1)
#define DC_PWMx_CFG_STA_Enable			0x01
#define DC_PWMx_CFG_STA_inv				0x02
#define DC_PWMx_CFG_STA_Keep			0x04
#define DC_PWMx_GET_Period(_g)			HAL_readl(HAL_OFST_PWMx_STATE0(_g))
#define DC_PWMx_GET_Width(_g)			HAL_readl(HAL_OFST_PWMx_STATE1(_g))
#define DC_PWMx_GET_Status(_g)			HAL_readl(HAL_OFST_PWMx_STATE2(_g))

#define DC_PWMx_Disable(_g)				HAL_writel(0x00,HAL_OFST_PWMx_CTRL(_g))

//==============================================================================
//extern
//local
static void sfPWM_GetInfo(void);
static int32 sfPWM_GetChannelFromPinInfo(mGPIOPinIfo *ipPinIfo);
static void sfPWM_Start(void);
static void sfPWM_Stop(uint8 iWhichOne);
//global

//==============================================================================
//extern
//local
static mPWM_Info sPWMInfo = {
	.aMainFreq = DC_HAL_PWM_MainFrq,
	.aSumChannel = DC_HAL_PWM_AllChannel,
};

static uint8 sPWMStatus = 0;
#define DC_PWM_Sta_Init		0x01
#define DC_PWM_Sta_Running	0x02
#define DC_PWM_Sta_Pin1		0x04
#define DC_PWM_Sta_Pin2		0x08
//global
mClass_PWM const gcoClass_PWM = {
	.pInfo = &sPWMInfo,
	.prfGCFPin = sfPWM_GetChannelFromPinInfo,
	.prfStart = sfPWM_Start,
	.prfStop = sfPWM_Stop,
	.prfGetInfo = sfPWM_GetInfo
};

//==============================================================================
//Global

//------------------------------------------------------------------------------
//Local
//---------- ---------- ---------- ----------
/*  static void sfPWM_GetInfo(void)
@introduction:
    获取本平台的 PWM 基本信息

@parameter:
    void

@return:
    void

*/
static void sfPWM_GetInfo(void)
{

	uint32 iStatus;
	sPWMInfo.aMainFreq = DC_HAL_PWM_MainFrq;
	sPWMInfo.aSumChannel = DC_HAL_PWM_AllChannel;

	switch (sPWMInfo.aNum) {
	case 0:
	case 1: {
		sPWMInfo.aDuty = DC_PWMx_GET_Period(sPWMInfo.aNum);
		sPWMInfo.aWidth = DC_PWMx_GET_Width(sPWMInfo.aNum);
		iStatus = DC_PWMx_GET_Status(sPWMInfo.aNum);
		sPWMInfo.aCycleNum = iStatus & 0x3FF;

		if (iStatus & 0x400) {
			sPWMInfo.aStatus = DC_PWM_StaBusy;
		}
		if (iStatus & 0x800) {
			sPWMInfo.aStatus |= DC_PWM_StaKeep;
		}
	}
	break;
	case 0xFF: {
		sPWMInfo.aNum = 2;
	}
	break;
	default:
		sPWMInfo.aStatus |= DC_PWM_Err;
		break;
	}
}

//---------- ---------- ---------- ----------
/*  static int32 sfPWM_GetChannelFromPinInfo( mGPIOPinIfo *ipPinIfo )
@introduction:
    根据引脚信息反馈 PWM 通道号

@parameter:
    ipPinIfo
        指向芯片引脚信息的指针

@return:
    >=0 通道号
    -1  输入引脚信息错误

*/
static int32 sfPWM_GetChannelFromPinInfo(mGPIOPinIfo *ipPinIfo)
{
	if ((ipPinIfo->aGroup == DC_PWM0_PIN_G)
	    && (ipPinIfo->aBit == DC_PWM0_PIN_B)) {
		return 0;
	}
	if ((ipPinIfo->aGroup == DC_PWM1_PIN_G)
	    && (ipPinIfo->aBit == DC_PWM1_PIN_B)) {
		return 1;
	}
	return -1;
}

//---------- ---------- ---------- ----------
/*  static void sfPWM_Start(void)
@introduction:
    启动 PWM

@parameter:
    void

@return:
    void

*/
static void sfPWM_Start(void)
{

	uint32 tCmd = 0;

	if (!(sPWMStatus & DC_PWM_Sta_Init)) {
		DF_PWM_MOD_RESET;
		DF_PWM_MOD_SET;
		DF_PWM_CLK_ENABLE;
		sPWMStatus |= DC_PWM_Sta_Init;
	}

	if (sPWMInfo.aNum == 0) {
		if (!(sPWMStatus & DC_PWM_Sta_Pin1)) {
			DC_PIN_SET_IO_OUT(DC_PWM0_PIN_G, DC_PWM0_PIN_B,
			                  DC_PWM0_MUXVal, DC_PWM0_MUX);
			sPWMStatus |= DC_PWM_Sta_Pin1;
		}
	} else if (sPWMInfo.aNum == 1) {
		if (!(sPWMStatus & DC_PWM_Sta_Pin2)) {
			DC_PIN_SET_IO_OUT(DC_PWM1_PIN_G, DC_PWM1_PIN_B,
			                  DC_PWM1_MUXVal, DC_PWM1_MUX);
			sPWMStatus |= DC_PWM_Sta_Pin2;
		}
	} else {
		return;
	}

	DC_PWMx_Disable(sPWMInfo.aNum);

	if (sPWMInfo.aCmd & DC_PWM_CmdKeep) {
		tCmd |= DC_PWMx_CFG_STA_Keep;
	} else {
		DF_PWMx_SET_Number(sPWMInfo.aNum, sPWMInfo.aCycleNum);
	}
	if (sPWMInfo.aCmd & DC_PWM_CmdKeep) {
		tCmd |= DC_PWMx_CFG_STA_inv;
	}
	tCmd |= DC_PWMx_CFG_STA_Enable;

	DF_PWMx_SET_Period(sPWMInfo.aNum, sPWMInfo.aDuty);
	DF_PWMx_SET_Width(sPWMInfo.aNum, sPWMInfo.aWidth);

	DF_PWMx_SET_Config(sPWMInfo.aNum, tCmd);
}

//---------- ---------- ---------- ----------
/*  asjkdaskdjasdk
@introduction:
    停止 PWM

@parameter:
    iWhichOne\
        PWM 通道

@return:
    void

*/
static void sfPWM_Stop(uint8 iWhichOne)
{

	if ((iWhichOne != 0) || (iWhichOne != 1)) {
		return;
	} else {
		DF_PWMx_SET_Config(iWhichOne, 0x00);
	}
}
