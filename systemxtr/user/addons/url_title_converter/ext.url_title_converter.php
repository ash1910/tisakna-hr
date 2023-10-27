<?php

class Url_title_converter_ext {

	var $name 			= 'URL Title Converter';
	var $version 		= '1.0.2';
	var $description 	= 'Convert foreign-language characters in URL Title field to custom characters.';
	var $settings_exist = 'n';
	var $docs_url 		= 'http://addons.caleydon.com/ee-url-title-converter/';

	var $settings = array();

	/**
	 * Constructor
	 *
	 * @param   mixed   Settings array or empty string if none exist.
	 */
	function __construct($settings='')
	{
		$this->settings = $settings;
	}

	function RTL_characters() 
	{
		ee()->lang->loadfile('url_title_converter');

		$characters = array(
        	'162'	=>	lang('cm_162'),	// ¢ - Cent sign
        	'163'	=>	lang('cm_163'),	// £ - Pound sign
			'165'	=>	lang('cm_165'),	// ¥ - Yen sign
        	'176'	=>	lang('cm_138'),	// ° - Degree sign
			'8356'	=>	lang('cm_8356'), // ₤ - Lira sign
			'8364'	=>	lang('cm_8364'), // € - Euro sign
			'138'	=>	lang('cm_138'),	// Š
			'142'	=>	lang('cm_142'),	// Ž
			'154'	=>	lang('cm_154'),	// š
			'158'	=>	lang('cm_1158'), // ž
			'159'	=>	lang('cm_159'),	// Ÿ
			'192'	=> 	lang('cm_192'),	// À
			'193'	=> 	lang('cm_193'),	// Á
			'194'	=> 	lang('cm_194'),	// Â
			'195'	=> 	lang('cm_195'),	// Ã
			'196'	=>	lang('cm_196'),	// Ä
			'197'	=>	lang('cm_197'),	// Å
			'198'	=>	lang('cm_198'),	// Æ
			'200'	=>	lang('cm_200'),	// È
			'201'	=>	lang('cm_201'),	// É
			'202'	=>	lang('cm_202'),	// Ê
			'203'	=>	lang('cm_203'),	// Ë 
			'204'	=>	lang('cm_204'),	// Ì
			'205'	=>	lang('cm_205'),	// Í 
			'206'	=>	lang('cm_206'),	// Î
			'207'	=>	lang('cm_207'),	// Ï 
			'210'	=> 	lang('cm_210'),	// Ò
			'211'	=> 	lang('cm_211'),	// Ó
			'212'	=> 	lang('cm_212'),	// Ô
			'213'	=> 	lang('cm_213'),	// Õ
			'214'	=>	lang('cm_214'),	// Ö
			'217'	=> 	lang('cm_217'),	// Ù
			'218'	=> 	lang('cm_218'),	// Ú
			'219'	=> 	lang('cm_219'),	// Û
			'220'	=> 	lang('cm_220'),	// Ü
			'221'	=> 	lang('cm_221'),	// Ý
			'223'	=>	lang('cm_223'),	// ß
    		'224'	=>	lang('cm_224'),	// à
    		'225'	=> 	lang('cm_225'),	// á
    		'226'	=> 	lang('cm_226'),	// â
    		'229'	=>	lang('cm_229'),	// å
    		'227'	=>	lang('cm_227'),	// ã
    		'228'	=> 	lang('cm_228'),	// ä
    		'230'	=>	lang('cm_230'),	// æ
    		'199'	=>	lang('cm_199'),	// Ç
    		'231'	=>	lang('cm_231'),	// ç
    		'232'	=>	lang('cm_232'),	// è
    		'233'	=>	lang('cm_233'),	// é
    		'234'	=>	lang('cm_234'),	// ê
    		'235'	=>	lang('cm_235'),	// ë 
    		'236'	=> 	lang('cm_236'),	// ì
    		'237'	=> 	lang('cm_237'),	// í
    		'238'	=> 	lang('cm_238'),	// î
    		'239'	=>	lang('cm_239'),	// ï
    		'241'	=>	lang('cm_241'),	// ñ
    		'242'	=> 	lang('cm_242'),	// ò
    		'243'	=> 	lang('cm_243'),	// ó
    		'244'	=>	lang('cm_244'),	// ô
    		'245'	=> 	lang('cm_245'),	// õ
    		'246'	=>	lang('cm_246'),	// ö
    		'249'	=> 	lang('cm_249'),	// ù 
    		'250' 	=> 	lang('cm_250'),	// ú
    		'251'	=>	lang('cm_251'),	// û
    		'252'	=>	lang('cm_252'),	// ü
	   		'253'	=>	lang('cm_253'),	// ý
	   		'255'	=>	lang('cm_255'),	// ÿ
			'256'	=>	lang('cm_256'),	// Ā
			'257'	=>	lang('cm_257'),	// ā
			'260'   =>  lang('cm_260'),	// Ą
			'261'	=>  lang('cm_261'),	// ą
			'262'   =>  lang('cm_232'),	// Ć
			'263'	=>  lang('cm_263'),	// ć
			'268'	=>	lang('cm_268'), '269'	=>	lang('cm_269'),	// Č, č
			'270'	=>	lang('cm_207'), '271'	=>	lang('cm_271'),	// Ď, ď
			'272'	=>	lang('cm_207'), '273'	=>	lang('cm_271'),	// Đ, đ
			'274'	=>	lang('cm_274'), '275'	=>	lang('cm_275'),	// Ē, ē
			'276'	=>	lang('cm_276'), '277'	=>	lang('cm_277'),	// Ě, ě
			'280'   =>  lang('cm_280'), '281'	=> 	lang('cm_281'),	// Ę, ę
			'282'	=>	lang('cm_282'), '283'	=>	lang('cm_283'),	// Ě, ě
			'290'	=>	lang('cm_290'), '291'	=>	lang('cm_291'),	// Ģ, ģ
			'298'	=>	lang('cm_298'), '299'	=>	lang('cm_299'),	// Ī, ī
			'310'	=>	lang('cm_310'), '311'	=>	lang('cm_311'),	// Ķ, ķ
			'313'	=>	lang('cm_313'), '314'	=>	lang('cm_314'),	// Ĺ, ĺ
			'315'	=>	lang('cm_315'), '316'	=>	lang('cm_316'),	// Ļ, ļ
			'317'	=>	lang('cm_317'), '318'	=>	lang('cm_318'),	// Ľ, ľ
			'321'	=>	lang('cm_321'), '322'	=>	lang('cm_322'),	// Ł, ł
			'323'   =>  lang('cm_323'), '324'	=>  lang('cm_324'),	// Ń, ń
			'325'	=>	lang('cm_325'), '326'	=>	lang('cm_326'),	// Ņ, ņ
			'327'	=>	lang('cm_327'), '328'	=>	lang('cm_328'),	// Ň, ň
			'340'	=>	lang('cm_340'), '341'	=>	lang('cm_341'),	// Ŕ, ŕ
			'344'	=>	lang('cm_344'), '345'	=>	lang('cm_345'),	// Ř, ř
			'346'   =>  lang('cm_346'), '347'	=>  lang('cm_347'),	// Ś, ś
			'352'	=>	lang('cm_352'), '353'	=>	lang('cm_353'),	// Š, š
			'356'	=>	lang('cm_356'), '357'	=>	lang('cm_357'),	// Ť, ť
			'362'	=>	lang('cm_362'), '363'	=>	lang('cm_363'),	// Ū, ū
			'366'	=>	lang('cm_366'), '367'	=>	lang('cm_367'),	// Ů, ů
			'377'   =>  lang('cm_377'), '378'	=>  lang('cm_378'),	// Ź, ź
			'379'   =>  lang('cm_379'), '380'	=>  lang('cm_380'),	// Ż, ż
			'381'	=>	lang('cm_381'), '382'	=>	lang('cm_382'),	// Ž, ž
			
			// Azbuka characters
			'1072'	=>	lang('cm_1072'), // a
			'1073'	=>	lang('cm_1073'), // б
			'1074'	=>	lang('cm_1074'), // в
			'1075'	=>	lang('cm_1075'), // г
			'1076'	=>	lang('cm_1076'), // д
			'1077'	=>	lang('cm_1077'), // е
			'1105'	=>	lang('cm_1105'), // ё
			'1078'	=>	lang('cm_1078'), // ж
			'1079'	=>	lang('cm_1079'), // з
			'1080'	=>	lang('cm_1080'), // и
			'1081'	=>	lang('cm_1081'), // й
			'1082'	=>	lang('cm_1082'), // к
			'1083'	=>	lang('cm_1083'), // л
			'1084'	=>	lang('cm_1084'), // м
			'1085'	=>	lang('cm_1085'), // н
			'1086'	=>	lang('cm_1086'), // о
			'1087'	=>	lang('cm_1087'), // п
			'1088'	=>	lang('cm_1088'), // р
			'1089'	=>	lang('cm_1089'), // с
			'1090'	=>	lang('cm_1090'), // т
			'1091'	=>	lang('cm_1091'), // у
			'1092'	=>	lang('cm_1092'), // ф
			'1093'	=>	lang('cm_1093'), // х
			'1094'	=>	lang('cm_1094'), // ц
			'1095'	=>	lang('cm_1095'), // ч
			'1096'	=>	lang('cm_1096'), // ш
			'1097'	=>	lang('cm_1097'), // щ
			'1099'	=>	lang('cm_1099'), // ы
			'1101'	=>	lang('cm_1101'), // э
			'1102'	=>	lang('cm_1102'), // ю
			'1103'	=>	lang('cm_1103'), // я
			'1040'	=>	lang('cm_1040'), // А
			'1041'	=>	lang('cm_1041'), // Б
			'1042'	=>	lang('cm_1042'), // В
			'1043'	=>	lang('cm_1043'), // Г
			'1044'	=>	lang('cm_1044'), // Д
			'1045'	=>	lang('cm_1045'), // Е
			'1025'	=>	lang('cm_1025'), // Ё
			'1046'	=>	lang('cm_1046'), // Ж
			'1047'	=>	lang('cm_1047'), // З
			'1048'	=>	lang('cm_1048'), // И
			'1049'	=>	lang('cm_1049'), // Й
			'1050'	=>	lang('cm_1050'), // К
			'1051'	=>	lang('cm_1051'), // Л
			'1052'	=>	lang('cm_1052'), // М
			'1053'	=>	lang('cm_1053'), // Н
			'1054'	=>	lang('cm_1054'), // О
			'1055'	=>	lang('cm_1055'), // П
			'1056'	=>	lang('cm_1056'), // Р
			'1057'	=>	lang('cm_1057'), // С
			'1058'	=>	lang('cm_1058'), // Т
			'1059'	=>	lang('cm_1059'), // У
			'1060'	=>	lang('cm_1060'), // Ф
			'1061'	=>	lang('cm_1061'), // Х
			'1062'	=>	lang('cm_1062'), // Ц
			'1063'	=>	lang('cm_1063'), // Ч
			'1064'	=>	lang('cm_1064'), // Ш
			'1065'	=>	lang('cm_1065'), // Щ
			'1067'	=>	lang('cm_1067'), // Ы
			'1069'	=>  lang('cm_1069'), // Э
			'1070'	=>	lang('cm_1070'), // Ю
			'1071'	=>	lang('cm_1071'), // Я
			'336'	=>	lang('cm_336'),	// Ő
			'337'	=>	lang('cm_337'),	// ő
			'368'	=>	lang('cm_368'),	// Ű
			'369'	=>	lang('cm_369'),	// ű
				
			// Greek characters	
			'971'	=>	lang('cm_971'),	// ϋ
			'944'	=>	lang('cm_944'),	// ΰ
			'945'	=>	lang('cm_945'),	// α
			'946'	=>	lang('cm_946'),	// β
			'947'	=>	lang('cm_947'),	// γ
			'948'	=>	lang('cm_948'),	// δ
			'949'	=>	lang('cm_949'),	// ε
			'950'	=>	lang('cm_950'),	// ζ
			'951'	=>	lang('cm_951'),	// η
			'952'	=>	lang('cm_952'),	// θ
			'953'	=>	lang('cm_953'),	// ι
			'954'	=>	lang('cm_954'),	// κ
			'955'	=>	lang('cm_955'),	// λ
			'956'	=>	lang('cm_956'),	// μ
			'957'	=>	lang('cm_957'),	// ν
			'958'	=>	lang('cm_958'),	// ξ
			'959'	=>	lang('cm_959'),	// ο
			'960'	=>	lang('cm_960'),	// π
			'961'	=>	lang('cm_961'),	// ρ
			'963'	=>	lang('cm_963'),	// σ
			'964'	=>	lang('cm_964'),	// τ
			'965'	=>	lang('cm_965'),	// υ
			'966'	=>	lang('cm_966'),	// φ
			'967'	=>	lang('cm_967'),	// x
			'968'	=>	lang('cm_968'),	// ψ
			'969'	=>	lang('cm_969'),	// ω
			'940'	=>	lang('cm_940'),	// ά
			'941'	=>	lang('cm_941'),	// έ
			'942'	=>	lang('cm_942'),	// ή
			'943'	=>	lang('cm_943'),	// ί
			'972'	=>	lang('cm_972'),	// ό
			'973'	=>	lang('cm_973'),	// ύ
			'974'	=>	lang('cm_974'),	// ώ
			'938'	=>	lang('cm_938'),	// Ϊ
			'939'	=>	lang('cm_939'),	// Ϋ
			'901'	=>	lang('cm_901'),	// ΅
			'903'	=>	lang('cm_903'),	// ·
			'913'	=>	lang('cm_913'),	// Α
			'914'	=>	lang('cm_914'),	// Β
			'915'	=>	lang('cm_915'),	// Γ
			'916'	=>	lang('cm_916'),	// Δ
			'917'	=>	lang('cm_917'),	// Ε
			'918'	=>	lang('cm_918'),	// Ζ
			'919'	=>	lang('cm_919'),	// Η
			'920'	=>	lang('cm_920'),	// Θ
			'921'	=>	lang('cm_921'),	// Ι
			'922'	=>	lang('cm_922'),	// Κ
			'923'	=>	lang('cm_923'),	// Λ
			'924'	=>	lang('cm_924'),	// Μ
			'925'	=>	lang('cm_925'),	// Ν
			'926'	=>	lang('cm_926'),	// Ξ
			'927'	=>	lang('cm_927'),	// Ο
			'928'	=>	lang('cm_928'),	// Π
			'929'	=>	lang('cm_929'),	// Ρ
			'931'	=>	lang('cm_931'),	// Σ
			'932'	=>	lang('cm_932'),	// Τ
			'933'	=>	lang('cm_933'),	// Υ
			'934'	=>	lang('cm_934'),	// Φ
			'935'	=>	lang('cm_935'),	// Χ
			'936'	=>	lang('cm_936'),	// Ψ
			'937'	=>	lang('cm_937'),	// Ω
			'902'	=>	lang('cm_902'),	// Ά
			'904'	=>	lang('cm_904'),	// Έ
			'905'	=>	lang('cm_905'),	// Ή
			'906'	=>	lang('cm_906'),	// Ί
			'908'	=>	lang('cm_908'),	// Ό
			'910'	=>	lang('cm_910'),	// Ύ
			'911'	=>	lang('cm_911'),	// Ώ
			'962'	=>	lang('cm_962'),	// ς
			'970'	=>	lang('cm_970'),	// ϊ
			'912'	=>	lang('cm_912'),	// ΐ 

			'1569'	=>	lang('1569'),
			'1575'	=>	lang('1575'),
			'1570'	=>	lang('1570'),
			'1571'	=>	lang('1571'),
			'1573'	=>	lang('1573'),
			'1609'	=>	lang('1609'),
			'1576'	=>	lang('1576'),
			'1577'	=>	lang('1577'),
			'1578'	=>	lang('1578'),
			'1579'	=>	lang('1579'),
			'1580'	=>	lang('1580'),
			'1581'	=>	lang('1581'),
			'1582'	=>	lang('1582'),
			'1583'	=>	lang('1583'),
			'1584'	=>	lang('1584'),
			'1585'	=>	lang('1585'),
			'1586'	=>	lang('1586'),
			'1587'	=>	lang('1587'),
			'1588'	=>	lang('1588'),
			'1589'	=>	lang('1589'),
			'1590'	=>	lang('1590'),
			'1591'	=>	lang('1591'),
			'1592'	=>	lang('1592'),
			'1593'	=>	lang('1593'),
			'1594'	=>	lang('1594'),
			'1600'	=>	lang('1600'),
			'1601'	=>	lang('1601'),
			'1602'	=>	lang('1602'),
			'1603'	=>	lang('1603'),
			'1604'	=>	lang('1604'),
			'1605'	=>	lang('1605'),
			'1606'	=>	lang('1606'),
			'1607'	=>	lang('1607'),
			'1608'	=>	lang('1608'),
			'1572'	=>	lang('1572'),
			'1610'	=>	lang('1610'),
			'1574'	=>	lang('1574'),
			'1615'	=>	lang('1615'),
			'1611'	=>	lang('1611'),
			'1613'	=>	lang('1613'),
			'1612'	=>	lang('1612'),
			'1614'	=>	lang('1614'),
			'1616'	=>	lang('1616')
		);
		
		return $characters;
	}

	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see https://ellislab.com/codeigniter/user-guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	function activate_extension() 
	{
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'RTL_characters',
			'hook'		=> 'foreign_character_conversion_array',
			'settings'	=> '',
			'priority'	=> 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		// insert ext in database
//		$this->EE->db->insert('exp_extensions', $data);
		ee()->db->insert('extensions', $data);
	}

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return  mixed   void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version) {
			return FALSE;
		}

		if ($current < $this->version) {
			// Update to version 1.0
		}

		ee()->db->where('class', __CLASS__);
		ee()->db->update(
						'extensions',
						array('version' => $this->version)
		);
	}
	
	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension() {
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	} 
}
// END CLASS