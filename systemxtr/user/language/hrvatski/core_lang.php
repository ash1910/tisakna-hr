<?php

$lang = array(

//----------------------------
// General word list
//----------------------------

'no' => 'Ne',

'yes' => 'Da',

'on' => 'uključeno',

'off' => 'isključeno',

'first' => 'Prvi',

'prev' => 'Prethodni',

'next' => 'Sljedeći',

'last' => 'Zadnji',

'enabled' => 'omogućen',

'disabled' => 'onemogućen',

'back' => 'Povratak',

'submit' => 'Pošalji',

'update' => 'Osvježi',

'thank_you' => 'Hvala Vam!',

'page' => 'Stranica',

'of' => 'od',

'by' => 'kod',

'at' => 'na',

'dot' => 'točka',

'and' => 'i',

'or' => 'ili',

'id' => 'ID',

'and_n_others' => 'i %d drugih...',

'encoded_email' => '(Morate imati omogućen JavaScript kako biste vidjeli ovu e-mail adresu)',

'search' => 'Tražilica',

'system_off_msg' => 'Ova stranica trenutno nije aktivna.',

'not_authorized' => 'Nije vam dopušteno izvesti ovu akciju',

'auto_redirection' => 'Bit ćete preusmjereni automatski za %x sekundi',

'click_if_no_redirect' => 'Kliknite ovdje ako niste automatski preusmjereni',

'return_to_previous' => 'Povratak na prethodnu stranicu',

'not_available' => 'Nije dostupno',

'setting' => 'Postavka',

'preference' => 'Odabir postavki',

'pag_first_link' => '&lsaquo; Prvi',

'pag_last_link' => 'Zadnji &rsaquo;',

'site_homepage' => 'Početna stranica',

//----------------------------
// Errors
//----------------------------

'error' => 'Greška',

'generic_fatal_error' => 'Došlo je do greške i ovaj URL se trenutačno ne može obraditi.',

'invalid_url' => 'URL koji ste upisali nije ispravan.',

'submission_error' => 'Obrazac koji ste poslali sadrži sljedeće greške',

'general_error' => 'Došlo je do sljedećih greški',

'invalid_action' => 'Akcija koju ste zatražili nije ispravna.',

'csrf_token_expired' => 'Ovaj obrazac je istekao. Osvježite i pokušajte ponovo.',

'current_password_required' => 'Potrebna je vaša trenutna lozinka.',

'current_password_incorrect' => 'Vaša trenutna lozinka nije pravilno unešena.',

'captcha_required' => 'Potrebno je upisati riječ prikazanu na slici',

'captcha_incorrect' => 'Niste ispravno upisali riječ sa slike',

'nonexistent_page' => 'Tražena stranica ne postoji',

'unable_to_load_field_type' => 'Nemoguće učitati traženi dokument o vrsti polja:  %s.<br />
Provjerite da li se dokument o vrsti polja nalazi u direktoriju /system/user/addons/',

'unwritable_cache_folder' => 'Vaš cache folder nema odgovarajuće dozvole.<br>
Za popravak: Postavite dopuštenja za cache folder (/system/user/cache/) na 777 (ili ekvivalent za vaš poslužitelj).',

'unwritable_config_file' => 'Vaša konfiguracijska datoteka nema odgovarajuće dozvole.<br>
Za popravak: Postavite dopuštenja za konfiguracijsku datoteku (/system/user/config/config.php) na 666 (ili ekvivalent za vaš poslužitelj).',

'redirect_xss_fail' => 'Veza na koju ste preusmjereni sadrži
potencijalno zlonamjeran ili opasan kod. Preporučujemo da pritisnete gumb za povratak
i pošaljete email na %s da prijavite vezu koja je generirala ovu poruku.',

'missing_mime_config' => 'Nije moguće uvesti vaš mime-type whitelist: datoteka %s ne postoji ili se ne može čitati.',

'version_mismatch' => 'Verzija (%s) vaše ExpressionEngine instalacije nije u skladu sa prijavljenom verzijom (%s). <a href="'.DOC_URL.'installation/update.html" rel="external">Ažurirajte ponovo ExpressionEngine instalaciju</a>.',

'theme_folder_wrong' => 'Put do Themes foldera nije točan. Idite na <a href="%s">Postavke za URL i Path</a> i provjerite <mark>Themes Path</mark> i <mark>Themes URL</mark>.',

'missing_encryption_key' => 'Nemate postavljenu vrijednost za <code>%s</code> u vašoj config.php datoteci. To može ostaviti vašu instalaciju otvorenu sigurnosnim ranjivostima. Vratite ključeve ili se <a href="%s">obratite podršci </a> za pomoć.',

'checksum_changed_warning' => 'Jedna ili više sistemskih datoteka su izmijenjene:',

'checksum_changed_accept' => 'Prihvati izmjene',

'checksum_email_subject' => 'Sistemska datoteka je izmijenjena na vašoj web-lokaciji.',

'checksum_email_message' => 'ExpressionEngine je otkrio izmjenu sistemske datoteke: {url}

To utječe na sljedeće datoteke:
{changed}

Ako ste napravili te izmjene, prihvatite izmjene na početnoj stranici upravljačke ploče. Ako niste izmijenili te datoteke, to može ukazivati na pokušaj hakiranja. Provjerite datoteke za sumnjive sadržaje (JavaScript ili iFrame) i kontaktirajte podršku za ExpressionEngine:
https://expressionengine.com/support',

'new_version_error' => 'Došlo je do neočekivane pogreške prilikom pokušaja preuzimanja trenutačnog broja verzije ExpressionEngine. Posjetite vaš <a href="%s" title="korisnički račun za preuzimanje" rel="external">korisnički račun za preuzimanje</a> da biste potvrdili da ste na trenutačnoj verziji. Ako se ta pogreška ponavlja, obratite se administratoru sustava',

'file_not_found' => 'Datoteka %s ne postoji.',

//----------------------------
// Member Groups
//----------------------------

'banned' => 'Blokirani',

'guests' => 'Gosti',

'members' => 'Korisnici',

'pending' => 'Na čekanju',

'super_admins' => 'Super admini',


//----------------------------
// Template.php
//----------------------------

'error_tag_syntax' => 'Sljedeći tag sadrži sintaksnu grešku:',

'error_fix_syntax' => 'Ispravite sintaksu u vašem predlošku.',

'error_tag_module_processing' => 'Sljedeći tag je nemoguće obraditi:',

'error_fix_module_processing' => 'Molimo provjerite da li je modul \'%x\' instaliran i da li je \'%y\' moguć u modulu',

'template_loop' => 'Prouzročili ste petlju u predlošku sa neispravno smještenim pod-predlošcima (\'%s\' ponovno pozvanima)',

'template_load_order' => 'Poredak učitavanja predloška',

'error_multiple_layouts' => 'Pronađeno je više obrazaca za objavu, provjerite imate li samo jednu oznaku za obrazac za objavu po predlošku',

'error_layout_too_late' => 'Plugin ili modul oznaka pronađena prije deklaracije obrasca za objavu. Premjestite oznaku obrasca za objavu na vrh predloška.',

'error_invalid_conditional' => 'Imate nevažeći uvjet u predlošku. Pregledajte svoje uvjete za nezatvorene tagove, nevaljane operatore i da li negdje nedostaje }, ili {/if}.',

'layout_contents_reserved' => 'Naziv "contents" rezerviran je za podatke predloška i ne može se koristiti kao varijabla obrasca za objavu (npr. {layout:set name="contents"} ili {layout="foo/bar" contents=""}).',

//----------------------------
// Email
//----------------------------

'forgotten_email_sent' => 'Upute za ponovo postavljanje vaše lozinke upravo su vam poslane na vašu email adresu.',

'error_sending_email' => 'Trenutno je nemoguće poslati email.',

'no_email_found' => 'Email adresa koju ste poslali nije pronađena u bazi podataka.',

'password_reset_flood_lock' => 'Pokušali ste ponovo postaviti svoju lozinku previše puta danas. Provjerite mape pristigle pošte i neželjene pošte za prethodne zahtjeve ili se obratite administratoru .',

'your_new_login_info' => 'Podaci za ulaz',

'password_has_been_reset' => 'Vaša lozinka je promijenjena, a nova poslana na email.',

//----------------------------
// Date
//----------------------------

'singular' => 'jedan',

'less_than' => 'manje od',

'about' => 'o',

'past' => '%s prije',

'future' => 'za %s',

'ago' => '%x prije',

'year' => 'godina',

'years' => 'godine',

'month' => 'mjesec',

'months' => 'mjeseci',

'fortnight' => 'dve nedjelje',

'fortnights' => 'dve nedjelje',

'week' => 'tjedan',

'weeks' => 'tjedana',

'day' => 'dan',

'days' => 'dana',

'hour' => 'sat',

'hours' => 'sati',

'minute' => 'minuta',

'minutes' => 'minuta',

'second' => 'sekunda',

'seconds' => 'sekundi',

'am' => 'pr.p',

'pm' => 'po.p',

'AM' => 'PR.P',

'PM' => 'PO.P',

'Sun' => 'Ned',

'Mon' => 'Pon',

'Tue' => 'Uto',

'Wed' => 'Sri',

'Thu' => 'Čet',

'Fri' => 'Pet',

'Sat' => 'Sub',

'Su' => 'N',

'Mo' => 'P',

'Tu' => 'U',

'We' => 'S',

'Th' => 'Č',

'Fr' => 'P',

'Sa' => 'S',

'Sunday' => 'Nedelja',

'Monday' => 'Ponedjeljak',

'Tuesday' => 'Utorak',

'Wednesday' => 'Srijeda',

'Thursday' => 'Četvrtak',

'Friday' => 'Petak',

'Saturday' => 'Subota',


'Jan' => 'Sij',

'Feb' => 'Velj',

'Mar' => 'Ožu',

'Apr' => 'Tra',

'May' => 'Svi',

'Jun' => 'Lip',

'Jul' => 'Srp',

'Aug' => 'Kol',

'Sep' => 'Ruj',

'Oct' => 'Lis',

'Nov' => 'Stu',

'Dec' => 'Pro',

'January' => 'Siječanj',

'February' => 'Veljača',

'March' => 'Ožujak',

'April' => 'Travanj',

'May_l' => 'Svibanj',

'June' => 'Lipanj',

'July' => 'Srpanj',

'August' => 'Kolovoz',

'September' => 'Rujan',

'October' => 'Listopad',

'November' => 'Studeni',

'December' => 'Prosinac',


'UM12'		=>	'(UTC -12:00) Baker/Howland Island',
'UM11'		=>	'(UTC -11:00) Niue',
'UM10'		=>	'(UTC -10:00) Hawaii-Aleutian Standard Time, Cook Islands, Tahiti',
'UM95'		=>	'(UTC -9:30) Marquesas Islands',
'UM9'		=>	'(UTC -9:00) Alaska Standard Time, Gambier Islands',
'UM8'		=>	'(UTC -8:00) Pacific Standard Time, Clipperton Island',
'UM7'		=>	'(UTC -7:00) Mountain Standard Time',
'UM6'		=>	'(UTC -6:00) Central Standard Time',
'UM5'		=>	'(UTC -5:00) Eastern Standard Time, Western Caribbean Standard Time',
'UM45'		=>	'(UTC -4:30) Venezuelan Standard Time',
'UM4'		=>	'(UTC -4:00) Atlantic Standard Time, Eastern Caribbean Standard Time',
'UM35'		=>	'(UTC -3:30) Newfoundland Standard Time',
'UM3'		=>	'(UTC -3:00) Argentina, Brazil, French Guiana, Uruguay',
'UM2'		=>	'(UTC -2:00) South Georgia/South Sandwich Islands',
'UM1'		=>	'(UTC -1:00) Azores, Cape Verde Islands',
'UTC'		=>	'(UTC) Greenwich Mean Time, Western European Time',
'UP1'		=>	'(UTC +1:00) Central European Time, West Africa Time',
'UP2'		=>	'(UTC +2:00) Central Africa Time, Eastern European Time, Kaliningrad Time',
'UP3'		=>	'(UTC +3:00) East Africa Time, Arabia Standard Time',
'UP35'		=>	'(UTC +3:30) Iran Standard Time',
'UP4'		=>	'(UTC +4:00) Moscow Time, Azerbaijan Standard Time',
'UP45'		=>	'(UTC +4:30) Afghanistan',
'UP5'		=>	'(UTC +5:00) Pakistan Standard Time, Yekaterinburg Time',
'UP55'		=>	'(UTC +5:30) Indian Standard Time, Sri Lanka Time',
'UP575'		=>	'(UTC +5:45) Nepal Time',
'UP6'		=>	'(UTC +6:00) Bangladesh Standard Time, Bhutan Time, Omsk Time',
'UP65'		=>	'(UTC +6:30) Cocos Islands, Myanmar',
'UP7'		=>	'(UTC +7:00) Krasnoyarsk Time, Cambodia, Laos, Thailand, Vietnam',
'UP8'		=>	'(UTC +8:00) Australian Western Standard Time, Beijing Time, Irkutsk Time',
'UP875'		=>	'(UTC +8:45) Australian Central Western Standard Time',
'UP9'		=>	'(UTC +9:00) Japan Standard Time, Korea Standard Time, Yakutsk Time',
'UP95'		=>	'(UTC +9:30) Australian Central Standard Time',
'UP10'		=>	'(UTC +10:00) Australian Eastern Standard Time, Vladivostok Time',
'UP105'		=>	'(UTC +10:30) Lord Howe Island',
'UP11'		=>	'(UTC +11:00) Magadan Time, Solomon Islands, Vanuatu',
'UP115'		=>	'(UTC +11:30) Norfolk Island',
'UP12'		=>	'(UTC +12:00) Fiji, Gilbert Islands, Kamchatka Time, New Zealand Standard Time',
'UP1275'	=>	'(UTC +12:45) Chatham Islands Standard Time',
'UP13'		=>	'(UTC +13:00) Samoa Time Zone, Phoenix Islands Time, Tonga',
'UP14'		=>	'(UTC +14:00) Line Islands',

"select_timezone" => "Odaberite vremensku zonu",

"no_timezones" => "Nema vremenskih zona",

'invalid_timezone' => "Vremenska zona koju ste poslali nije važeća.",

'invalid_date_format' => "Format datuma koji ste poslali nije važeći.",

'curl_not_installed' => 'cURL nije instaliran na vašem poslužitelju',

// IGNORE
''=>'');

// EOF
