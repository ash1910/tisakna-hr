<?php

$lang['db_invalid_connection_str'] = 'Nije moguće odrediti postavke baze podataka na temelju veze koju ste poslali.';
$lang['db_unable_to_connect'] = 'Nije moguće povezati se s poslužiteljem baze podataka pomoću navedenih postavki.';
$lang['db_unable_to_select'] = 'Nije moguće odabrati navedenu bazu podataka: %s';
$lang['db_unable_to_create'] = 'Nije moguće kreirati navedenu bazu podataka: %s';
$lang['db_invalid_query'] = 'Upit koji ste poslali nije važeći.';
$lang['db_must_set_table'] = 'Morate postaviti tablicu baze podataka koja će se koristiti s vašim upitom.';
$lang['db_must_use_set'] = 'Morate koristiti "set" metodu za ažuriranje unosa.';
$lang['db_must_use_index'] = 'Morate navesti indeks koji će odgovarati za batch ažuriranja.';
$lang['db_batch_missing_index'] = 'Jedan ili više redaka poslanih za batch ažuriranje nema navedeni indeks.';
$lang['db_must_use_where'] = 'Ažuriranja nisu dopuštena osim ako sadrže "where" klauzulu.';
$lang['db_del_must_use_where'] = 'Brisanja nisu dopuštena osim ako sadrže "where" ili "like" klauzulu.';
$lang['db_field_param_missing'] = 'Dohvaćanje polja zahtijeva naziv tablice kao parametar.';
$lang['db_unsupported_function'] = 'Ova značajka nije dostupna za bazu podataka koju koristite.';
$lang['db_transaction_failure'] = 'Pogreška transakcije: izvršeno je vraćanje.';
$lang['db_unable_to_drop'] = 'Nije moguće napraviti drop navedene baze podataka.';
$lang['db_unsuported_feature'] = 'Nepodržana značajka platforme baze podataka koju koristite.';
$lang['db_unsuported_compression'] = 'Vaš poslužitelj ne podržava odabrani format datoteke za komprimiranje.';
$lang['db_filepath_error'] = 'Nije moguće zapisati podatke na putanje datoteke koju ste poslali.';
$lang['db_invalid_cache_path'] = 'Cache folder koji ste poslali nije valjan ili u njega nije moguće pisati.';
$lang['db_table_name_required'] = 'Naziv tablice je potreban za tu operaciju.';
$lang['db_column_name_required'] = 'Za tu je operaciju potreban naziv stupca.';
$lang['db_column_definition_required'] = 'Za tu je operaciju potrebna definicija stupca.';
$lang['db_unable_to_set_charset'] = 'Nije moguće postaviti client connection character set: %s';
$lang['db_error_heading'] = 'Došlo je do pogreške baze podataka';

// EOF
