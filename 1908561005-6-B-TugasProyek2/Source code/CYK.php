<?php
function getfile($path){
    $file = fopen($path,"r");
    $container = [];
    while ( $line = fgets($file) ){
        $container[] =  trim(strtolower($line));
    }
    fclose($file);
    return array_unique($container);
}

function getrules($path){
    $clean_rules = [];
    $file = fopen($path,"r");
    $leksikon = ['BdLeksikon', 'SfLeksikon', 'BilLeksikon', 'GtLeksikon', 'KjLeksikon', 'PnLeksikon', 'PsLeksikon'];
    // explode tanda panah
    while( $rule = fgets($file) ){
        $new_rule = explode("->", $rule);
        $nonTerminal = trim($new_rule[0]); // bersihin spasi
        $rhs = trim($new_rule[1]); // bersihin spasi

        // jika rhs adalah leksikon
        if( in_array($rhs, $leksikon) ){
            $rhs = getfile("./rule/" . $rhs . ".txt");
            $clean_rules[$nonTerminal] =  $rhs;
        }else{
            $clean_rules[$nonTerminal][] =  $rhs;
        }
    }
    return $clean_rules;
}

function bagianKata($rules, $value){
    $arr = [];
    foreach($rules as $nonTerminal => $rhs){
        if( in_array($value, $rhs) ){
            $arr[] = $nonTerminal;
        }
    }
    return $arr;
}

function gabung($left, $right){
    // ubah ke array
    $left = explode(" ", $left);
    $right = explode(" ", $right);

    // kombinasi nested loop
    $new = [];
    foreach( $left as $l ){
        foreach( $right as $r ){
            $new[] = $l . " " . $r;
        }
    }

    return $new;
}

function get_combinations($arrays) {
	$result = array(array());
	foreach ($arrays as $property => $property_values) {
		$tmp = array();
		foreach ($result as $result_item) {
			foreach ($property_values as $property_value) {
				$tmp[] = array_merge($result_item, array($property => $property_value));
			}
		}
		$result = $tmp;
	}
	return $result;
}


$tabel = [];
$kalimat;
$kata;
$jmlKata;
$valid;
$variabels;
$terminals;
$productions;
$startSymbol;

function CFG($product, $symbol, $variabel = " ", $terminal = " "){
    global $variabels;
    global $terminals;
    global $productions;
    global $startSymbol;

    $variabels = $variabel;
    $terminals = $terminal;
    $productions = $product;
    $startSymbol = $symbol;
}

function generate_table($kalimats){
    global $tabel;
    global $kalimat;
    global $kata;
    global $jmlKata;
    global $valid;
    global $variabels;
    global $terminals;
    global $productions;
    global $startSymbol;

    $kalimat = $kalimats = trim(strtolower($kalimat)); // bersihkan string
    $kata = explode(" ", $kalimat); // mencari tiap kata
    $jmlKata = count($kata); // jumlah kata

    // generate tabel baris pertama
    for( $i = 0; $i < $jmlKata; $i++ ){
        $kata[$i] = trim($kata[$i]);
        $tabel[$i][$i] = implode(" ", bagianKata($productions, $kata[$i]));
    }
}

function solve(){
    global $tabel;
    global $kalimat;
    global $kata;
    global $jmlKata;
    global $valid;
    global $variabels;
    global $terminals;
    global $productions;
    global $startSymbol;


    $tabelfilling = $tabel;
    $rules = $productions;

    for( $j = 1; $j < $jmlKata; $j++ ){
        for( $i=$j-1 ; $i >= 0 ; $i-- ){
            $tabelfilling[$i][$j] = []; //  himpunan kosong
            for($h=$i; $h <= $j-1; $h++ ){
                $new_rhs = gabung($tabelfilling[$i][$h], $tabelfilling[$h+1][$j]);

                // perulangan untuk setiap kombinasi RHS baru, cek apakah memiliki terminal
                foreach( $new_rhs as $rhs ){
                    $nonTerminal = bagianKata($rules, $rhs);
                    if( count($nonTerminal) > 0 ){
                        $tabelfilling[$i][$j] = array_merge($tabelfilling[$i][$j], $nonTerminal);
                    }
                }
            }
            // union
            $tabelfilling[$i][$j] = implode(" ", array_unique($tabelfilling[$i][$j]));
        }
    }

    $table = $tabelfilling;
}

function validation(){
    global $tabel;
    global $kalimat;
    global $kata;
    global $jmlKata;
    global $valid;
    global $variabels;
    global $terminals;
    global $productions;
    global $startSymbol;

    $tabelfilling = $tabel;
    $arr = explode(" ", $tabelfilling[0][$jmlKata-1]);
    if( in_array($startSymbol, $arr) ){
        return true;
    }else{
        return false;
    }
}