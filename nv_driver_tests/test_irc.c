#include "netview_driver_api.h"

void com_head(void)
{
    printf("\n\n\n");
    printf("======================================== =========");
    printf("==============================\n");
    printf("                      Input the command to control the module\n\n");
    printf(" 1) Set IRC switch to day                      2) Set IRC switch to night\n");
    printf(" 0) Quit");
    printf("\n");
    printf("Please inter the command.\n\n");
    printf("Command :");
}


int main()
{
	int ifd;
	int iret;
	int ichose = 0;
	static NV_S32 s32NvcFd = -1;
	Nvc_Driver_Cap_Info_S stCapInfo;
	NV_IRC_STATUS_E enIrcAutoStatus = -1;
	
	s32NvcFd = open(NV_DRIVER_DEVICE, O_RDWR | O_NONBLOCK);
	if (s32NvcFd < 0)
	{
		NV_ERROR("Can't open %s.", NV_DRIVER_DEVICE);
		return -1;
	}

	if (NV_SUCCESS != nv_drv_init_device_info(s32NvcFd, NULL, &stCapInfo))
	{
		close(s32NvcFd);
		return -2;
	}

    if ((stCapInfo.u32CapMask & NVC_SUPP_LdrMonitor) == 0 || 
		(stCapInfo.u32CapMask & NVC_SUPP_Ircut) == 0)
	{
		NV_ERROR("Dont support IRC switch for this device.");
		close(s32NvcFd);
		return -3;
	}
 
	system("clear");
	//input command repeatedly, if input 'q' or "quit" then end the programme
	while(1)
	{
	   // iflags = 0;
		com_head(); 

		scanf("%d", &ichose);
	//	  printf("Had input %d\n",ichose);
		if(ichose == 1 || ichose == 2)
		{ 
			enIrcAutoStatus = ichose == 1 ? NV_IRC_STATUS_DAY : NV_IRC_STATUS_NIGHT;
            if ((stCapInfo.u32CapMask & NVC_SUPP_Ircut) && NV_SUCCESS == nv_drv_set_filter_switch(s32NvcFd, enIrcAutoStatus))
        	{
				printf("Switch Ifilter to %s success!", ichose == 1 ? "day" : "night");
        	}
		}
		else if(ichose == 0 )
		{
			break;
		}
		else
		{
			printf("Wrong command!\n");
		}
		getchar();
		fflush(stdin);
		//printf("%s: no found\n", cacommand);
	 }
	close(s32NvcFd);
	return 0;
}
