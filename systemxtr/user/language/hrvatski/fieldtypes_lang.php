<?php

$lang = array(

'add_file'	=>
'Dodaj',

'remove_file' => 'Obriši',

'file_undo_remove' => 'Poništi brisanje',

'directory_no_access' => 'Nemate pristup mapi označenoj za ovo polje',

'directory' => 'Mapa:',

// Relationships
'rel_ft_channels'			=> 'Kanali za povezivanje',
'rel_ft_channels_desc'		=> 'Iz kojih se kanala može povezivati sadržaj.',
'rel_ft_include'			=> 'Uključi u izbor',
'rel_ft_include_desc'		=> 'Dopusti istekle ili buduće sadržaje.',
'rel_ft_include_expired'	=> 'Istekli sadržaji',
'rel_ft_include_future'		=> 'Budući sadržaji',
'rel_ft_categories'			=> 'Ograničenje po kategoriji',
'rel_ft_categories_desc'	=> 'Ograničavanje sadržaja po kategorijama.',
'rel_ft_authors'			=> 'Autori',
'rel_ft_authors_desc'		=> 'Ograničavanje sadržaja po autorima.',
'rel_ft_statuses'			=> 'Ograničenje po statusu',
'rel_ft_statuses_desc'		=> 'Ograničavanje sadržaja po statusima.',
'rel_ft_limit'				=> 'Maksimalni broj sadržaja',
'rel_ft_limit_desc'			=> 'Maksimalni broj sadržaja koji će se prikazati u polju za povezivanje sadržaja.<br><i>Ostavite prazno da biste dopustili sve sadržaje.</i>',
'rel_ft_order'				=> 'Poredaj po',
'rel_ft_order_desc'			=> 'Zadani poredak sadržaja u polju za povezivanje sadržaja.',
'rel_ft_order_title'		=> 'Naslovu',
'rel_ft_order_date'			=> 'Datumu objave',
'rel_ft_order_ascending'	=> 'Uzlazno (A-Z)',
'rel_ft_order_descending'	=> 'Silazno (Z-A)',
'rel_ft_allow_multi'		=> 'Dopusti više povezivanja sadržaja?',
'rel_ft_allow_multi_desc'	=> 'Kada je postavljeno na <b>da</b>, autorima će biti dopušteno stvaranje višestrukih odnosa.',
'any_channel' 				=> 'Bilo koji kanal',
'any_category' 				=> 'Bilo koja kategorija',
'any_author' 				=> 'Bilo koji autor',
'any_status' 				=> 'Bilo koji status',

// File
'file_ft_configure'				=> 'Opće opcije polja',
'file_ft_configure_subtext'		=> 'Dodatne postavke kako se polje za datoteke treba ponašati.',
'file_ft_content_type'			=> 'Dopuštene vrste datoteka',
'file_ft_content_type_desc'		=> 'Vrste datoteka koje se mogu prenijeti preko ovog polja.',
'file_ft_allowed_dirs'			=> 'Dopuštena mapa',
'file_ft_allowed_dirs_desc'		=> 'Zadana mapa za pohranjene datoteke preko ovog polja.',
'file_ft_show_files'			=> 'Prikaži postojeće datoteke?',
'file_ft_show_files_desc'		=> 'Kada je postavljeno na <b>da</b>, autorima će se prikazati padajući popis postojećih datoteka.',
'file_ft_limit'					=> 'Ograničenje postojećih datoteka',
'file_ft_limit_desc'			=> 'Maksimalan broj datoteka koji će se prikazati u padajućem izborniku.<br><i>Ostavite prazno za prikaz svih datoteka.</i>',
'file_ft_select_existing'		=> 'Odaberite postojeću datoteku',
'file_ft_cannot_find_file'		=> '<b>Datoteka nije pronađena.</b> Nismo mogli pronaći %s na poslužitelju.',
'file_ft_no_upload_directories' => 'Trenutačno nema raspoloživih mapa za prijenos. <a href="%s">Dodajte jednu ili više mapa za prijenos</a> da biste mogli upotrebljavali vrstu polja za prijenos datoteka.',

// Grid
'grid_min_rows'				=> 'Minimalno redova',
'grid_min_rows_desc'		=> 'Postavite minimalan broj redaka za grid polje',
'grid_max_rows'				=> 'Maksimalno redova',
'grid_max_rows_desc'		=> 'Postavite maksimalan broj redaka za grid polje',
'grid_fields'				=> 'Grid polja',
'grid_config_desc'			=> 'Koje podatke želite prikupiti?',
'grid_col_type'				=> 'Vrsta podataka?',
'grid_col_label'			=> 'Naslov',
'grid_col_name'				=> 'Kratki naziv',
'grid_col_instr'			=> 'Upute',
'grid_col_options'			=> 'Jesu li ti podaci',
'grid_col_width'			=> 'Širina stupca',
'grid_col_width_desc'		=> 'Postavite širinu ovog stupca u obrazac za objavljivanje.',
'grid_col_width_percent'	=> 'Postotak.',
'grid_in_this_field'		=> 'Da li je ovo polje',
'grid_in_this_field_desc'	=> 'Označi polje kao obavezno, ili pretraživo.',
'grid_show_fmt_btns'		=> 'Prikaz gumbova za formatiranje?',
'grid_output_format'		=> 'Izlazno formatiranje?',
'grid_text_direction'		=> 'Smjer teksta?',
'grid_limit_input'			=> 'Ograniči unos?',
'grid_date_localized'		=> 'Lokalizirano?',
'grid_chars_allowed'		=> 'Dopušteno znakova.',
'grid_order_by'				=> 'Poredaj po',
'grid_show'					=> 'Prikaži',
'grid_col_label_required'	=> 'Postoji jedan ili više stupaca bez naslova stupca.',
'grid_col_name_required'	=> 'Postoji jedan ili više stupaca bez kratkog naziva stupca.',
'grid_col_name_reserved'	=> 'Jedan ili više stupaca koriste naziv stupca rezerviran za drugu funkciju predloška.',
'grid_duplicate_col_label'	=> 'Naslovi polja stupca moraju biti jedinstveni.',
'grid_duplicate_col_name'	=> 'Kratki nazivi polja stupca moraju biti jedinstveni.',
'grid_numeric_percentage'	=> 'Širine stupaca moraju biti numeričke.',
'grid_invalid_column_name'	=> 'Kratki nazivi stupaca moraju sadržavati samo alfanumeričke znakove i bez razmaka.',
'grid_add_some_data'		=> 'Još niste dodali nijedan red. <a href="#" class="grid_link_add">Dodajte novi red?</a>',
'grid_validation_error'		=> 'Došlo je do problema s jednim ili više grid polja',
'grid_field_required'		=> 'Ovo polje je obavezno',
'grid_reorder_field'		=> 'promijeni redoslijed polja',
'grid_add_field'			=> 'dodaj novo polje',
'grid_copy_field'			=> 'kopiraj polje',
'grid_remove_field'			=> 'ukloni polje',

// URL
'url_ft_allowed_url_schemes'         => 'Dopuštene URL sheme',
'url_ft_url_scheme_placeholder'      => 'URL shema rezervirano mjesto',
'url_ft_url_scheme_placeholder_desc' => 'Prikazan kao tekst rezerviranog mjesta kada nijedan URL nije poslan.',
'url_ft_protocol_relative_url'       => 'Relativni URL protokola',
'url_ft_invalid_url'                 => 'Nevažeći URL',
'url_ft_invalid_url_scheme'          => 'Vaš URL mora početi s važećom shemom: %s',


// IGNORE
''=>'');

// EOF
