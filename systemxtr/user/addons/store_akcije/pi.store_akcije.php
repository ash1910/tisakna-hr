<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


use Store\Model\Product;
use Store\Model\Sale;
use Store\Model\ProductModifier;


class Store_akcije
{
    public $return_data;

    public function __construct()
    {
        ee();

        ee()->load->helper('string');

        ee()->lang->loadfile('store_akcije');
    }



    function akcije()
    {

        //get field ID
        $cijene_id = ee()->TMPL->fetch_param('cijene');
        $cijene_id = "field_id_".$cijene_id;
        $najniza_id = ee()->TMPL->fetch_param('najniza');
        $najniza_id = "field_id_".$najniza_id;
        $akcijska_id = ee()->TMPL->fetch_param('akcijska');
        $akcijska_id = "field_id_".$akcijska_id;
        $channel_id = ee()->TMPL->fetch_param('channelid');
        $channel_ids = explode("|",$channel_id);
        $channel_ids = join("','",$channel_ids);

        $akcija_field = "";
        $regular_price = "";
        $akcija_field_array = array();
        
        
        //loop trough all entries
        $akcija_field_query = "SELECT c.$cijene_id as $cijene_id, c.$najniza_id as $najniza_id, c.$akcijska_id as $akcijska_id, c.entry_id, p.price, t.status
        FROM exp_channel_data c
        LEFT JOIN exp_channel_titles t on c.entry_id = t.entry_id
        LEFT JOIN exp_store_products p on c.entry_id = p.entry_id
        WHERE c.channel_id IN ('$channel_ids') ORDER BY entry_id ASC LIMIT 1000000";
        $akcija_field_query1 = ee()->db->query($akcija_field_query);
            if ($akcija_field_query1->num_rows() > 0) {
                foreach ($akcija_field_query1->result() as $row) {
$regular_price = "";
$sale_price = "";

                    $entry_id = $row->entry_id;
                    $status = $row->status;
                    //get value from custom field for each entry as string
                    $akcija_field = $row->$cijene_id;
                    $akcijska_cijena = $row->$akcijska_id;
                    if ($akcija_field != "") {
                        $akcija_field_array = explode("|",$akcija_field);               
                    } else  {
                        $akcija_field_array = array();
                    }
                            //get current price
                            $regular_price = $row->price;
                            $sale_price = $regular_price;
//get lowest price, if no elements in array, put product price (obično kada tek kreće plugin, ili se proizvod prvi dan stavlja na akciju (iako to ne bi smjeli po zakonu))
                            $lowest_price="";
                            if ($akcija_field == "") {
                                $lowest_price = $regular_price;
                            } else {
                                $lowest_price = min($akcija_field_array);
                            }
                            //checking only status open product for sale
                            if ($regular_price != "" AND $status != "closed") {
                                $product = Product::with(array(
                                    'modifiers' => function($query) { $query->orderBy('mod_order'); },
                                    'modifiers.options' => function($query) { $query->orderBy('opt_order'); },
                                    'stock',
                                ))->whereNotNull('price')
                                ->find($entry_id);
                                    //sometimes product does not exist (import flaw), check if reglar price exists before this
                                    $product = ee()->store->products->apply_sales($product);
                                    $sale_price = $product->sale_price;
                            }

                            if($sale_price != $regular_price){
                                array_push($akcija_field_array,$sale_price);
                                    if ($akcijska_cijena == "") {
                                    ee()->db->query("UPDATE exp_channel_data SET $akcijska_id='$sale_price', $najniza_id='$lowest_price' WHERE entry_id = '$entry_id'");
                                } elseif ($sale_price != $akcijska_cijena) {
                                    ee()->db->query("UPDATE exp_channel_data SET $akcijska_id='$sale_price', $najniza_id='$lowest_price' WHERE entry_id = '$entry_id'");
                                }
                            } else {
                                array_push($akcija_field_array,$regular_price);
                                ee()->db->query("UPDATE exp_channel_data SET $akcijska_id='', $najniza_id='' WHERE entry_id = '$entry_id'");
                            }

                    //shorten array to 30, display last 30
                    if (count($akcija_field_array) > 30) {
                        $akcija_field_array = array_slice($akcija_field_array, -30, 30); 
                    }
                    //write array as string in field
                    //echo ("<pre>".$entry_id);print_r($akcija_field_array);
                    $akcija_field = implode("|",$akcija_field_array);
                    ee()->db->query("UPDATE exp_channel_data SET $cijene_id='$akcija_field' WHERE entry_id = '$entry_id'");
                }
            }
    }

    function akcije_euro()
    {

        //get field ID
        $cijene_id = ee()->TMPL->fetch_param('cijene');
        $cijene_id = "field_id_".$cijene_id;
        $najniza_id = ee()->TMPL->fetch_param('najniza');
        $najniza_id = "field_id_".$najniza_id;
        $akcijska_id = ee()->TMPL->fetch_param('akcijska');
        $akcijska_id = "field_id_".$akcijska_id;
        $channel_id = ee()->TMPL->fetch_param('channelid');
        $channel_ids = explode("|",$channel_id);
        $channel_ids = join("','",$channel_ids);

        $akcija_field = "";
        $regular_price = "";
        $akcija_field_array = array();
        
        
        //loop trough all entries
        $akcija_field_query = "SELECT c.$cijene_id as $cijene_id, c.$najniza_id as $najniza_id, c.$akcijska_id as $akcijska_id, c.entry_id
        FROM exp_channel_data c
        LEFT JOIN exp_channel_titles t on c.entry_id = t.entry_id
        WHERE c.channel_id IN ('$channel_ids') ORDER BY entry_id ASC LIMIT 1000000";
        $akcija_field_query1 = ee()->db->query($akcija_field_query);
            if ($akcija_field_query1->num_rows() > 0) {
                foreach ($akcija_field_query1->result() as $row) {
                    $entry_id = $row->entry_id;
                    $akcija_field = $row->$cijene_id;
                    $akcija_field_array_eur = array();
                    if ($akcija_field != "") {
                        $akcija_field_array = explode("|",$akcija_field);
                        foreach ($akcija_field_array as $value) {
                            $akcija_field_array_eur[] = round($value / 7.53450, 2);
                        }
                        $akcija_field = implode("|",$akcija_field_array_eur);
                        ee()->db->query("UPDATE exp_channel_data SET $cijene_id='$akcija_field' WHERE entry_id = '$entry_id'");
                    }
                    $akcijska_cijena = $row->$akcijska_id;
                    if ($akcijska_cijena != "") {
                        $akcijska_cijena = round($akcijska_cijena / 7.53450, 2);
                        ee()->db->query("UPDATE exp_channel_data SET $akcijska_id='$akcijska_cijena' WHERE entry_id = '$entry_id'");
                    }
                    $najniza_cijena = $row->$najniza_id;
                    if ($najniza_cijena != "") {
                        $najniza_cijena = round($najniza_cijena / 7.53450, 2);
                        ee()->db->query("UPDATE exp_channel_data SET $najniza_id='$najniza_cijena' WHERE entry_id = '$entry_id'");
                    }
                }
            }
    }
}

/* End of file pi.store_akcije.php */