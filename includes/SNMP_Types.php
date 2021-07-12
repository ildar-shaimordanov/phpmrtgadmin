<?

/*
* phpMyAdmin:
*       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
* Version:
*       1.00
*
* Copyright (C) 2005-2006 Ildar N. Shaimordanov
*
* Licensed under the terms of the GNU General Public License:
* http://opensource.org/licenses/gpl-license.php
*
* File Name: SNMP_Types.php
*       This is the part of phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*/

/*
*
* the more info about interface types and their groups were published at
* http://people.ee.ethz.ch/~oetiker/webtools/mrtg/doc/cfgmaker.en.html#predefined_filter_variables
*
*/

class SNMP_Types
{

	var $if_type	= array(
		1	=>	'Other',

		2	=>	'regular1822',
		3	=>	'hdh1822',
		4	=>	'ddnX25',
		5	=>	'rfc877x25',
		6	=>	'ethernetCsmacd',
		7	=>	'iso88023Csmacd',
		8	=>	'iso88024TokenBus',
		9	=>	'iso88025TokenRing',
		10	=>	'iso88026Man',
		11	=>	'starLan',
		12	=>	'proteon10Mbit',
		13	=>	'proteon80Mbit',
		14	=>	'hyperchannel',
		15	=>	'fddi',
		16	=>	'lapb',
		17	=>	'sdlc',
		18	=>	'ds1',
		19	=>	'e1',
		20	=>	'basicISDN',
		21	=>	'primaryISDN',
		22	=>	'propPointToPointSerial',
		23	=>	'ppp',
		24	=>	'softwareLoopback',
		25	=>	'eon',
		26	=>	'ethernet-3Mbit',
		27	=>	'nsip',
		28	=>	'slip',
		29	=>	'ultra',
		30	=>	'ds3',
		31	=>	'sip',
		32	=>	'frame-relay',
		33	=>	'rs232',
		34	=>	'para',
		35	=>	'arcnet',
		36	=>	'arcnetPlus',
		37	=>	'atm',
		38	=>	'miox25',
		39	=>	'sonet',
		40	=>	'x25ple',
		41	=>	'iso88022llc',
		42	=>	'localTalk',
		43	=>	'smdsDxi',
		44	=>	'frameRelayService',
		45	=>	'v35',
		46	=>	'hssi',
		47	=>	'hippi',
		48	=>	'modem',
		49	=>	'aal5',
		50	=>	'sonetPath',
		51	=>	'sonetVT',
		52	=>	'smdsIcip',
		53	=>	'propVirtual',
		54	=>	'propMultiplexor',
		55	=>	'100BaseVG',
		56	=>	'Fibre Channel',
		57	=>	'HIPPI Interface',
		58	=>	'Obsolete for FrameRelay',
		59	=>	'ATM Emulation of 802.3 LAN',
		60	=>	'ATM Emulation of 802.5 LAN',
		61	=>	'ATM Emulation of a Circuit',
		62	=>	'FastEthernet (100BaseT)',
		63	=>	'ISDN & X.25',
		64	=>	'CCITT V.11/X.21',
		65	=>	'CCITT V.36',
		66	=>	'CCITT G703 at 64Kbps',
		67	=>	'Obsolete G702 see DS1-MIB',
		68	=>	'SNA QLLC',
		69	=>	'Full Duplex Fast Ethernet (100BaseFX)',
		70	=>	'Channel',
		71	=>	'Radio Spread Spectrum (802.11)',
		72	=>	'IBM System 360/370 OEMI Channel',
		73	=>	'IBM Enterprise Systems Connection',
		74	=>	'Data Link Switching',
		75	=>	'ISDN S/T Interface',
		76	=>	'ISDN U Interface',
		77	=>	'Link Access Protocol D (LAPD)',
		78	=>	'IP Switching Opjects',
		79	=>	'Remote Source Route Bridging',
		80	=>	'ATM Logical Port',
		81	=>	'AT&T DS0 Point (64 Kbps)',
		82	=>	'AT&T Group of DS0 on a single DS1',
		83	=>	'BiSync Protocol (BSC)',
		84	=>	'Asynchronous Protocol',
		85	=>	'Combat Net Radio',
		86	=>	'ISO 802.5r DTR',
		87	=>	'Ext Pos Loc Report Sys',
		88	=>	'Apple Talk Remote Access Protocol',
		89	=>	'Proprietary Connectionless Protocol',
		90	=>	'CCITT-ITU X.29 PAD Protocol',
		91	=>	'CCITT-ITU X.3 PAD Facility',
		92	=>	'MultiProtocol Connection over Frame/Relay',
		93	=>	'CCITT-ITU X213',
		94	=>	'Asymetric Digitial Subscriber Loop (ADSL)',
		95	=>	'Rate-Adapt Digital Subscriber Loop (RDSL)',
		96	=>	'Symetric Digitial Subscriber Loop (SDSL)',
		97	=>	'Very High Speed Digitial Subscriber Loop (HDSL)',
		98	=>	'ISO 802.5 CRFP',
		99	=>	'Myricom Myrinet',
		100	=>	'Voice recEive and transMit (voiceEM)',
		101	=>	'Voice Foreign eXchange Office (voiceFXO)',
		102	=>	'Voice Foreign eXchange Station (voiceFXS)',
		103	=>	'Voice Encapulation',
		104	=>	'Voice Over IP Encapulation',
		105	=>	'ATM DXI',
		106	=>	'ATM FUNI',
		107	=>	'ATM IMA',
		108	=>	'PPP Multilink Bundle',
		109	=>	'IBM IP over CDLC',
		110	=>	'IBM Common Link Access to Workstation',
		111	=>	'IBM Stack to Stack',
		112	=>	'IBM Virtual IP Address (VIPA)',
		113	=>	'IBM Multi-Protocol Channel Support',
		114	=>	'IBM IP over ATM',
		115	=>	'ISO 802.5j Fiber Token Ring',
		116	=>	'IBM Twinaxial Data Link Control (TDLC)',
		117	=>	'Gigabit Ethernet',
		118	=>	'Higher Data Link Control (HDLC)',
		119	=>	'Link Access Protocol F (LAPF)',
		120	=>	'CCITT V.37',
		121	=>	'CCITT X.25 Multi-Link Protocol',
		122	=>	'CCITT X.25 Hunt Group',
		123	=>	'Transp HDLC',
		124	=>	'Interleave Channel',
		125	=>	'Fast Channel',
		126	=>	'IP (for APPN HPR in IP Networks)',
		127	=>	'CATV MAC Layer',
		128	=>	'CATV Downstream Interface',
		129	=>	'CATV Upstream Interface',
		130	=>	'Avalon Parallel Processor',
		131	=>	'Encapsulation Interface',
		132	=>	'Coffee Pot',
		133	=>	'Circuit Emulation Service',
		134	=>	'ATM Sub Interface',
		135	=>	'Layer 2 Virtual LAN using 802.1Q',
		136	=>	'Layer 3 Virtual LAN using IP',
		137	=>	'Layer 3 Virtual LAN using IPX',
		138	=>	'IP Over Power Lines',
		139	=>	'Multi-Media Mail over IP',
		140	=>	'Dynamic synchronous Transfer Mode (DTM)',
		141	=>	'Data Communications Network',
		142	=>	'IP Forwarding Interface',

		162	=>	'Cisco Express Forwarding Interface',
	);

	var $if_grouped	= array(
		'ethernet'	=>	array(
			'desc'	=>	'Ethernet',
			'list'	=>	array(6, 7, 26, 62, 69, 117),
		),
		'isdn'	=>	array(
			'desc'	=>	'ISDN',
			'list'	=>	array(20, 21, 63, 75, 76, 77),
		),
		'dialup'	=>	array(
			'desc'	=>	'Dialup',
			'list'	=>	array(20, 21, 23, 63, 75, 76, 77, 81, 82, 108), // +$this->if_grouped['isdn']['list']
		),
		'atm'	=>	array(
			'desc'	=>	'ATM',
			'list'	=>	array(37, 49, 107, 105, 106, 114, 134),
		),
		'wan'	=>	array(
			'desc'	=>	'WAN, Frame Relay and High Speed Serial',
			'list'	=>	array(22, 32, 44, 46),
		),
		'lan'	=>	array(
			'desc'	=>	'LAN',
			'list'	=>	array(6, 7, 8, 9, 11, 15, 26, 55, 59, 60, 62, 69, 115, 117), // +$this->if_grouped['ethernet']['list']
		),
		'dsl'	=>	array(
			'desc'	=>	'ADSL, RDSL, HDSL and SDSL',
			'list'	=>	array(94, 95, 96, 97),
		),
		'loopback'	=>	array(
			'desc'	=>	'Loopback',
			'list'	=>	array(24),
		),
		'ciscovlan'	=>	array(
			'desc'	=>	'Cisco VLAN',
			'list'	=>	array(),
		),
	);

	function isEthernet($system)
	{
		return $this->isType($system, $this->if_grouped['ethernet']['list']);
	}

	function isIsdn($system)
	{
		return $this->isType($system, $this->if_grouped['isdn']['list']);
	}

	function isDialup($system)
	{
		return $this->isType($system, $this->if_grouped['dialup']['list']);
	}

	function isAtm($system)
	{
		return $this->isType($system, $this->if_grouped['atm']['list']);
	}

	function isWan($system)
	{
		return $this->isType($system, $this->if_grouped['wan']['list']);
	}

	function isLan($system)
	{
		return $this->isType($system, $this->if_grouped['lan']['list']);
	}

	function isDsl($system)
	{
		return $this->isType($system, $this->if_grouped['dsl']['list']);
	}

	function isLoopback($system)
	{
		return $this->isType($system, $this->if_grouped['loopback']['list']);
	}

	function isCiscoVlan($system)
	{
		return preg_match('/vlan/i', $system['system']['if_snmp_descr']);
	}

	function isType($system, $if_types)
	{
		return in_array($system['system']['if_type_num'], $if_types);
	}

	function getVlanId($system)
	{
		return $system['system']['if_port_name'] ? $system['system']['if_port_name'] : false;
	}

	function getMtu($system)
	{
		return false;
	}

}

?>