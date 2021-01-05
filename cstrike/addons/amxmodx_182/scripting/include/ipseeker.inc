/* 
*
* by KinSprite
* 
* This module requires "wry.dat" in amxx_datadir (Default is addons/amxmodx/data)
*
* The "wry.dat", you only can use "Coralwry.Dat" or "QQwry.Dat",
*   then change its name to "wry.dat".
*
*  Coralwry, see http://www.coralqq.com
*  QQwry, see http://www.cz88.net/
*
* This file is provided as is (no warranties).
*/

#if defined _IPSEEKER_included
	#endinput
#endif
#define _IPSEEKER_included

#if AMXX_VERSION_NUM >= 175
 #pragma reqlib ipseeker
 #if !defined AMXMODX_NOAUTOLOAD
  #pragma loadlib ipseeker
 #endif
#else
 #pragma library ipseeker
#endif

enum {
	ipseek_all,
	ipseek_country,
	ipseek_area,
}; //ipseak_type

// get an IP's address with ipseek_type.
// if use_UTF8 is 0, the output will be in ANSI code page; 
//   else , the output will be in UTF-8 code page.
native ipseeker(ip[], ipseek_type = ipseek_all, out_address[], addr_len, use_UTF8 = 0);

// get an IP's country , area
// if use_UTF8 is 0, the output will be in ANSI code page; 
//   else , the output will be in UTF-8 code page.
native ipseeker2(ip[], out_country[], country_len, country_use_UTF8, out_area[], area_len, area_use_UTF8);
