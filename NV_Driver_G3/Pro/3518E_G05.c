/*
Coder:      yan.xu
Date:       2016-5-18

Abstract:
	IfrFilt(IRCUT)
		IRC1(GPIO7_6,switch,low_pass,high_block)
		IRC2(GPIO7_7,enable,low_en,high_disen)
	IfrLi
		GPIO0_0 H-on L-off
	AudioSwitch
		GPIO0_6 H_off L_on
	button
		visitor button( GPIO1_7, high_normal, low_press)
			after high to low to high, it should send a notify to app.
		wifi button( GPIO0_1 high_normal, low_press  )
	LDR
		Adc 1st channel.
	DoorLock
		GPIO5_7 high_open low_close
	EAS( emergency alert system )
		GPIO5_4 high_danger low_safty ( for camera )
	State light
		(wifi status light)GPIO0_3 high_off low_on
	FM1288
		IIC_SCL( GPIO5_5 )
		IIC_SDA( GPIO5_6 )

鍫寸キ鑱嗗礁
IfrFilt                           1
IfrLi                             1
AuPl                              1
Button(0 WIFI)                    1
Button(1 Door)                    1
LDR                               1
Doorlock                          1
EAS(EAS emergency alert system)   0
StaLi                             1
FM1288                            1
*/
//==============================================================================
// C
// Linux
// local
#include "ProInclude.h"
// remote
#include "../HAL/HAL.h"
#include "../Tool/String.h"

//=============================================================================
// DATA TYPE
typedef struct {
	uint16 aOnDgr;
	uint16 aOffDgr;
	uint16 aBrthNum;
	uint16 *apBrthDgr;
	uint32 aPTime;
	uint32 aNTime;
	uint32 aTimer;
	uint16 aOrder;
	uint8 aRunMod;
} mSL_CfgIfo;

//=============================================================================
// MACRO
// CONSTANT
// FUNCTION

//==============================================================================
//extern
//local
static mClass_CTA *sfPro_3518E_G05_WR_CONF(void);
static int32 sfPro_3518E_G05_Init(void);
static int32 sfPro_3518E_G05_Uninit(void);
//global

//==============================================================================
//extern
extern mGPIOPinIfo sdefPin_IfrFilt_TypeB[];
//local
static mGPIOPinIfo sG05_Button_Pin[] = {
	{0, 1, 73, 0x00},
	{1, 7, 31, 0x00},
};

static mGPIOPinIfo sG05_StateLight_Pin[] = {
	{5, 2, 47, 0x00},
	{5, 3, 48, 0x00},
};

//global
mProInfo pgs3518E_G05_ProInfo = {
	.aPro = {.aKey = "G05", .aID = 0x1005,},
	.aChip = {.aKey = "3518E", .aID = 0x3518E,},
	.aSubCmd = NULL,
	.afRewriteAndConfig = sfPro_3518E_G05_WR_CONF,
	.afInit = sfPro_3518E_G05_Init,
	.afUninit = sfPro_3518E_G05_Uninit,
};

//==============================================================================
//Global

//------------------------------------------------------------------------------
//Local
//---------- ---------- ---------- ----------
/*  static mClass_CTA*	sfPro_3518E_G05_WR_CONF(void)
@introduction:

@parameter:

@return:

*/
static mClass_CTA *sfPro_3518E_G05_WR_CONF(void)
{
	mClass_CTA *tClassCTA;
	tClassCTA = (mClass_CTA *) kmalloc(sizeof(mClass_CTA), GFP_ATOMIC);
	gClassStr.afMemset((uint8 *) tClassCTA, 0x00, sizeof(mClass_CTA));

	// LDR
	tClassCTA->apLDR = &gClassLDR;
	// Infrared light
	tClassCTA->apIfrLi = &gClassIfrLi;
	// Infrared filter
	tClassCTA->apIfrFilter = &gClassIfrFilt;
	tClassCTA->apIfrFilter->aType = DC_IfrFltOpt_TypeB;
	tClassCTA->apIfrFilter->apPinArr->apPin = sdefPin_IfrFilt_TypeB;
	tClassCTA->apIfrFilter->apPinArr->aNum = 2;
	// Audio Plug
	tClassCTA->apAudioPlug = &gClassAudioPlug;
	// Button
	tClassCTA->apButton = &gClassButton;
	tClassCTA->apButton->apPinArr->apPin = sG05_Button_Pin;
	tClassCTA->apButton->apPinArr->aNum = 2;
	// State Light
	tClassCTA->apStateLi = &gClassStateLi;
	tClassCTA->apStateLi->apPinArr->apPin = sG05_StateLight_Pin;
	tClassCTA->apStateLi->apPinArr->aNum = 2;
	tClassCTA->apStateLi->OnStatus = 0x01;

	// Door Bell
	tClassCTA->apDoorBell = &gClassDoorBell;
	// Door Lock
	tClassCTA->apDoorLock = &gClassDoorLock;
	// FM1288
	tClassCTA->apFM1288 = &gClassFM1288;
	//EAS
	tClassCTA->apEAS = &gClassEAS;

	return tClassCTA;
}

//---------- ---------- ---------- ----------
/*  static int32		sfPro_3518E_G05_Init(void)
@introduction:

@parameter:

@return:

*/
static int32 sfPro_3518E_G05_Init(void)
{
	mClass_CTA *tClassCTA = gClassPro.apCTA;
	// HAL
	gClassHAL.ADC->prfInit();
	gClassHAL.Timer->prfInit();
	gClassHAL.PeriodEvent->afInit();

	// LDR
	tClassCTA->apLDR->afInit();
	// Infrared Light
	tClassCTA->apIfrLi->afInit();
	tClassCTA->apIfrLi->afSetStatus(DC_IfLi_Off);
	// Infrared filter
	tClassCTA->apIfrFilter->afInit();
	tClassCTA->apIfrFilter->afSetStatus(DC_IfrFlt_BlockLi);
	// Audio Plug
	tClassCTA->apAudioPlug->afInit();
	tClassCTA->apAudioPlug->afSetStatus(DC_AuPl_Speaker_Off);
	// State Light
	tClassCTA->apStateLi->afInit();
	// Button
	tClassCTA->apButton->afInit();
	// Door Bell
	tClassCTA->apDoorBell->afInit();
	// Door Lock
	tClassCTA->apDoorLock->afInit();
	// FM1288
	tClassCTA->apFM1288->afInit();
	// EAS emergency alert system
	tClassCTA->apEAS->afInit();

	return 0;
}

//---------- ---------- ---------- ----------
/*  static int32		sfPro_3518E_G05_Uninit(void)
@introduction:

@parameter:

@return:

*/
static int32 sfPro_3518E_G05_Uninit(void)
{
	mClass_CTA *tClassCTA = gClassPro.apCTA;

	// LDR
	tClassCTA->apLDR->afUninit();
	// Infrared Light
	tClassCTA->apIfrLi->afUninit();
	// Infrared filter
	tClassCTA->apIfrFilter->afUninit();
	// Audio Plug
	tClassCTA->apAudioPlug->afUninit();
	// State light
	tClassCTA->apStateLi->afUninit();
	// Button
	tClassCTA->apButton->afUninit();
	// Door Bell
	tClassCTA->apDoorBell->afUninit();
	// Door Lock
	tClassCTA->apDoorLock->afUninit();
	// FM1288
	tClassCTA->apFM1288->afUninit();
	// EAS
	tClassCTA->apEAS->afUninit();

	// HAL
	gClassHAL.PeriodEvent->afUninit();
	gClassHAL.Timer->prfUninit();
	gClassHAL.ADC->prfUninit();
	kfree(tClassCTA);
	return 0;
}

//==============================================================================
//Others
