<?php

//---------------------------------------------------
//	Admin Notification of New Entry
//--------------------------------------------------

if ( ! function_exists('admin_notify_entry_title'))
{
	function admin_notify_entry_title()
	{
return <<<EOF
Objavljen je novi sadržaj u kanalu
EOF;
	}
}

if ( ! function_exists('admin_notify_entry'))
{
	function admin_notify_entry()
	{
return <<<EOF
Novi sadržaj je objavljen u sljedećem kanalu:
{channel_name}

Naslov sadržaja:
{entry_title}

Objavio: {name}
Email: {email}

Za čitanje sadržaja molimo posjetite: 
{entry_url}

EOF;
	}
}



//---------------------------------------------------
//	Admin Notification of New Member Registrations
//--------------------------------------------------

if ( ! function_exists('admin_notify_reg_title'))
{
	function admin_notify_reg_title()
	{
return <<<EOF
Obavijest o novoj registraciji korisnika
EOF;
	}
}

if ( ! function_exists('admin_notify_reg'))
{
	function admin_notify_reg()
	{
return <<<EOF
Web stranica: {site_name}

Screen name: {name}
User name: {username}
Email: {email}

URL do CMS sistema: {control_panel_url}
EOF;
	}
}



//---------------------------------------------------
//	Admin Notification of New Comment
//--------------------------------------------------

if ( ! function_exists('admin_notify_comment_title'))
{
	function admin_notify_comment_title()
	{
return <<<EOF
Upravo ste dobili komentar
EOF;
	}
}

if ( ! function_exists('admin_notify_comment'))
{
	function admin_notify_comment()
	{
return <<<EOF
Upravo ste dobili komentar za sljedeći kanal:
{channel_name}

Naslov sadržaja:
{entry_title}

Lokacija:
{comment_url}

Objavio: {name}
Email: {email}
URL: {url}
Lokacija: {location}

{comment}
EOF;
	}
}



//---------------------------------------------------
//	Membership Activation Instructions
//--------------------------------------------------

if ( ! function_exists('mbr_activation_instructions_title'))
{
	function mbr_activation_instructions_title()
	{
return <<<EOF
U prilogu je vaš aktivacijski kod
EOF;
	}
}

if ( ! function_exists('mbr_activation_instructions'))
{
	function mbr_activation_instructions()
	{
return <<<EOF
Zahvaljujemo na Vašoj registraciji.

Da biste aktivirali novi račun, molimo posjetite sljedeći URL:

{unwrap}{activation_url}{/unwrap}

Hvala Vam!

{site_name}

{site_url}
EOF;
	}
}



//---------------------------------------------------
//	Member Forgotten Password Instructions
//--------------------------------------------------

if ( ! function_exists('forgot_password_instructions_title'))
{
	function forgot_password_instructions_title()
	{
return <<<EOF
Podaci za prijavu
EOF;
	}
}

if ( ! function_exists('forgot_password_instructions'))
{
	function forgot_password_instructions()
	{
return <<<EOF
{name},

Da biste ponovo postavili lozinku, molimo Vas da posjetite sljedeću stranicu:

{reset_url}

Zatim se prijavite sa vašim korisničkim imenom: {username}

Ako ne želite resetirati lozinku, zanemarite ovu poruku. Poruka će isteći u roku od 24 sata.

{site_name}
{site_url}
EOF;
	}
}


//---------------------------------------------------
//	Validated Member Notification
//--------------------------------------------------

if ( ! function_exists('validated_member_notify_title'))
{
	function validated_member_notify_title()
	{
return <<<EOF
Vaš korisnički račun je aktiviran
EOF;
	}
}

if ( ! function_exists('validated_member_notify'))
{
	function validated_member_notify()
	{
return <<<EOF
{name},

Vaš korisnički račun je aktiviran i spreman za uporabu.

Hvala Vam!

{site_name}
{site_url}
EOF;
	}
}



//---------------------------------------------------
//	Decline Member Validation
//--------------------------------------------------

if ( ! function_exists('decline_member_validation_title'))
{
	function decline_member_validation_title()
	{
return <<<EOF
Vaš korisnička prijava je odbijena.
EOF;
	}
}

if ( ! function_exists('decline_member_validation'))
{
	function decline_member_validation()
	{
return <<<EOF
{name},

Žao nam je, ali naši su djelatnici odlučili ne potvrditi Vašu prijavu.

{site_name}
{site_url}
EOF;
	}
}


//---------------------------------------------------
//	Comment Notification
//--------------------------------------------------

if ( ! function_exists('comment_notification_title'))
{
	function comment_notification_title()
	{
return <<<EOF
Netko je upravo odgovorio na vaš komentar
EOF;
	}
}

if ( ! function_exists('comment_notification'))
{
	function comment_notification()
	{
return <<<EOF
{name_of_commenter} je upravo odgovorio na sadržaj za koji ste se prijavili u:
{channel_name}

Naslov sadržaja je:
{entry_title}

Komentar možete vidjeti na sljedećoj stranici:
{comment_url}

{comment}

Da biste prestali primati obavijesti za ovaj komentar, kliknite ovdje:
{notification_removal_url}
EOF;
	}
}

//---------------------------------------------------
//	Comments Opened Notification
//--------------------------------------------------

if ( ! function_exists('comments_opened_notification_title'))
{
	function comments_opened_notification_title()
	{
return <<<EOF
Dodani su novi komentari
EOF;
	}
}

if ( ! function_exists('comments_opened_notification'))
{
	function comments_opened_notification()
	{
return <<<EOF
Dodani su odgovori za sadržaj na koji ste se prijavili u:
{channel_name}

Naslov sadržaja je:
{entry_title}

Komentare možete vidjeti na sljedećoj stranici:
{comment_url}

{comments}
{comment}
{/comments}

Da biste prestali primati obavijesti za ovaj tekst, kliknite ovdje:
{notification_removal_url}
EOF;
	}
}

//---------------------------------------------------
//	Admin Notification of New Forum Post
//--------------------------------------------------

if ( ! function_exists('admin_notify_forum_post_title'))
{
	function admin_notify_forum_post_title()
	{
return <<<EOF
Netko je upravo objavio post na {forum_name}
EOF;
	}
}

if ( ! function_exists('admin_notify_forum_post'))
{
	function admin_notify_forum_post()
	{
return <<<EOF
{name_of_poster} je upravo objavio novi post u {forum_name}

Naslov je:
{title}

Post možete pronaći na:
{post_url}

{body}
EOF;
	}
}



//---------------------------------------------------
//	Forum Post User Notification
//--------------------------------------------------

if ( ! function_exists('forum_post_notification_title'))
{
	function forum_post_notification_title()
	{
return <<<EOF
Netko je upravo objavio post na {forum_name}
EOF;
	}
}

if ( ! function_exists('forum_post_notification'))
{
	function forum_post_notification()
	{
return <<<EOF
Netko je upravo objavio post na temu za koju ste prijavljeni:
{forum_name}

Naslov je:
{title}

Post možete pronaći na:
{post_url}

{body}

Da biste prestali primati obavijesti za ovaj komentar, kliknite ovdje:
{notification_removal_url}
EOF;
	}
}



//---------------------------------------------------
//	Private Message Notification
//--------------------------------------------------

if ( ! function_exists('private_message_notification_title'))
{
	function private_message_notification_title()
	{
return <<<EOF
Netko vam je poslao privatnu poruku
EOF;
	}
}

if ( ! function_exists('private_message_notification'))
{
	function private_message_notification()
	{
return <<<EOF

{recipient_name},

{sender_name} Vam je upravo poslao privatnu poruku pod nazivom ‘{message_subject}’.

Poruku možete vidjeti ako se prijavite i pogledate u mapu pristigle pošte na:
{site_url}

Sadržaj:

{message_content}

Da biste prestali primati obavijesti o primljenim Porukama, tu mogućnost možete isključiti u postavkama emaila.

{site_name}
{site_url}
EOF;
	}
}



/* -------------------------------------
/*  Notification of Full PM Inbox
/* -------------------------------------*/
if ( ! function_exists('pm_inbox_full_title'))
{
	function pm_inbox_full_title()
	{
return <<<EOF
Vaš spremnik poruka je pun
EOF;
	}
}

if ( ! function_exists('pm_inbox_full'))
{
	function pm_inbox_full()
	{
return <<<EOF
{recipient_name},

{sender_name} Vam je upravo pokušao poslati poruku,
ali vaš spremnik poruka je pun i prelazi maksimum od {pm_storage_limit}.

Molimo, prijavite se i uklonite neželjene poruke iz vaše mape pristiglih poruka na:
{site_url}
EOF;
	}
}



/* -------------------------------------
/*  Notification of Forum Topic Moderation
/* -------------------------------------*/
if ( ! function_exists('forum_moderation_notification_title'))
{
	function forum_moderation_notification_title()
	{
return <<<EOF
Obavijesti moderatora na {forum_name}
EOF;
	}
}

if ( ! function_exists('forum_moderation_notification'))
{
	function forum_moderation_notification()
	{
return <<<EOF
{name_of_recipient}, moderator je {moderation_action} vašu temu.

Naslov teme je:
{title}

Temu možete pronaći na:
{thread_url}
EOF;
	}
}



/* -------------------------------------
/*  Notification of Forum Post Report
/* -------------------------------------*/
if ( ! function_exists('forum_report_notification_title'))
{
	function forum_report_notification_title()
	{
return <<<EOF
Post je prijavljen na {forum_name}
EOF;
	}
}

if ( ! function_exists('forum_report_notification'))
{
	function forum_report_notification()
	{
return <<<EOF
{reporter_name} je prijavio post od {author} u:
{forum_name}

Razlog za prijavu:
{reasons}

Dodatne bilješke od {reporter_name}:
{notes}

Post možete pronaći na:
{post_url}

Sadržaj objavljenog posta:
{body}
EOF;
	}
}



/* -------------------------------------
//  OFFLINE SYSTEM PAGE
/* -------------------------------------*/
if ( ! function_exists('offline_template'))
{
	function offline_template()
	{
return <<<EOF
<html>
<head>

<title>Stranica nije dostupna</title>

<style type="text/css">

body {
background-color:	#ffffff;
margin:				50px;
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-size:			11px;
color:				#000;
background-color:	#fff;
}

a {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-weight:		bold;
letter-spacing:		.09em;
text-decoration:	none;
color:			  #330099;
background-color:	transparent;
}

a:visited {
color:				#330099;
background-color:	transparent;
}

a:hover {
color:				#000;
text-decoration:	underline;
background-color:	transparent;
}

#content  {
border:				#999999 1px solid;
padding:			22px 25px 14px 25px;
}

h1 {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-weight:		bold;
font-size:			14px;
color:				#000;
margin-top: 		0;
margin-bottom:		14px;
}

p {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-size: 			12px;
font-weight: 		normal;
margin-top: 		12px;
margin-bottom: 		14px;
color: 				#000;
}
</style>

</head>

<body>

<div id="content">

<h1>Stranica nije dostupna</h1>

<p>Ova stranica trenutno nije dostupna.</p>

</div>

</body>

</html>
EOF;
	}
}



/* -------------------------------------
//  User Messages Template
/* -------------------------------------*/
if ( ! function_exists('message_template'))
{
	function message_template()
	{
return <<<EOF
<html>
<head>

<title>{title}</title>

<meta http-equiv='content-type' content='text/html; charset={charset}' />

{meta_refresh}

<style type="text/css">

body {
background-color:	#ffffff;
margin:				50px;
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-size:			11px;
color:				#000;
background-color:	#fff;
}

a {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
letter-spacing:		.09em;
text-decoration:	none;
color:			  #330099;
background-color:	transparent;
}

a:visited {
color:				#330099;
background-color:	transparent;
}

a:active {
color:				#ccc;
background-color:	transparent;
}

a:hover {
color:				#000;
text-decoration:	underline;
background-color:	transparent;
}

#content  {
border:				#000 1px solid;
background-color: 	#DEDFE3;
padding:			22px 25px 14px 25px;
}

h1 {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-weight:		bold;
font-size:			14px;
color:				#000;
margin-top: 		0;
margin-bottom:		14px;
}

p {
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-size: 			12px;
font-weight: 		normal;
margin-top: 		12px;
margin-bottom: 		14px;
color: 				#000;
}

ul {
margin-bottom: 		16px;
}

li {
list-style:			square;
font-family:		Verdana, Arial, Tahoma, Trebuchet MS, Sans-serif;
font-size: 			12px;
font-weight: 		normal;
margin-top: 		8px;
margin-bottom: 		8px;
color: 				#000;
}

</style>

</head>

<body>

<div id="content">

<h1>{heading}</h1>

{content}

<p>{link}</p>

</div>

</body>

</html>
EOF;
	}
}

// EOF
